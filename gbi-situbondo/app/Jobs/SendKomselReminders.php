<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PelaksanaanKegiatan;
use App\Models\Komsel;
use App\Mail\KomselReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendKomselReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 2;
    public $backoff = [60];

    protected $targetDate;
    protected $when;

    public function __construct($targetDate, $when = 'day_before')
    {
        $this->targetDate = $targetDate;
        $this->when = $when;
        $this->onQueue('notifications');
    }

    public function handle()
    {
        Log::info("SendKomselReminders job started", [
            'target_date' => $this->targetDate,
            'when' => $this->when
        ]);

        try {
            $komselEvents = PelaksanaanKegiatan::with(['kegiatan'])
                ->whereHas('kegiatan', function($q) {
                    $q->where('tipe_kegiatan', 'komsel');
                })
                ->where('tanggal_kegiatan', $this->targetDate)
                ->get();

            $sentCount = 0;
            $failedCount = 0;

            foreach ($komselEvents as $event) {
                $komselName = str_replace('Komsel - ', '', $event->kegiatan->nama_kegiatan);
                $komsel = Komsel::where('nama_komsel', $komselName)->first();
                
                if (!$komsel) continue;

                foreach ($komsel->anggota as $anggota) {
                    if (!$anggota->email) {
                        $failedCount++;
                        continue;
                    }

                    try {
                        Mail::to($anggota->email)
                            ->send(new KomselReminder($event, $komsel, $anggota, $this->when));
                        
                        $sentCount++;
                        
                        Log::info("Komsel reminder sent successfully", [
                            'event_id' => $event->id_pelaksanaan,
                            'email' => $anggota->email,
                            'komsel' => $komselName
                        ]);
                        
                        usleep(200000); // 0.2 seconds delay
                        
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Failed to send komsel reminder", [
                            'event_id' => $event->id_pelaksanaan,
                            'email' => $anggota->email,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("SendKomselReminders job completed", [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount
            ]);

        } catch (\Exception $e) {
            Log::error("SendKomselReminders job failed", [
                'error' => $e->getMessage(),
                'target_date' => $this->targetDate
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("SendKomselReminders job failed permanently", [
            'target_date' => $this->targetDate,
            'error' => $exception->getMessage()
        ]);
    }
}