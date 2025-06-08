<?php
// app/Jobs/ProcessAbsenceNotifications.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

class ProcessAbsenceNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected $days;
    protected $threshold;

    public function __construct($days = 30, $threshold = 3)
    {
        $this->days = $days;
        $this->threshold = $threshold;
    }

    public function handle()
    {
        Log::info("Starting absence notification processing", [
            'days' => $this->days,
            'threshold' => $this->threshold
        ]);

        $startDate = Carbon::now()->subDays($this->days);
        $endDate = Carbon::now();

        // Get all recent events
        $recentEvents = PelaksanaanKegiatan::with(['kegiatan', 'kehadiran.anggota'])
            ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
            ->where('tanggal_kegiatan', '<', Carbon::now())
            ->orderBy('tanggal_kegiatan', 'desc')
            ->get();

        // Group events by type
        $ibadahEvents = $recentEvents->filter(function($event) {
            return $event->kegiatan && $event->kegiatan->tipe_kegiatan === 'ibadah';
        });

        $komselEvents = $recentEvents->filter(function($event) {
            return $event->kegiatan && $event->kegiatan->tipe_kegiatan === 'komsel';
        });

        $notifications = [];

        // Check Ibadah absences
        $this->checkIbadahAbsences($ibadahEvents, $notifications);

        // Check Komsel absences
        $this->checkKomselAbsences($komselEvents, $notifications);

        // Dispatch email jobs
        foreach ($notifications as $notification) {
            SendNotificationEmail::dispatch($notification);
        }

        Log::info("Absence notification processing completed", [
            'notifications_queued' => count($notifications)
        ]);
    }

    private function checkIbadahAbsences($ibadahEvents, &$notifications)
    {
        if ($ibadahEvents->isEmpty()) {
            return;
        }

        $allAnggota = Anggota::whereNotNull('email')->get();

        foreach ($allAnggota as $anggota) {
            $consecutiveAbsences = $this->getConsecutiveAbsences($anggota, $ibadahEvents);
            
            if ($consecutiveAbsences >= $this->threshold) {
                $recentAbsences = $this->getRecentAbsencesDetails($anggota, $ibadahEvents->take(5));

                // Notify pengurus gereja (role 2)
                $pengurus = User::with('anggota')
                    ->where('id_role', 2)
                    ->whereNotNull('email')
                    ->get();

                foreach ($pengurus as $pengurusUser) {
                    $notifications[] = [
                        'type' => 'absence_leader',
                        'email' => $pengurusUser->email,
                        'mail_class' => AbsenceNotificationToLeader::class,
                        'mail_data' => [
                            'anggota' => $anggota,
                            'absence_count' => $consecutiveAbsences,
                            'tipe_kegiatan' => 'ibadah',
                            'leader' => $pengurusUser->anggota,
                            'recent_absences' => $recentAbsences
                        ]
                    ];
                }

                // Notify the member
                $notifications[] = [
                    'type' => 'absence_member',
                    'email' => $anggota->email,
                    'mail_class' => AbsenceNotificationToMember::class,
                    'mail_data' => [
                        'anggota' => $anggota,
                        'absence_count' => $consecutiveAbsences,
                        'tipe_kegiatan' => 'ibadah',
                        'recent_absences' => $recentAbsences
                    ]
                ];
            }
        }
    }

    private function checkKomselAbsences($komselEvents, &$notifications)
    {
        if ($komselEvents->isEmpty()) {
            return;
        }

        $komselGrouped = $komselEvents->groupBy(function($event) {
            return str_replace('Komsel - ', '', $event->kegiatan->nama_kegiatan);
        });

        foreach ($komselGrouped as $komselName => $events) {
            $komsel = Komsel::where('nama_komsel', $komselName)->first();
            
            if (!$komsel) continue;

            foreach ($komsel->anggota as $anggota) {
                if (!$anggota->email) continue;

                $consecutiveAbsences = $this->getConsecutiveAbsences($anggota, $events);
                
                if ($consecutiveAbsences >= $this->threshold) {
                    $recentAbsences = $this->getRecentAbsencesDetails($anggota, $events->take(5));

                    // Notify komsel leader
                    if ($komsel->pemimpin && $komsel->pemimpin->email) {
                        $notifications[] = [
                            'type' => 'absence_leader',
                            'email' => $komsel->pemimpin->email,
                            'mail_class' => AbsenceNotificationToLeader::class,
                            'mail_data' => [
                                'anggota' => $anggota,
                                'absence_count' => $consecutiveAbsences,
                                'tipe_kegiatan' => 'komsel',
                                'leader' => $komsel->pemimpin,
                                'recent_absences' => $recentAbsences
                            ]
                        ];
                    }

                    // Notify the member
                    $notifications[] = [
                        'type' => 'absence_member',
                        'email' => $anggota->email,
                        'mail_class' => AbsenceNotificationToMember::class,
                        'mail_data' => [
                            'anggota' => $anggota,
                            'absence_count' => $consecutiveAbsences,
                            'tipe_kegiatan' => 'komsel',
                            'recent_absences' => $recentAbsences
                        ]
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
                break;
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

    public function failed(\Throwable $exception)
    {
        Log::error("Absence notification job failed", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}