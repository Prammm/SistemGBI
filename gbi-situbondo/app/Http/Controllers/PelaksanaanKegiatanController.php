<?php

namespace App\Http\Controllers;

use App\Models\PelaksanaanKegiatan;
use App\Models\Kegiatan;
use App\Http\Requests\StorePelaksanaanKegiatanRequest;
use App\Http\Requests\UpdatePelaksanaanKegiatanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PelaksanaanKegiatanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // If admin only (role 2 pengurus dihapus)
        if ($user->id_role == 1) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->orderByRaw('
                    CASE 
                        WHEN DATE(tanggal_kegiatan) = CURDATE() THEN 0
                        WHEN DATE(tanggal_kegiatan) > CURDATE() THEN 1 
                        ELSE 2 
                    END,
                    DATE(tanggal_kegiatan) ASC,
                    jam_mulai ASC
                ')
                ->get();
        }
        // If petugas pelayanan (role 3) or anggota jemaat (role 4)
        else {
            $anggota = $user->anggota;
            
            if (!$anggota) {
                // If user is not associated with any anggota, only show general church activities
                $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                    ->whereHas('kegiatan', function($query) {
                        $query->where('tipe_kegiatan', '!=', 'komsel');
                    })
                    ->orderByRaw('
                        CASE 
                            WHEN DATE(tanggal_kegiatan) = CURDATE() THEN 0
                            WHEN DATE(tanggal_kegiatan) > CURDATE() THEN 1 
                            ELSE 2 
                        END,
                        DATE(tanggal_kegiatan) ASC,
                        jam_mulai ASC
                    ')
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
                        if (!empty($komselActivityPatterns)) {
                            // Include user's komsel activities
                            $query->whereHas('kegiatan', function($subquery) use ($komselActivityPatterns) {
                                $subquery->where('tipe_kegiatan', 'komsel')
                                    ->whereIn('nama_kegiatan', $komselActivityPatterns);
                            });
                        }
                        
                        // Include non-komsel activities (church-wide)
                        $query->orWhereHas('kegiatan', function($subquery) {
                            $subquery->where('tipe_kegiatan', '!=', 'komsel');
                        });
                    })
                    ->orderByRaw('
                        CASE 
                            WHEN DATE(tanggal_kegiatan) = CURDATE() THEN 0
                            WHEN DATE(tanggal_kegiatan) > CURDATE() THEN 1 
                            ELSE 2 
                        END,
                        DATE(tanggal_kegiatan) ASC,
                        jam_mulai ASC
                    ')
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
        
        // If petugas pelayanan (role 3) or anggota jemaat (role 4) - not admin
        if ($user->id_role > 1) {
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

    public function edit(PelaksanaanKegiatan $pelaksanaan)
    {
        // Check if this schedule can be edited (must not have started yet)
        $eventStartTime = $this->getEventStartTime($pelaksanaan);
        $canEdit = Carbon::now()->lt($eventStartTime);
        
        if (!$canEdit) {
            return redirect()->route('pelaksanaan.index')
                ->with('error', 'Jadwal kegiatan yang sudah berlangsung tidak dapat diedit.');
        }

        $kegiatan = Kegiatan::orderBy('nama_kegiatan')->get();
        
        // Additional data for recurring schedules
        $canEditEndDate = true;
        if ($pelaksanaan->is_recurring && $pelaksanaan->recurring_end_date) {
            $canEditEndDate = Carbon::now()->lt($pelaksanaan->recurring_end_date);
        }
        
        return view('pelaksanaan.edit', compact('pelaksanaan', 'kegiatan', 'canEditEndDate'));
    }

    public function update(UpdatePelaksanaanKegiatanRequest $request, PelaksanaanKegiatan $pelaksanaan)
    {
        try {
            DB::beginTransaction();

            $updateData = [
                'id_kegiatan' => $request->id_kegiatan,
                'tanggal_kegiatan' => $request->tanggal_kegiatan,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'lokasi' => $request->lokasi,
            ];

            // Handle recurring schedule updates
            if ($pelaksanaan->is_recurring) {
                $oldEndDate = $pelaksanaan->recurring_end_date;
                $newEndDate = Carbon::parse($request->recurring_end_date);
                
                $updateData['recurring_type'] = $request->recurring_type;
                $updateData['recurring_end_date'] = $request->recurring_end_date;
                
                // Update the parent schedule
                $pelaksanaan->update($updateData);
                
                // Update all future child schedules (that haven't started yet)
                $this->updateFutureChildSchedules($pelaksanaan, $updateData);
                
                // If end date is extended, generate new schedules
                if ($newEndDate->gt($oldEndDate)) {
                    $this->generateAdditionalSchedules($pelaksanaan, $oldEndDate, $newEndDate);
                }
                
                // If end date is shortened, remove schedules beyond new end date
                if ($newEndDate->lt($oldEndDate)) {
                    $this->removeFutureSchedules($pelaksanaan, $newEndDate);
                }
                
            } else {
                // Regular schedule update
                $pelaksanaan->update($updateData);
            }

            DB::commit();

            $message = $pelaksanaan->is_recurring 
                ? 'Jadwal kegiatan berulang berhasil diperbarui!' 
                : 'Jadwal kegiatan berhasil diperbarui!';

            return redirect()->route('pelaksanaan.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
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
     * Helper method to get event start time
     */
    private function getEventStartTime($pelaksanaan)
    {
        $eventDate = Carbon::parse($pelaksanaan->tanggal_kegiatan);
        try {
            return $eventDate->copy()->setTimeFromTimeString($pelaksanaan->jam_mulai);
        } catch (\Exception $e) {
            return Carbon::createFromFormat('Y-m-d H:i', 
                $eventDate->format('Y-m-d') . ' ' . substr($pelaksanaan->jam_mulai, 0, 5));
        }
    }

    /**
     * Update all future child schedules that haven't started yet
     */
    private function updateFutureChildSchedules($parentSchedule, $updateData)
    {
        $now = Carbon::now();
        
        $futureSchedules = $parentSchedule->children()
            ->where('tanggal_kegiatan', '>=', $now->format('Y-m-d'))
            ->get();
            
        foreach ($futureSchedules as $schedule) {
            $eventStartTime = $this->getEventStartTime($schedule);
            
            // Only update if the event hasn't started yet
            if ($now->lt($eventStartTime)) {
                $schedule->update([
                    'jam_mulai' => $updateData['jam_mulai'],
                    'jam_selesai' => $updateData['jam_selesai'],
                    'lokasi' => $updateData['lokasi'],
                ]);
            }
        }
    }

    /**
     * Generate additional schedules when end date is extended
     */
    private function generateAdditionalSchedules($parentSchedule, $oldEndDate, $newEndDate)
    {
        // Get the last existing schedule date
        $lastExistingDate = $parentSchedule->children()
            ->orderBy('tanggal_kegiatan', 'desc')
            ->first();
            
        $startDate = $lastExistingDate ? 
            Carbon::parse($lastExistingDate->tanggal_kegiatan) : 
            $parentSchedule->tanggal_kegiatan->copy();
            
        $schedules = [];
        $currentDate = $startDate->copy();
        
        // Start from the next occurrence after the last existing schedule
        if ($parentSchedule->recurring_type === 'weekly') {
            $currentDate->addWeek();
        } elseif ($parentSchedule->recurring_type === 'monthly') {
            $currentDate->addMonth();
        }
        
        // Generate until the new end date
        while ($currentDate->lte($newEndDate)) {
            // Check if this date already exists to prevent duplicates
            $exists = $parentSchedule->children()
                ->where('tanggal_kegiatan', $currentDate->format('Y-m-d'))
                ->exists();
                
            if (!$exists) {
                $schedules[] = [
                    'id_kegiatan' => $parentSchedule->id_kegiatan,
                    'tanggal_kegiatan' => $currentDate->format('Y-m-d'),
                    'jam_mulai' => $parentSchedule->jam_mulai,
                    'jam_selesai' => $parentSchedule->jam_selesai,
                    'lokasi' => $parentSchedule->lokasi,
                    'is_recurring' => false,
                    'parent_id' => $parentSchedule->id_pelaksanaan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            if ($parentSchedule->recurring_type === 'weekly') {
                $currentDate->addWeek();
            } elseif ($parentSchedule->recurring_type === 'monthly') {
                $currentDate->addMonth();
            }
        }
        
        if (!empty($schedules)) {
            PelaksanaanKegiatan::insert($schedules);
        }
    }

    /**
     * Remove schedules beyond the new end date (but preserve data)
     */
    private function removeFutureSchedules($parentSchedule, $newEndDate)
    {
        $now = Carbon::now();
        
        // Only remove schedules that:
        // 1. Are beyond the new end date
        // 2. Haven't started yet (to preserve data for completed events)
        $scheduleToRemove = $parentSchedule->children()
            ->where('tanggal_kegiatan', '>', $newEndDate->format('Y-m-d'))
            ->get();
            
        foreach ($scheduleToRemove as $schedule) {
            $eventStartTime = $this->getEventStartTime($schedule);
            
            // Only delete if the event hasn't started yet
            if ($now->lt($eventStartTime)) {
                $schedule->delete();
            }
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