<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\PelaksanaanKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class KegiatanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_kegiatan')->only(['index', 'show', 'calendar', 'getEvents']);
        $this->middleware('permission:create_kegiatan')->only(['create', 'store']);
        $this->middleware('permission:edit_kegiatan')->only(['edit', 'update']);
        $this->middleware('permission:delete_kegiatan')->only('destroy');
    }

    public function index()
    {
        try {
            $kegiatan = Kegiatan::orderBy('nama_kegiatan')->get();
            
            // Debug: Log jumlah data kegiatan
            Log::info('Jumlah kegiatan: ' . $kegiatan->count());
            Log::info('Data kegiatan: ' . $kegiatan->toJson());
            
            return view('kegiatan.index', compact('kegiatan'));
        } catch (\Exception $e) {
            Log::error('Error di index kegiatan: ' . $e->getMessage());
            return view('kegiatan.index', ['kegiatan' => collect()]);
        }
    }

    public function create()
    {
        return view('kegiatan.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required|string|max:255',
            'tipe_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Kegiatan::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'tipe_kegiatan' => $request->tipe_kegiatan,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('kegiatan.index')
            ->with('success', 'Kegiatan berhasil dibuat.');
    }

    public function show(Kegiatan $kegiatan)
    {
        $kegiatan->load('pelaksanaan');
        return view('kegiatan.show', compact('kegiatan'));
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('kegiatan.edit', compact('kegiatan'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required|string|max:255',
            'tipe_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $kegiatan->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'tipe_kegiatan' => $request->tipe_kegiatan,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('kegiatan.index')
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        // Check if kegiatan has pelaksanaan
        $hasPelaksanaan = $kegiatan->pelaksanaan()->exists();
        
        if ($hasPelaksanaan) {
            return redirect()->route('kegiatan.index')
                ->with('error', 'Tidak dapat menghapus kegiatan karena memiliki jadwal pelaksanaan.');
        }
        
        $kegiatan->delete();
        
        return redirect()->route('kegiatan.index')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function calendar()
    {
        try {
            // Debug: Cek koneksi database dan tabel
            Log::info('=== DEBUG CALENDAR START ===');
            
            // 1. Cek apakah tabel kegiatan ada dan memiliki data
            $totalKegiatan = Kegiatan::count();
            Log::info('Total kegiatan di database: ' . $totalKegiatan);
            
            if ($totalKegiatan > 0) {
                $sampleKegiatan = Kegiatan::first();
                Log::info('Sample kegiatan: ' . $sampleKegiatan->toJson());
            }
            
            // 2. Cek apakah tabel pelaksanaan_kegiatan ada dan memiliki data
            $totalPelaksanaan = PelaksanaanKegiatan::count();
            Log::info('Total pelaksanaan kegiatan di database: ' . $totalPelaksanaan);
            
            if ($totalPelaksanaan > 0) {
                $samplePelaksanaan = PelaksanaanKegiatan::first();
                Log::info('Sample pelaksanaan: ' . $samplePelaksanaan->toJson());
            }
            
            // 3. Cek join antara kegiatan dan pelaksanaan
            $pelaksanaanWithKegiatan = PelaksanaanKegiatan::with('kegiatan')->get();
            Log::info('Pelaksanaan dengan kegiatan: ' . $pelaksanaanWithKegiatan->count());
            
            // 4. Cek yang memiliki relasi kegiatan
            $pelaksanaanHasKegiatan = PelaksanaanKegiatan::with('kegiatan')
                ->whereHas('kegiatan')
                ->get();
            Log::info('Pelaksanaan yang memiliki kegiatan: ' . $pelaksanaanHasKegiatan->count());
            
            // Get all pelaksanaan with their kegiatan
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->whereHas('kegiatan')
                ->orderBy('tanggal_kegiatan')
                ->get();
                
            Log::info('Query result count: ' . $pelaksanaan->count());
            
            $events = [];
            
            foreach ($pelaksanaan as $p) {
                Log::info('Processing pelaksanaan ID: ' . $p->id_pelaksanaan);
                
                // Skip if kegiatan doesn't exist
                if (!$p->kegiatan) {
                    Log::warning('Pelaksanaan ID ' . $p->id_pelaksanaan . ' tidak memiliki kegiatan');
                    continue;
                }
                
                Log::info('Kegiatan found: ' . $p->kegiatan->nama_kegiatan . ' (Tipe: ' . $p->kegiatan->tipe_kegiatan . ')');
                
                // Tentukan warna berdasarkan tipe kegiatan
                $color = '#3498db'; // Default: blue
                
                switch ($p->kegiatan->tipe_kegiatan) {
                    case 'ibadah':
                        $color = '#2ecc71'; // Green
                        break;
                    case 'komsel':
                        $color = '#f39c12'; // Orange
                        break;
                    case 'pelayanan':
                        $color = '#9b59b6'; // Purple
                        break;
                    case 'pelatihan':
                        $color = '#e74c3c'; // Red
                        break;
                }
                
                // Format tanggal dan waktu - dengan validation
                $tanggalKegiatan = $p->tanggal_kegiatan instanceof \Carbon\Carbon 
                    ? $p->tanggal_kegiatan->format('Y-m-d')
                    : $p->tanggal_kegiatan;
                    
                $startDateTime = $tanggalKegiatan . 'T' . $p->jam_mulai;  
                $endDateTime = $tanggalKegiatan . 'T' . $p->jam_selesai;
                
                Log::info('DateTime: ' . $startDateTime . ' to ' . $endDateTime);
                
                $eventData = [
                    'id' => $p->id_pelaksanaan,
                    'title' => $p->kegiatan->nama_kegiatan,
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'color' => $color,
                    'description' => $p->kegiatan->deskripsi ?? '',
                    'location' => $p->lokasi ?? '',
                    'url' => route('pelaksanaan.show', $p->id_pelaksanaan),
                    'extendedProps' => [
                        'description' => $p->kegiatan->deskripsi ?? '',
                        'location' => $p->lokasi ?? '',
                        'type' => $p->kegiatan->tipe_kegiatan,
                        'is_recurring' => $p->is_recurring ?? false,
                        'recurring_type' => $p->recurring_type ?? null
                    ]
                ];
                
                $events[] = $eventData;
                Log::info('Event added: ' . json_encode($eventData));
            }
            
            Log::info('Total events created: ' . count($events));
            Log::info('Events data: ' . json_encode($events));
            Log::info('=== DEBUG CALENDAR END ===');
            
            return view('kegiatan.calendar', compact('events'));
            
        } catch (\Exception $e) {
            Log::error('Calendar error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return view('kegiatan.calendar', ['events' => []])
                ->with('error', 'Terjadi kesalahan saat memuat kalender: ' . $e->getMessage());
        }
    }

    public function getEvents(Request $request)
    {
        try {
            Log::info('=== GET EVENTS API CALLED ===');
            Log::info('Request parameters: ' . json_encode($request->all()));
            
            $start = $request->start;
            $end = $request->end;
            
            $query = PelaksanaanKegiatan::with('kegiatan')
                ->whereHas('kegiatan');
            
            // Filter by date range if provided
            if ($start && $end) {
                $query->whereBetween('tanggal_kegiatan', [$start, $end]);
                Log::info('Date filter applied: ' . $start . ' to ' . $end);
            } elseif ($start) {
                $query->where('tanggal_kegiatan', '>=', $start);
                Log::info('Start date filter applied: ' . $start);
            } elseif ($end) {
                $query->where('tanggal_kegiatan', '<=', $end);
                Log::info('End date filter applied: ' . $end);
            }
            
            $pelaksanaan = $query->orderBy('tanggal_kegiatan')->get();
            Log::info('Filtered pelaksanaan count: ' . $pelaksanaan->count());
            
            $events = [];
            
            foreach ($pelaksanaan as $p) {
                // Skip if kegiatan doesn't exist
                if (!$p->kegiatan) {
                    continue;
                }
                
                // Tentukan warna berdasarkan tipe kegiatan
                $color = '#3498db'; // Default: blue
                
                switch ($p->kegiatan->tipe_kegiatan) {
                    case 'ibadah':
                        $color = '#2ecc71'; // Green
                        break;
                    case 'komsel':
                        $color = '#f39c12'; // Orange
                        break;
                    case 'pelayanan':
                        $color = '#9b59b6'; // Purple
                        break;
                    case 'pelatihan':
                        $color = '#e74c3c'; // Red
                        break;
                }
                
                $tanggalKegiatan = $p->tanggal_kegiatan instanceof \Carbon\Carbon 
                    ? $p->tanggal_kegiatan->format('Y-m-d')
                    : $p->tanggal_kegiatan;
                
                $events[] = [
                    'id' => $p->id_pelaksanaan,
                    'title' => $p->kegiatan->nama_kegiatan,
                    'start' => $tanggalKegiatan . 'T' . $p->jam_mulai,
                    'end' => $tanggalKegiatan . 'T' . $p->jam_selesai,
                    'color' => $color,
                    'description' => $p->kegiatan->deskripsi ?? '',
                    'location' => $p->lokasi ?? '',
                    'url' => route('pelaksanaan.show', $p->id_pelaksanaan),
                    'extendedProps' => [
                        'description' => $p->kegiatan->deskripsi ?? '',
                        'location' => $p->lokasi ?? '',
                        'type' => $p->kegiatan->tipe_kegiatan,
                        'is_recurring' => $p->is_recurring ?? false,
                        'recurring_type' => $p->recurring_type ?? null
                    ]
                ];
            }
            
            Log::info('API Events returned: ' . count($events));
            Log::info('=== GET EVENTS API END ===');
            
            return response()->json($events);
            
        } catch (\Exception $e) {
            Log::error('Get events error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat events: ' . $e->getMessage()
            ], 500);
        }
    }
}