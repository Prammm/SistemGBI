<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PelayananController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get upcoming pelayanan
        $user = Auth::user();
        
        // For admin and pengurus gereja, show all schedules
        if ($user->id_role <= 2) {
            $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'kegiatan'])
                ->where('tanggal_pelayanan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_pelayanan')
                ->get()
                ->groupBy('tanggal_pelayanan');
        }
        // For pengurus pelayanan, show their team's schedules
        else if ($user->id_role == 3) {
            $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'kegiatan'])
                ->where('tanggal_pelayanan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_pelayanan')
                ->get()
                ->groupBy('tanggal_pelayanan');
        }
        // For regular members, show their own schedules
        else {
            if ($user->id_anggota) {
                $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'kegiatan'])
                    ->where('id_anggota', $user->id_anggota)
                    ->where('tanggal_pelayanan', '>=', Carbon::now()->format('Y-m-d'))
                    ->orderBy('tanggal_pelayanan')
                    ->get()
                    ->groupBy('tanggal_pelayanan');
            } else {
                $jadwalPelayanan = collect();
            }
        }
        
        // Get previous pelayanan history
        if ($user->id_role <= 2) {
            $riwayatPelayanan = JadwalPelayanan::with(['anggota', 'kegiatan'])
                ->where('tanggal_pelayanan', '<', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_pelayanan', 'desc')
                ->limit(30)
                ->get()
                ->groupBy('tanggal_pelayanan');
        } else if ($user->id_role == 3) {
            $riwayatPelayanan = JadwalPelayanan::with(['anggota', 'kegiatan'])
                ->where('tanggal_pelayanan', '<', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_pelayanan', 'desc')
                ->limit(30)
                ->get()
                ->groupBy('tanggal_pelayanan');
        } else {
            if ($user->id_anggota) {
                $riwayatPelayanan = JadwalPelayanan::with(['anggota', 'kegiatan'])
                    ->where('id_anggota', $user->id_anggota)
                    ->where('tanggal_pelayanan', '<', Carbon::now()->format('Y-m-d'))
                    ->orderBy('tanggal_pelayanan', 'desc')
                    ->limit(10)
                    ->get()
                    ->groupBy('tanggal_pelayanan');
            } else {
                $riwayatPelayanan = collect();
            }
        }
        
        // Get all ibadah kegiatan for creating new schedules (admin and pengurus only)
        $kegiatan = [];
        if ($user->id_role <= 3) {
            $kegiatan = Kegiatan::where('tipe_kegiatan', 'ibadah')
                ->orderBy('nama_kegiatan')
                ->get();
        }
        
        return view('pelayanan.index', compact('jadwalPelayanan', 'riwayatPelayanan', 'kegiatan'));
    }
    
    public function create(Request $request)
    {
        // Check if user has permission
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk membuat jadwal pelayanan.');
        }
        
        $kegiatan = null;
        $tanggal = null;
        
        if ($request->has('id_kegiatan')) {
            $kegiatan = Kegiatan::findOrFail($request->id_kegiatan);
        } else {
            $kegiatan = Kegiatan::where('tipe_kegiatan', 'ibadah')
                ->orderBy('nama_kegiatan')
                ->first();
        }
        
        if (!$kegiatan) {
            return redirect()->route('kegiatan.create')
                ->with('error', 'Silahkan buat kegiatan ibadah terlebih dahulu.');
        }
        
        if ($request->has('tanggal')) {
            $tanggal = $request->tanggal;
        } else {
            $tanggal = Carbon::now()->next(Carbon::SUNDAY)->format('Y-m-d');
        }
        
        // Get anggota for pelayanan
        $anggota = Anggota::orderBy('nama')->get();
        
        // Get existing jadwal for this tanggal and kegiatan
        $existingJadwal = JadwalPelayanan::with('anggota')
            ->where('id_kegiatan', $kegiatan->id_kegiatan)
            ->where('tanggal_pelayanan', $tanggal)
            ->get();
            
        // Prepare posisi options
        $posisiOptions = [
            'Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Bass', 'Drum', 
            'Sound System', 'Multimedia', 'Usher', 'Liturgos', 'Pembaca Alkitab',
            'Pembawa Persembahan', 'Pemimpin Pujian', 'Pemain Musik', 'Dokumentasi'
        ];
        
        // Jika sudah ada jadwal, siapkan data edit
        $jadwalByPosisi = [];
        foreach ($existingJadwal as $jadwal) {
            $jadwalByPosisi[$jadwal->posisi] = $jadwal;
        }
        
        $allKegiatan = Kegiatan::where('tipe_kegiatan', 'ibadah')
            ->orderBy('nama_kegiatan')
            ->get();
            
        return view('pelayanan.create', compact('kegiatan', 'allKegiatan', 'tanggal', 'anggota', 'existingJadwal', 'posisiOptions', 'jadwalByPosisi'));
    }
    
    public function store(Request $request)
    {
        // Check if user has permission
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk membuat jadwal pelayanan.');
        }
        
        $validator = Validator::make($request->all(), [
            'id_kegiatan' => 'required|exists:kegiatan,id_kegiatan',
            'tanggal_pelayanan' => 'required|date',
            'petugas' => 'required|array',
            'petugas.*.posisi' => 'required|string',
            'petugas.*.id_anggota' => 'required|exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Delete existing jadwal for this kegiatan and tanggal
            JadwalPelayanan::where('id_kegiatan', $request->id_kegiatan)
                ->where('tanggal_pelayanan', $request->tanggal_pelayanan)
                ->delete();
                
            // Create new jadwal
            foreach ($request->petugas as $petugas) {
                JadwalPelayanan::create([
                    'id_kegiatan' => $request->id_kegiatan,
                    'tanggal_pelayanan' => $request->tanggal_pelayanan,
                    'id_anggota' => $petugas['id_anggota'],
                    'posisi' => $petugas['posisi'],
                    'status_konfirmasi' => 'belum',
                ]);
            }
            
            DB::commit();
            return redirect()->route('pelayanan.index')
                ->with('success', 'Jadwal pelayanan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan jadwal pelayanan: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function konfirmasi($id, $status)
    {
        $jadwal = JadwalPelayanan::findOrFail($id);
        
        // Check if user is authorized
        $user = Auth::user();
        if ($user->id_role > 3 && (!$user->id_anggota || $user->id_anggota != $jadwal->id_anggota)) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengkonfirmasi jadwal ini.');
        }
        
        // Check if status is valid
        if (!in_array($status, ['terima', 'tolak'])) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Status konfirmasi tidak valid.');
        }
        
        $jadwal->status_konfirmasi = $status;
        $jadwal->save();
        
        return redirect()->route('pelayanan.index')
            ->with('success', 'Konfirmasi jadwal pelayanan berhasil disimpan.');
    }
    
    public function destroy($id)
    {
        // Check if user has permission
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus jadwal pelayanan.');
        }
        
        $jadwal = JadwalPelayanan::findOrFail($id);
        $jadwal->delete();
        
        return redirect()->route('pelayanan.index')
            ->with('success', 'Jadwal pelayanan berhasil dihapus.');
    }
    
    public function generateSchedule(Request $request)
    {
        // Check if user has permission
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk generate jadwal pelayanan.');
        }
        
        $validator = Validator::make($request->all(), [
            'id_kegiatan' => 'required|exists:kegiatan,id_kegiatan',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'hari' => 'required|array',
            'hari.*' => 'in:0,1,2,3,4,5,6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $kegiatan = Kegiatan::findOrFail($request->id_kegiatan);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $hari = $request->hari;
        
        // Get all available petugas
        $petugas = Anggota::whereHas('jadwalPelayanan')->get();
        
        if ($petugas->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada anggota yang memiliki riwayat pelayanan untuk dijadwalkan secara otomatis.')
                ->withInput();
        }
        
        // Get the positions based on the kegiatan
        $positions = JadwalPelayanan::where('id_kegiatan', $kegiatan->id_kegiatan)
            ->distinct('posisi')
            ->pluck('posisi')
            ->toArray();
            
        if (empty($positions)) {
            $positions = [
                'Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Bass', 'Drum', 
                'Sound System', 'Multimedia', 'Liturgos'
            ];
        }
        
        // Generate array of dates that match the selected days
        $dates = [];
        $currentDate = clone $startDate;
        
        while ($currentDate->lte($endDate)) {
            if (in_array($currentDate->dayOfWeek, $hari)) {
                $dates[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }
        
        if (empty($dates)) {
            return redirect()->back()
                ->with('error', 'Tidak ada tanggal yang cocok dengan hari yang dipilih dalam rentang waktu tersebut.')
                ->withInput();
        }
        
        // Prepare scheduled dates
        $scheduledDates = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($dates as $date) {
                // Check if already has schedule
                $existingSchedule = JadwalPelayanan::where('id_kegiatan', $kegiatan->id_kegiatan)
                    ->where('tanggal_pelayanan', $date)
                    ->exists();
                    
                if ($existingSchedule) {
                    $scheduledDates[] = Carbon::parse($date)->format('d/m/Y');
                    continue;
                }
                
                // For each position, assign a person who has not been scheduled recently
                foreach ($positions as $position) {
                    // Find eligible candidates
                    $eligiblePetugas = $petugas->filter(function ($anggota) use ($position, $date) {
                        // Check if they usually serve in this position
                        $serveInPosition = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)
                            ->where('posisi', $position)
                            ->exists();
                            
                        // Check if they are already scheduled on this date
                        $alreadyScheduled = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)
                            ->where('tanggal_pelayanan', $date)
                            ->exists();
                            
                        // Check when they served last time in this position
                        $lastServed = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)
                            ->where('posisi', $position)
                            ->where('tanggal_pelayanan', '<', $date)
                            ->orderBy('tanggal_pelayanan', 'desc')
                            ->first();
                            
                        // If they have served in this position and are not already scheduled
                        return $serveInPosition && !$alreadyScheduled;
                    });
                    
                    if ($eligiblePetugas->isEmpty()) {
                        continue; // Skip this position if no eligible candidates
                    }
                    
                    // Sort by last served date (oldest first)
                    $eligiblePetugas = $eligiblePetugas->sortBy(function ($anggota) use ($position) {
                        $lastServed = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)
                            ->where('posisi', $position)
                            ->orderBy('tanggal_pelayanan', 'desc')
                            ->first();
                            
                        return $lastServed ? $lastServed->tanggal_pelayanan : '1970-01-01';
                    });
                    
                    // Select the one who served longest time ago
                    $selectedPetugas = $eligiblePetugas->first();
                    
                    // Create jadwal
                    JadwalPelayanan::create([
                        'id_kegiatan' => $kegiatan->id_kegiatan,
                        'tanggal_pelayanan' => $date,
                        'id_anggota' => $selectedPetugas->id_anggota,
                        'posisi' => $position,
                        'status_konfirmasi' => 'belum',
                    ]);
                }
            }
            
            DB::commit();
            
            if (!empty($scheduledDates)) {
                return redirect()->route('pelayanan.index')
                    ->with('info', 'Jadwal pelayanan berhasil digenerate, namun beberapa tanggal sudah memiliki jadwal: ' . implode(', ', $scheduledDates))
                    ->with('success', 'Jadwal pelayanan berhasil digenerate.');
            } else {
                return redirect()->route('pelayanan.index')
                    ->with('success', 'Jadwal pelayanan berhasil digenerate untuk ' . count($dates) . ' tanggal.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat generate jadwal: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function showGenerator()
    {
        // Check if user has permission
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk generate jadwal pelayanan.');
        }
        
        $kegiatan = Kegiatan::where('tipe_kegiatan', 'ibadah')
            ->orderBy('nama_kegiatan')
            ->get();
            
        if ($kegiatan->isEmpty()) {
            return redirect()->route('kegiatan.create')
                ->with('error', 'Silahkan buat kegiatan ibadah terlebih dahulu.');
        }
        
        return view('pelayanan.generator', compact('kegiatan'));
    }
}