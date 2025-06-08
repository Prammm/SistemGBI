<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Anggota;
use App\Models\PelaksanaanKegiatan;
use App\Models\Kehadiran;
use App\Models\Komsel;
use App\Models\User;
use App\Mail\AbsenceNotificationToLeader;
use App\Mail\AbsenceNotificationToMember;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckConsecutiveAbsences extends Command
{
    protected $signature = 'notification:check-absences
                            {--days=30 : Number of days to check back}
                            {--threshold=3 : Number of consecutive absences to trigger notification}
                            {--dry-run : Run without sending actual emails}';

    protected $description = 'Check for consecutive absences and send notifications';

    public function handle()
    {
        $days = (int) $this->option('days');
        $threshold = (int) $this->option('threshold');
        $dryRun = $this->option('dry-run');

        $this->info("Checking consecutive absences for the last {$days} days with threshold of {$threshold}...");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No emails will be sent");
        }

        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        // Get all recent events
        $recentEvents = PelaksanaanKegiatan::with(['kegiatan', 'kehadiran.anggota'])
            ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
            ->where('tanggal_kegiatan', '<', Carbon::now()) // Only past events
            ->orderBy('tanggal_kegiatan', 'desc')
            ->get();

        $this->info("Found {$recentEvents->count()} events in the period");

        // Group events by type
        $ibadahEvents = $recentEvents->filter(function($event) {
            return $event->kegiatan && $event->kegiatan->tipe_kegiatan === 'ibadah';
        });

        $komselEvents = $recentEvents->filter(function($event) {
            return $event->kegiatan && $event->kegiatan->tipe_kegiatan === 'komsel';
        });

        $notifications = [];

        // Check Ibadah absences
        $this->checkIbadahAbsences($ibadahEvents, $threshold, $notifications);

        // Check Komsel absences
        $this->checkKomselAbsences($komselEvents, $threshold, $notifications);

        // Send notifications
        $sentCount = 0;
        foreach ($notifications as $notification) {
            if (!$dryRun) {
                try {
                    Mail::to($notification['email'])->send($notification['mail']);
                    $sentCount++;
                    $this->info("✓ Sent notification to {$notification['email']} ({$notification['type']})");
                } catch (\Exception $e) {
                    $this->error("✗ Failed to send to {$notification['email']}: " . $e->getMessage());
                    Log::error("Email notification failed", [
                        'email' => $notification['email'],
                        'type' => $notification['type'],
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $this->line("Would send {$notification['type']} to {$notification['email']}");
                $sentCount++;
            }
        }

        $this->info("Processed {$sentCount} notifications");
        return 0;
    }

    private function checkIbadahAbsences($ibadahEvents, $threshold, &$notifications)
    {
        if ($ibadahEvents->isEmpty()) {
            $this->info("No ibadah events found");
            return;
        }

        // Get all anggota who should attend ibadah
        $allAnggota = Anggota::whereNotNull('email')->get();

        foreach ($allAnggota as $anggota) {
            $consecutiveAbsences = $this->getConsecutiveAbsences($anggota, $ibadahEvents);
            
            if ($consecutiveAbsences >= $threshold) {
                $this->warn("Found {$consecutiveAbsences} consecutive ibadah absences for {$anggota->nama}");
                
                // Get recent absences for context
                $recentAbsences = $this->getRecentAbsencesDetails($anggota, $ibadahEvents->take(5));

                // Notify pengurus gereja (role 2)
                $pengurus = User::with('anggota')
                    ->where('id_role', 2)
                    ->whereNotNull('email')
                    ->get();

                foreach ($pengurus as $pengurusUser) {
                    $notifications[] = [
                        'email' => $pengurusUser->email,
                        'type' => 'absence_notification_leader',
                        'mail' => new AbsenceNotificationToLeader(
                            $anggota, 
                            $consecutiveAbsences, 
                            'ibadah', 
                            $pengurusUser->anggota,
                            $recentAbsences
                        )
                    ];
                }

                // Notify the member
                $notifications[] = [
                    'email' => $anggota->email,
                    'type' => 'absence_notification_member',
                    'mail' => new AbsenceNotificationToMember(
                        $anggota, 
                        $consecutiveAbsences, 
                        'ibadah',
                        $recentAbsences
                    )
                ];
            }
        }
    }

    private function checkKomselAbsences($komselEvents, $threshold, &$notifications)
    {
        if ($komselEvents->isEmpty()) {
            $this->info("No komsel events found");
            return;
        }

        // Group komsel events by komsel
        $komselGrouped = $komselEvents->groupBy(function($event) {
            return str_replace('Komsel - ', '', $event->kegiatan->nama_kegiatan);
        });

        foreach ($komselGrouped as $komselName => $events) {
            $komsel = Komsel::where('nama_komsel', $komselName)->first();
            
            if (!$komsel) continue;

            // Check each komsel member
            foreach ($komsel->anggota as $anggota) {
                if (!$anggota->email) continue;

                $consecutiveAbsences = $this->getConsecutiveAbsences($anggota, $events);
                
                if ($consecutiveAbsences >= $threshold) {
                    $this->warn("Found {$consecutiveAbsences} consecutive komsel absences for {$anggota->nama} in {$komselName}");
                    
                    $recentAbsences = $this->getRecentAbsencesDetails($anggota, $events->take(5));

                    // Notify komsel leader
                    if ($komsel->pemimpin && $komsel->pemimpin->email) {
                        $notifications[] = [
                            'email' => $komsel->pemimpin->email,
                            'type' => 'absence_notification_leader',
                            'mail' => new AbsenceNotificationToLeader(
                                $anggota, 
                                $consecutiveAbsences, 
                                'komsel', 
                                $komsel->pemimpin,
                                $recentAbsences
                            )
                        ];
                    }

                    // Notify the member
                    $notifications[] = [
                        'email' => $anggota->email,
                        'type' => 'absence_notification_member',
                        'mail' => new AbsenceNotificationToMember(
                            $anggota, 
                            $consecutiveAbsences, 
                            'komsel',
                            $recentAbsences
                        )
                    ];
                }
            }
        }
    }

    private function getConsecutiveAbsences($anggota, $events)
    {
        $consecutiveCount = 0;
        
        foreach ($events as $event) {
            $attended = $event->kehadiran->contains('id_anggota', $anggota->id_anggota);
            
            if (!$attended) {
                $consecutiveCount++;
            } else {
                break; // Reset if they attended
            }
        }
        
        return $consecutiveCount;
    }

    private function getRecentAbsencesDetails($anggota, $events)
    {
        $absences = [];
        
        foreach ($events as $event) {
            $attended = $event->kehadiran->contains('id_anggota', $anggota->id_anggota);
            
            if (!$attended) {
                $absences[] = [
                    'kegiatan' => $event->kegiatan->nama_kegiatan,
                    'tanggal' => Carbon::parse($event->tanggal_kegiatan)->format('d F Y'),
                    'lokasi' => $event->lokasi
                ];
            }
        }
        
        return $absences;
    }
}