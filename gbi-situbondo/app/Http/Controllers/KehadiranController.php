<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\Anggota;
use App\Models\PelaksanaanKegiatan;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KehadiranController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_kehadiran')->only(['index', 'show', 'laporan']);
        $this->middleware('permission:create_kehadiran')->only(['create', 'store', 'scan', 'processQR']);
        $this->middleware('permission:edit_kehadiran')->only(['edit', 'update']);
    }

    public function index()
    {
        $kegiatan = Kegiatan::all();
        $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
            ->where('tanggal_kegiatan', '>=', Carbon::now()->subDays(7)->format('Y-m-d'))
            ->orderBy('tanggal_kegiatan')
            ->limit(10)
            ->get();
            
        return view('kehadiran.index', compact('kegiatan', 'pelaksanaan'));
    }

    public function create(Request $request)
    {
        $pelaksanaan = null;
        $anggota = Anggota::orderBy('nama')->get();
        
        if ($request->has('id_pelaksanaan')) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->findOrFail($request->id_pelaksanaan);
        } else {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', '>=', Carbon::now()->subDays(1)->format('Y-m-d'))
                ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(1)->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->first();
        }
        
        if (!$pelaksanaan) {
            return redirect()->route('kehadiran.index')
                ->with('error', 'Tidak ada kegiatan yang dapat dicatat kehadirannya saat ini.');
        }
        
        // Get attendance that already recorded
        $kehadiran = Kehadiran::where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
            ->pluck('id_anggota')
            ->toArray();
        
        return view('kehadiran.create', compact('pelaksanaan', 'anggota', 'kehadiran'));
    }

    public function store(Request $request)
    {
        // Updated validation - allow empty anggota array
        $validator = Validator::make($request->all(), [
            'id_pelaksanaan' => 'required|exists:pelaksanaan_kegiatan,id_pelaksanaan',
            'anggota' => 'nullable|array', // Changed from 'required' to 'nullable'
            'anggota.*' => 'exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Get selected anggota IDs, default to empty array if none selected
            $anggotaIds = $request->input('anggota', []);
            
            // Delete existing attendance for this pelaksanaan
            Kehadiran::where('id_pelaksanaan', $request->id_pelaksanaan)->delete();
            
            // Insert new attendance only if there are selected members
            if (!empty($anggotaIds)) {
                foreach ($anggotaIds as $id_anggota) {
                    Kehadiran::create([
                        'id_anggota' => $id_anggota,
                        'id_pelaksanaan' => $request->id_pelaksanaan,
                        'waktu_absensi' => Carbon::now(),
                        'status' => 'hadir',
                    ]);
                }
                
                $message = 'Data kehadiran berhasil disimpan untuk ' . count($anggotaIds) . ' anggota yang hadir.';
            } else {
                $message = 'Data kehadiran berhasil disimpan.';
            }
            
            DB::commit();
            return redirect()->route('kehadiran.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data kehadiran: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $pelaksanaan = PelaksanaanKegiatan::with(['kegiatan', 'kehadiran.anggota'])
            ->findOrFail($id);
            
        return view('kehadiran.show', compact('pelaksanaan'));
    }

    public function scan($id = null)
    {
        $pelaksanaan = null;
        
        if ($id) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')->findOrFail($id);
        } else {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', Carbon::now()->format('Y-m-d'))
                ->orderBy('jam_mulai')
                ->first();
        }
        
        if (!$pelaksanaan) {
            return redirect()->route('kehadiran.index')
                ->with('error', 'Tidak ada kegiatan yang dapat di-scan saat ini.');
        }
        
        // URL untuk QR code
        $qrUrl = route('kehadiran.scan-process', $pelaksanaan->id_pelaksanaan);
            
        return view('kehadiran.scan', compact('pelaksanaan', 'qrUrl'));
    }
    
    public function processQR(Request $request, $id)
    {
        $pelaksanaan = PelaksanaanKegiatan::findOrFail($id);
        
        // For anggota authentication
        if (Auth::user()->id_anggota) {
            $anggota = Anggota::findOrFail(Auth::user()->id_anggota);
            
            // Check if already attended
            $exists = Kehadiran::where('id_anggota', $anggota->id_anggota)
                ->where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
                ->exists();
                
            if ($exists) {
                return redirect()->route('dashboard')
                    ->with('info', 'Anda sudah melakukan presensi pada kegiatan ini.');
            }
            
            // Record attendance
            Kehadiran::create([
                'id_anggota' => $anggota->id_anggota,
                'id_pelaksanaan' => $pelaksanaan->id_pelaksanaan,
                'waktu_absensi' => Carbon::now(),
                'status' => 'hadir',
            ]);
            
            return redirect()->route('dashboard')
                ->with('success', 'Presensi berhasil tercatat. Terima kasih!');
        }
        
        // For manual input (admin/pengurus)
        return redirect()->route('kehadiran.create', ['id_pelaksanaan' => $pelaksanaan->id_pelaksanaan]);
    }
    
    public function laporan()
    {
        $kegiatan = Kegiatan::all();
        return view('kehadiran.laporan', compact('kegiatan'));
    }
    
    public function generateLaporan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kegiatan' => 'required|exists:kegiatan,id_kegiatan',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $kegiatan = Kegiatan::findOrFail($request->id_kegiatan);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        // Get all pelaksanaan within date range
        $pelaksanaan = PelaksanaanKegiatan::where('id_kegiatan', $request->id_kegiatan)
            ->whereBetween('tanggal_kegiatan', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('tanggal_kegiatan')
            ->get();
            
        if ($pelaksanaan->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada data pelaksanaan kegiatan dalam rentang tanggal yang dipilih.')
                ->withInput();
        }
        
        // Get all anggota
        $anggota = Anggota::orderBy('nama')->get();
        
        // Get all kehadiran for these pelaksanaan
        $kehadiran = Kehadiran::whereIn('id_pelaksanaan', $pelaksanaan->pluck('id_pelaksanaan'))
            ->get()
            ->groupBy('id_pelaksanaan')
            ->map(function($item) {
                return $item->pluck('id_anggota')->toArray();
            });
            
        return view('kehadiran.laporan-hasil', compact('kegiatan', 'pelaksanaan', 'anggota', 'kehadiran', 'startDate', 'endDate'));
    }
}