<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Keluarga;
use App\Models\Komsel;
use App\Models\HubunganKeluarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AnggotaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_anggota')->only(['index', 'show']);
        $this->middleware('permission:create_anggota')->only(['create', 'store']);
        $this->middleware('permission:edit_anggota')->only(['edit', 'update']);
        $this->middleware('permission:delete_anggota')->only('destroy');
    }

    public function index()
    {
        $anggota = Anggota::with('keluarga')->get();
        return view('anggota.index', compact('anggota'));
    }

    public function create()
    {
        $keluarga = Keluarga::all();
        $anggota = Anggota::all(); // For selecting parent
        $komsel = Komsel::all();
        return view('anggota.create', compact('keluarga', 'anggota', 'komsel'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'id_keluarga' => 'nullable|exists:keluarga,id_keluarga',
            'id_ortu' => 'nullable|exists:anggota,id_anggota',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'komsel' => 'nullable|array',
            'komsel.*' => 'exists:komsel,id_komsel',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Jika id_ortu ada tapi id_keluarga tidak ada, ambil keluarga dari orang tua
            $id_keluarga = $request->id_keluarga;
            if ($request->id_ortu && !$id_keluarga) {
                $orangtua = Anggota::find($request->id_ortu);
                if ($orangtua && $orangtua->id_keluarga) {
                    $id_keluarga = $orangtua->id_keluarga;
                }
            }

            // Create anggota
            $anggota = Anggota::create([
                'nama' => $request->nama,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'id_keluarga' => $id_keluarga,
                'id_ortu' => $request->id_ortu,
                'alamat' => $request->alamat,
                'no_telepon' => $request->no_telepon,
                'email' => $request->email,
            ]);

            // Attach to komsel if provided
            if ($request->has('komsel')) {
                foreach ($request->komsel as $komselId) {
                    DB::table('anggota_komsel')->insert([
                        'id_anggota' => $anggota->id_anggota,
                        'id_komsel' => $komselId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menambahkan anggota: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Anggota $anggota)
    {
        $anggota->load(['keluarga', 'orangtua', 'anak', 'komsel']);
        
        // Ambil semua anggota keluarga yang berelasi
        $anggotaKeluarga = $anggota->getAnggotaKeluarga();
        
        // Ambil hubungan keluarga
        $hubunganKeluarga = [];
        if ($anggota->id_keluarga) {
            $hubunganKeluarga = HubunganKeluarga::whereIn('id_anggota', 
                Anggota::where('id_keluarga', $anggota->id_keluarga)
                    ->pluck('id_anggota')
            )
            ->orWhereIn('id_anggota_tujuan', 
                Anggota::where('id_keluarga', $anggota->id_keluarga)
                    ->pluck('id_anggota')
            )
            ->with(['anggota', 'anggotaTujuan'])
            ->get();
        }
        
        return view('anggota.show', compact('anggota', 'anggotaKeluarga', 'hubunganKeluarga'));
    }

    public function edit(Anggota $anggota)
    {
        $keluarga = Keluarga::all();
        $allAnggota = Anggota::where('id_anggota', '!=', $anggota->id_anggota)->get();
        $komsel = Komsel::all();
        $anggotaKomsel = $anggota->komsel->pluck('id_komsel')->toArray();
        
        return view('anggota.edit', compact('anggota', 'keluarga', 'allAnggota', 'komsel', 'anggotaKomsel'));
    }

    public function update(Request $request, Anggota $anggota)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'id_keluarga' => 'nullable|exists:keluarga,id_keluarga',
            'id_ortu' => 'nullable|exists:anggota,id_anggota',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'komsel' => 'nullable|array',
            'komsel.*' => 'exists:komsel,id_komsel',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $oldOrtu = $anggota->id_ortu;
            $oldKeluarga = $anggota->id_keluarga;
            
            // Jika id_ortu berubah, update keluarga sesuai orang tua baru
            $id_keluarga = $request->id_keluarga;
            if ($request->id_ortu && $request->id_ortu != $oldOrtu) {
                $orangtua = Anggota::find($request->id_ortu);
                if ($orangtua && $orangtua->id_keluarga) {
                    $id_keluarga = $orangtua->id_keluarga;
                }
            }

            // Update anggota
            $anggota->update([
                'nama' => $request->nama,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'id_keluarga' => $id_keluarga,
                'id_ortu' => $request->id_ortu,
                'alamat' => $request->alamat,
                'no_telepon' => $request->no_telepon,
                'email' => $request->email,
            ]);

            // Jika orang tua berubah, update hubungan keluarga
            if ($request->id_ortu != $oldOrtu) {
                // Hapus hubungan lama dengan orang tua
                if ($oldOrtu) {
                    HubunganKeluarga::where('id_anggota', $anggota->id_anggota)
                        ->where('id_anggota_tujuan', $oldOrtu)
                        ->where('hubungan', 'Anak')
                        ->delete();
                }

                // Buat hubungan baru dengan orang tua
                if ($request->id_ortu) {
                    // Cek apakah hubungan sudah ada
                    $existingRelation = HubunganKeluarga::where('id_anggota', $anggota->id_anggota)
                        ->where('id_anggota_tujuan', $request->id_ortu)
                        ->where('hubungan', 'Anak')
                        ->first();

                    if (!$existingRelation) {
                        HubunganKeluarga::create([
                            'id_anggota' => $anggota->id_anggota,
                            'id_anggota_tujuan' => $request->id_ortu,
                            'hubungan' => 'Anak'
                        ]);
                    }
                }
            }

            // Update komsel
            DB::table('anggota_komsel')->where('id_anggota', $anggota->id_anggota)->delete();
            
            if ($request->has('komsel')) {
                foreach ($request->komsel as $komselId) {
                    DB::table('anggota_komsel')->insert([
                        'id_anggota' => $anggota->id_anggota,
                        'id_komsel' => $komselId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui anggota: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Anggota $anggota)
    {
        // Check if anggota has children
        $hasChildren = Anggota::where('id_ortu', $anggota->id_anggota)->exists();
        
        // Check if anggota has a user account
        $hasUser = $anggota->user()->exists();
        
        if ($hasChildren) {
            return redirect()->route('anggota.index')
                ->with('error', 'Tidak dapat menghapus anggota karena masih memiliki anggota lain sebagai anak.');
        }
        
        if ($hasUser) {
            return redirect()->route('anggota.index')
                ->with('error', 'Tidak dapat menghapus anggota karena terkait dengan akun pengguna.');
        }

        DB::beginTransaction();

        try {
            // Delete from anggota_komsel
            DB::table('anggota_komsel')->where('id_anggota', $anggota->id_anggota)->delete();
            
            // Delete from hubungan_keluarga where anggota is either source or target
            DB::table('hubungan_keluarga')
                ->where('id_anggota', $anggota->id_anggota)
                ->orWhere('id_anggota_tujuan', $anggota->id_anggota)
                ->delete();
            
            // Delete from kehadiran
            DB::table('kehadiran')->where('id_anggota', $anggota->id_anggota)->delete();
            
            // Delete from jadwal_pelayanan
            DB::table('jadwal_pelayanan')->where('id_anggota', $anggota->id_anggota)->delete();
            
            // Delete anggota
            $anggota->delete();
            
            DB::commit();
            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('anggota.index')
                ->with('error', 'Terjadi kesalahan saat menghapus anggota: ' . $e->getMessage());
        }
    }
}