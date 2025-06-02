<?php

namespace App\Http\Controllers;

use App\Models\PelaksanaanKegiatan;
use App\Models\Kegiatan;
use App\Http\Requests\StorePelaksanaanKegiatanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PelaksanaanKegiatanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // If admin, show all activities
        if ($user->id_role <= 2) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->orderByRaw('DATE(tanggal_kegiatan) ASC')
                ->orderBy('jam_mulai', 'asc')
                ->get();
        }
        // If regular member or service staff (roles 3 and 4)
        else {
            $anggota = $user->anggota;
            
            if (!$anggota) {
                // If user is not associated with any anggota, only show general church activities
                $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                    ->whereHas('kegiatan', function($query) {
                        $query->where('tipe_kegiatan', '!=', 'komsel');
                    })
                    ->orderByRaw('DATE(tanggal_kegiatan) ASC')
                    ->orderBy('jam_mulai', 'asc')
                    ->get();
            } else {
                // Get user's komsel names for the activities filter
                $komselNames = $anggota->komsel->pluck('nama_komsel')->toArray();
                $komselActivityPatterns = array_map(function($name) {
                    return 'Komsel - ' . $name;
                }, $komselNames);
                
                // Get activities related to user's komsel or non-komsel activities
                $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                    ->where(function($query) use ($komselActivityPatterns) {
                        // Include user's komsel activities
                        $query->whereHas('kegiatan', function($subquery) use ($komselActivityPatterns) {
                            $subquery->where('tipe_kegiatan', 'komsel')
                                ->where(function($nameQuery) use ($komselActivityPatterns) {
                                    foreach($komselActivityPatterns as $pattern) {
                                        $nameQuery->orWhere('nama_kegiatan', $pattern);
                                    }
                                });
                        })
                        // Include non-komsel activities (church-wide)
                        ->orWhereHas('kegiatan', function($subquery) {
                            $subquery->where('tipe_kegiatan', '!=', 'komsel');
                        });
                    })
                    ->orderByRaw('DATE(tanggal_kegiatan) ASC')
                    ->orderBy('jam_mulai', 'asc')
                    ->get();
            }
        }
        
        return view('pelaksanaan.index', compact('pelaksanaan'));
    }

    public function create()
    {
        $kegiatan = Kegiatan::orderBy('nama_kegiatan')->get();
        return view('pelaksanaan.create', compact('kegiatan'));
    }

    public function store(StorePelaksanaanKegiatanRequest $request)
    {
        try {
            DB::beginTransaction();

            // Create main schedule
            $pelaksanaan = PelaksanaanKegiatan::create([
                'id_kegiatan' => $request->id_kegiatan,
                'tanggal_kegiatan' => $request->tanggal_kegiatan,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'lokasi' => $request->lokasi,
                'is_recurring' => $request->boolean('is_recurring'),
                'recurring_type' => $request->is_recurring ? $request->recurring_type : null,
                'recurring_end_date' => $request->is_recurring ? $request->recurring_end_date : null,
            ]);

            // Generate recurring schedules if enabled
            if ($request->boolean('is_recurring')) {
                $recurringSchedules = $this->generateRecurringSchedules(
                    $pelaksanaan,
                    $request->recurring_type,
                    $request->tanggal_kegiatan,
                    $request->recurring_end_date
                );

                if (!empty($recurringSchedules)) {
                    PelaksanaanKegiatan::insert($recurringSchedules);
                }
            }

            DB::commit();

            $message = $request->boolean('is_recurring') 
                ? 'Jadwal kegiatan berulang berhasil ditambahkan!' 
                : 'Jadwal kegiatan berhasil ditambahkan!';

            return redirect()->route('pelaksanaan.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PelaksanaanKegiatan $pelaksanaan)
    {
        $user = auth()->user();
        
        // If regular member or service staff (not admin)
        if ($user->id_role > 2) {
            $anggota = $user->anggota;
            
            // Check if the activity is a komsel activity
            if ($pelaksanaan->kegiatan && $pelaksanaan->kegiatan->tipe_kegiatan == 'komsel') {
                // Extract komsel name from activity name
                $komselName = str_replace('Komsel - ', '', $pelaksanaan->kegiatan->nama_kegiatan);
                
                // Check if user is a member of this komsel
                if (!$anggota || !$anggota->komsel->contains('nama_komsel', $komselName)) {
                    return redirect()->route('pelaksanaan.index')
                        ->with('error', 'Anda tidak memiliki akses untuk melihat kegiatan komsel ini.');
                }
            }
        }
        
        $pelaksanaan->load('kegiatan', 'kehadiran.anggota');
        
        // Generate QR URL for attendance
        $qrUrl = route('kehadiran.scan', $pelaksanaan->id_pelaksanaan);
        
        return view('pelaksanaan.show', compact('pelaksanaan', 'qrUrl'));
    }

    public function update(Request $request, PelaksanaanKegiatan $pelaksanaan)
    {
        $request->validate([
            'id_kegiatan' => 'required|exists:kegiatan,id_kegiatan',
            'tanggal_kegiatan' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'lokasi' => 'nullable|string|max:255',
        ], [
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai',
        ]);

        $pelaksanaan->update([
            'id_kegiatan' => $request->id_kegiatan,
            'tanggal_kegiatan' => $request->tanggal_kegiatan,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'lokasi' => $request->lokasi,
        ]);

        return redirect()->route('pelaksanaan.index')->with('success', 'Jadwal kegiatan berhasil diperbarui!');
    }

    public function destroy(PelaksanaanKegiatan $pelaksanaan)
    {
        try {
            DB::beginTransaction();

            // If this is a parent recurring schedule, ask for confirmation
            if ($pelaksanaan->is_recurring && $pelaksanaan->children()->count() > 0) {
                // This should be handled by a separate method or confirmation dialog
                $pelaksanaan->children()->delete();
            }

            $pelaksanaan->delete();
            
            DB::commit();
            
            return redirect()->route('pelaksanaan.index')->with('success', 'Jadwal kegiatan berhasil dihapus!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Generate recurring schedules
     */
    private function generateRecurringSchedules($parentSchedule, $recurringType, $startDate, $endDate)
    {
        $schedules = [];
        $currentDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        
        while ($currentDate->lt($endDate)) {
            if ($recurringType === 'weekly') {
                $currentDate->addWeek();
            } elseif ($recurringType === 'monthly') {
                $currentDate->addMonth();
            }
            
            if ($currentDate->lte($endDate)) {
                $schedules[] = [
                    'id_kegiatan' => $parentSchedule->id_kegiatan,
                    'tanggal_kegiatan' => $currentDate->format('Y-m-d'),
                    'jam_mulai' => $parentSchedule->jam_mulai,
                    'jam_selesai' => $parentSchedule->jam_selesai,
                    'lokasi' => $parentSchedule->lokasi,
                    'is_recurring' => false,
                    'recurring_type' => null,
                    'recurring_day' => null,
                    'recurring_end_date' => null,
                    'parent_id' => $parentSchedule->id_pelaksanaan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        return $schedules;
    }

    /**
     * Delete recurring series
     */
    public function destroyRecurringSeries(PelaksanaanKegiatan $pelaksanaan)
    {
        try {
            DB::beginTransaction();

            // Find the parent schedule
            $parentSchedule = $pelaksanaan->parent_id ? $pelaksanaan->parent : $pelaksanaan;
            
            if ($parentSchedule->is_recurring) {
                // Delete all child schedules
                $parentSchedule->children()->delete();
                // Delete parent schedule
                $parentSchedule->delete();
                
                $message = 'Seluruh seri jadwal berulang berhasil dihapus!';
            } else {
                $pelaksanaan->delete();
                $message = 'Jadwal kegiatan berhasil dihapus!';
            }
            
            DB::commit();
            
            return redirect()->route('pelaksanaan.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show all schedules in a recurring series
     */
    public function showRecurringSeries(PelaksanaanKegiatan $pelaksanaan)
    {
        $parentSchedule = $pelaksanaan->parent_id ? $pelaksanaan->parent : $pelaksanaan;
        
        if (!$parentSchedule->is_recurring) {
            return redirect()->route('pelaksanaan.show', $pelaksanaan->id_pelaksanaan);
        }
        
        $allSchedules = collect([$parentSchedule])
            ->merge($parentSchedule->children)
            ->sortBy('tanggal_kegiatan');
            
        return view('pelaksanaan.series', compact('parentSchedule', 'allSchedules'));
    }
}