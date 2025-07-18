<?php

namespace App\Http\Controllers;

use App\Models\Komsel;
use App\Models\Anggota;
use App\Models\AnggotaKomsel;
use App\Models\Kegiatan;
use App\Models\PelaksanaanKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KomselController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_komsel')->only(['index', 'show']);
        $this->middleware('permission:create_komsel')->only(['create', 'store']);
        $this->middleware('permission:edit_komsel')->only(['edit', 'update']);
        $this->middleware('permission:delete_komsel')->only('destroy');
    }

    public function index()
    {
        $user = auth()->user();
        
        // If admin, show all komsel
        if ($user->id_role <= 2) {
            $komsel = Komsel::withCount('anggota')->with('pemimpin')->get();
        }
        // If regular member or service staff (roles 3 and 4), only show their komsel
        else {
            $anggota = $user->anggota;
            
            if (!$anggota) {
                $komsel = collect();
            } else {
                $komsel = $anggota->komsel()->withCount('anggota')->with('pemimpin')->get();
            }
        }
        
        return view('komsel.index', compact('komsel'));
    }

    public function create()
    {
        $anggota = Anggota::orderBy('nama')->get();
        return view('komsel.create', compact('anggota'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_komsel' => 'required|string|max:255',
            'hari' => 'required|string|max:20',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'lokasi' => 'nullable|string|max:255',
            'id_pemimpin' => 'nullable|exists:anggota,id_anggota',
            'deskripsi' => 'nullable|string',
            'anggota' => 'nullable|array',
            'anggota.*' => 'exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Create komsel
            $komsel = Komsel::create([
                'nama_komsel' => $request->nama_komsel,
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'lokasi' => $request->lokasi,
                'id_pemimpin' => $request->id_pemimpin,
                'deskripsi' => $request->deskripsi,
            ]);

            // Add members to komsel
            if ($request->has('anggota')) {
                foreach ($request->anggota as $id_anggota) {
                    AnggotaKomsel::create([
                        'id_komsel' => $komsel->id_komsel,
                        'id_anggota' => $id_anggota,
                    ]);
                }
            }

            // Add pemimpin as member if they're not already included
            if ($request->id_pemimpin && (!$request->has('anggota') || !in_array($request->id_pemimpin, $request->anggota))) {
                AnggotaKomsel::create([
                    'id_komsel' => $komsel->id_komsel,
                    'id_anggota' => $request->id_pemimpin,
                ]);
            }

            // Create kegiatan for the komsel
            $kegiatan = Kegiatan::create([
                'nama_kegiatan' => 'Komsel - ' . $request->nama_komsel,
                'tipe_kegiatan' => 'komsel',
                'deskripsi' => 'Kegiatan kelompok sel ' . $request->nama_komsel,
            ]);

            DB::commit();
            return redirect()->route('komsel.index')
                ->with('success', 'Kelompok sel berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat kelompok sel: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Komsel $komsel)
    {
        $user = auth()->user();
        
        // If regular member or service staff (not admin)
        if ($user->id_role > 2) {
            $anggota = $user->anggota;
            
            // Check if user is a member of this komsel
            if (!$anggota || !$anggota->komsel->contains('id_komsel', $komsel->id_komsel)) {
                return redirect()->route('komsel.index')
                    ->with('error', 'Anda tidak memiliki akses untuk melihat kelompok sel ini.');
            }
        }
        
        $komsel->load(['anggota', 'pemimpin']);
        
        // Get recent and upcoming meetings
        $kegiatan = Kegiatan::where('nama_kegiatan', 'Komsel - ' . $komsel->nama_komsel)
            ->where('tipe_kegiatan', 'komsel')
            ->first();
            
        $pertemuan = [];
        
        if ($kegiatan) {
            $pertemuan = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan->id_kegiatan)
                ->orderBy('tanggal_kegiatan', 'desc')
                ->limit(10)
                ->get();
        }
        
        // Get current pertemuan for attendance view (latest or selected)
        $currentPertemuan = null;
        $selectedPertemuanId = request('pertemuan_id');
        
        if ($selectedPertemuanId) {
            $currentPertemuan = $pertemuan->firstWhere('id_pelaksanaan', $selectedPertemuanId);
        } else {
            $currentPertemuan = $pertemuan->first();
        }
        
        // Get previous and next pertemuan for navigation
        $previousPertemuan = null;
        $nextPertemuan = null;
        
        if ($currentPertemuan && $kegiatan) {
            $allPertemuan = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan->id_kegiatan)
                ->orderBy('tanggal_kegiatan', 'asc')
                ->get();
                
            $currentIndex = $allPertemuan->search(function($item) use ($currentPertemuan) {
                return $item->id_pelaksanaan == $currentPertemuan->id_pelaksanaan;
            });
            
            if ($currentIndex !== false) {
                if ($currentIndex > 0) {
                    $previousPertemuan = $allPertemuan[$currentIndex - 1];
                }
                if ($currentIndex < $allPertemuan->count() - 1) {
                    $nextPertemuan = $allPertemuan[$currentIndex + 1];
                }
            }
        }
        
        // Get attendance for current pertemuan
        $attendanceData = [];
        if ($currentPertemuan) {
            $attendanceData = $this->getAttendanceData($komsel, $currentPertemuan);
        }
        
        return view('komsel.show', compact(
            'komsel', 
            'pertemuan', 
            'kegiatan', 
            'currentPertemuan', 
            'previousPertemuan', 
            'nextPertemuan',
            'attendanceData'
        ));
    }

    /**
     * Helper method to check if user can view anggota profile
     */
    private function canViewAnggotaProfile($targetAnggotaId)
    {
        $user = auth()->user();
        
        // Admin can view all profiles
        if ($user->id_role <= 1) {
            return true;
        }
        
        // User can view their own profile
        if ($user->id_anggota == $targetAnggotaId) {
            return true;
        }
        
        // If user is not an anggota, deny access
        if (!$user->anggota) {
            return false;
        }
        
        // Komsel leaders can view their members' profiles
        $userKomselAsLeader = Komsel::where('id_pemimpin', $user->id_anggota)->get();
        
        foreach ($userKomselAsLeader as $komsel) {
            if ($komsel->anggota->contains('id_anggota', $targetAnggotaId)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get attendance data for a specific pertemuan
     */
    private function getAttendanceData($komsel, $pertemuan)
    {
        $attendanceData = [];
        
        foreach ($komsel->anggota as $anggota) {
            $kehadiran = DB::table('kehadiran')
                ->where('id_anggota', $anggota->id_anggota)
                ->where('id_pelaksanaan', $pertemuan->id_pelaksanaan)
                ->first();
            
            $contactInfo = $this->getContactInfo($anggota);
            
            $attendanceData[] = [
                'anggota' => $anggota,
                'hadir' => $kehadiran ? true : false,
                'kehadiran' => $kehadiran,
                'contact_info' => $contactInfo,
                'can_view_profile' => $this->canViewAnggotaProfile($anggota->id_anggota)
            ];
        }
        
        return $attendanceData;
    }

    /**
     * Get contact information for an anggota
     */
    private function getContactInfo($anggota)
    {
        $contactInfo = [
            'has_phone' => !empty($anggota->no_telepon),
            'has_email' => !empty($anggota->email),
            'phone' => $anggota->no_telepon,
            'email' => $anggota->email,
            'whatsapp_url' => null,
            'contact_message' => null
        ];
        
        if ($contactInfo['has_phone']) {
            // Clean phone number for WhatsApp
            $cleanPhone = preg_replace('/[^0-9]/', '', $anggota->no_telepon);
            if (substr($cleanPhone, 0, 1) === '0') {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            $contactInfo['whatsapp_url'] = "https://wa.me/{$cleanPhone}";
        } elseif ($contactInfo['has_email']) {
            $contactInfo['contact_message'] = "Hubungi melalui email: {$anggota->email}";
        } else {
            $contactInfo['contact_message'] = "Anggota tidak mencantumkan nomor HP atau email. Harap perbarui data kontak anggota di sistem.";
        }
        
        return $contactInfo;
    }

    public function edit(Komsel $komsel)
    {
        $anggota = Anggota::orderBy('nama')->get();
        $anggotaKomsel = $komsel->anggota->pluck('id_anggota')->toArray();
        
        return view('komsel.edit', compact('komsel', 'anggota', 'anggotaKomsel'));
    }

    public function update(Request $request, Komsel $komsel)
    {
        $validator = Validator::make($request->all(), [
            'nama_komsel' => 'required|string|max:255',
            'hari' => 'required|string|max:20',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'lokasi' => 'nullable|string|max:255',
            'id_pemimpin' => 'nullable|exists:anggota,id_anggota',
            'deskripsi' => 'nullable|string',
            'anggota' => 'nullable|array',
            'anggota.*' => 'exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Update komsel
            $oldName = $komsel->nama_komsel;
            
            $komsel->update([
                'nama_komsel' => $request->nama_komsel,
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'lokasi' => $request->lokasi,
                'id_pemimpin' => $request->id_pemimpin,
                'deskripsi' => $request->deskripsi,
            ]);

            // Update members
            AnggotaKomsel::where('id_komsel', $komsel->id_komsel)->delete();
            
            if ($request->has('anggota')) {
                foreach ($request->anggota as $id_anggota) {
                    AnggotaKomsel::create([
                        'id_komsel' => $komsel->id_komsel,
                        'id_anggota' => $id_anggota,
                    ]);
                }
            }

            // Add pemimpin as member if they're not already included
            if ($request->id_pemimpin && (!$request->has('anggota') || !in_array($request->id_pemimpin, $request->anggota))) {
                AnggotaKomsel::create([
                    'id_komsel' => $komsel->id_komsel,
                    'id_anggota' => $request->id_pemimpin,
                ]);
            }

            // Update kegiatan name if komsel name changed
            if ($oldName !== $request->nama_komsel) {
                Kegiatan::where('nama_kegiatan', 'Komsel - ' . $oldName)
                    ->where('tipe_kegiatan', 'komsel')
                    ->update([
                        'nama_kegiatan' => 'Komsel - ' . $request->nama_komsel,
                        'deskripsi' => 'Kegiatan kelompok sel ' . $request->nama_komsel,
                    ]);
            }

            DB::commit();
            return redirect()->route('komsel.index')
                ->with('success', 'Kelompok sel berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui kelompok sel: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Komsel $komsel)
    {
        DB::beginTransaction();

        try {
            // Delete all members
            AnggotaKomsel::where('id_komsel', $komsel->id_komsel)->delete();
            
            // Delete related kegiatan
            $kegiatan = Kegiatan::where('nama_kegiatan', 'Komsel - ' . $komsel->nama_komsel)
                ->where('tipe_kegiatan', 'komsel')
                ->first();
                
            if ($kegiatan) {
                // Delete related pelaksanaan
                $pelaksanaan_ids = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan->id_kegiatan)
                    ->pluck('id_pelaksanaan');
                    
                // Delete related kehadiran
                DB::table('kehadiran')->whereIn('id_pelaksanaan', $pelaksanaan_ids)->delete();
                
                // Delete pelaksanaan
                PelaksanaanKegiatan::where('id_kegiatan', $kegiatan->id_kegiatan)->delete();
                
                // Delete kegiatan
                $kegiatan->delete();
            }
            
            // Delete komsel
            $komsel->delete();
            
            DB::commit();
            return redirect()->route('komsel.index')
                ->with('success', 'Kelompok sel berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus kelompok sel: ' . $e->getMessage());
        }
    }
    
    public function tambahPertemuan(Request $request, Komsel $komsel)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_kegiatan' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'lokasi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Find the kegiatan for this komsel
        $kegiatan = Kegiatan::where('nama_kegiatan', 'Komsel - ' . $komsel->nama_komsel)
            ->where('tipe_kegiatan', 'komsel')
            ->first();
            
        if (!$kegiatan) {
            // Create new kegiatan if not exists
            $kegiatan = Kegiatan::create([
                'nama_kegiatan' => 'Komsel - ' . $komsel->nama_komsel,
                'tipe_kegiatan' => 'komsel',
                'deskripsi' => 'Kegiatan kelompok sel ' . $komsel->nama_komsel,
            ]);
        }
        
        // Create pelaksanaan
        $pelaksanaan = PelaksanaanKegiatan::create([
            'id_kegiatan' => $kegiatan->id_kegiatan,
            'tanggal_kegiatan' => $request->tanggal_kegiatan,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'lokasi' => $request->lokasi ?: $komsel->lokasi,
        ]);
        
        return redirect()->route('komsel.show', $komsel->id_komsel)
            ->with('success', 'Pertemuan komsel berhasil ditambahkan.');
    }
    
    public function jadwalkanPertemuan(Komsel $komsel)
    {
        // Find the kegiatan for this komsel
        $kegiatan = Kegiatan::where('nama_kegiatan', 'Komsel - ' . $komsel->nama_komsel)
            ->where('tipe_kegiatan', 'komsel')
            ->first();
            
        if (!$kegiatan) {
            // Create new kegiatan if not exists
            $kegiatan = Kegiatan::create([
                'nama_kegiatan' => 'Komsel - ' . $komsel->nama_komsel,
                'tipe_kegiatan' => 'komsel',
                'deskripsi' => 'Kegiatan kelompok sel ' . $komsel->nama_komsel,
            ]);
        }
        
        // Get next meeting date (based on day of week)
        $dayOfWeek = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 0,
        ];
        
        $nextDate = Carbon::now();
        $targetDay = $dayOfWeek[$komsel->hari] ?? 0;
        
        // If today is the target day but the time has passed, start from tomorrow
        if ($nextDate->dayOfWeek == $targetDay && 
            Carbon::now()->format('H:i:s') > $komsel->jam_mulai) {
            $nextDate->addWeek();
        }
        
        // Go to the next occurrence of the target day
        while ($nextDate->dayOfWeek != $targetDay) {
            $nextDate->addDay();
        }
        
        // Create pelaksanaan
        $pelaksanaan = PelaksanaanKegiatan::create([
            'id_kegiatan' => $kegiatan->id_kegiatan,
            'tanggal_kegiatan' => $nextDate->format('Y-m-d'),
            'jam_mulai' => $komsel->jam_mulai,
            'jam_selesai' => $komsel->jam_selesai,
            'lokasi' => $komsel->lokasi,
        ]);
        
        return redirect()->route('komsel.show', $komsel->id_komsel)
            ->with('success', 'Pertemuan komsel berhasil dijadwalkan untuk ' . $nextDate->format('d/m/Y') . '.');
    }
    
    public function absensi(PelaksanaanKegiatan $pelaksanaan)
    {
        $pelaksanaan->load('kegiatan');
        
        // Verify this is a komsel meeting
        if (!$pelaksanaan->kegiatan || $pelaksanaan->kegiatan->tipe_kegiatan != 'komsel') {
            return redirect()->route('komsel.index')
                ->with('error', 'Pelaksanaan kegiatan bukan merupakan pertemuan komsel.');
        }
        
        // Check if meeting has started - NEW FEATURE
        $meetingDate = Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('Y-m-d');
        $meetingTime = Carbon::parse($pelaksanaan->jam_mulai)->format('H:i:s');
        $meetingDateTime = Carbon::parse($meetingDate . ' ' . $meetingTime);
        $canTakeAttendance = Carbon::now()->gte($meetingDateTime);
        
        // Extract komsel name from kegiatan
        $komselName = str_replace('Komsel - ', '', $pelaksanaan->kegiatan->nama_kegiatan);
        
        // Get komsel
        $komsel = Komsel::where('nama_komsel', $komselName)->first();
        
        if (!$komsel) {
            return redirect()->route('komsel.index')
                ->with('error', 'Kelompok sel tidak ditemukan.');
        }
        
        // Get all members
        $anggota = $komsel->anggota;
        
        // Get attendance that already recorded
        $kehadiran = DB::table('kehadiran')
            ->where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
            ->pluck('id_anggota')
            ->toArray();
            
        return view('komsel.absensi', compact(
            'pelaksanaan', 
            'komsel', 
            'anggota', 
            'kehadiran', 
            'canTakeAttendance'
        ));
    }
    
    public function storeAbsensi(Request $request, PelaksanaanKegiatan $pelaksanaan)
    {
        // Check if meeting has started - NEW VALIDATION
        $meetingDate = Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('Y-m-d');
        $meetingTime = Carbon::parse($pelaksanaan->jam_mulai)->format('H:i:s');
        $meetingDateTime = Carbon::parse($meetingDate . ' ' . $meetingTime);
        if (Carbon::now()->lt($meetingDateTime)) {
            return redirect()->back()
                ->with('error', 'Presensi tidak dapat dilakukan sebelum kegiatan dimulai.');
        }

        $validator = Validator::make($request->all(), [
            'anggota' => 'nullable|array',
            'anggota.*' => 'exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Delete existing attendance
            DB::table('kehadiran')->where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)->delete();
            
            // Insert new attendance
            if ($request->has('anggota')) {
                foreach ($request->anggota as $id_anggota) {
                    DB::table('kehadiran')->insert([
                        'id_anggota' => $id_anggota,
                        'id_pelaksanaan' => $pelaksanaan->id_pelaksanaan,
                        'waktu_absensi' => Carbon::now(),
                        'status' => 'hadir',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
            
            DB::commit();
            
            // Extract komsel name from kegiatan
            $komselName = str_replace('Komsel - ', '', $pelaksanaan->kegiatan->nama_kegiatan);
            
            // Get komsel
            $komsel = Komsel::where('nama_komsel', $komselName)->first();
            
            return redirect()->route('komsel.show', $komsel->id_komsel)
                ->with('success', 'Presensi pertemuan komsel berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan Presensi: ' . $e->getMessage())
                ->withInput();
        }
    }
}