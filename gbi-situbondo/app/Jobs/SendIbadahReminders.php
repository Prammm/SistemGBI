<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PelaksanaanKegiatan;
use App\Models\Anggota;
use App\Mail\IbadahReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendIbadahReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900; // 15 minutes (more time for sending to all members)
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
        Log::info("SendIbadahReminders job started", [
            'target_date' => $this->targetDate,
            'when' => $this->when
        ]);

        try {
            $ibadahEvents = PelaksanaanKegiatan::with(['kegiatan'])
                ->whereHas('kegiatan', function($q) {
                    $q->where('tipe_kegiatan', 'ibadah');
                })
                ->where('tanggal_kegiatan', $this->targetDate)
                ->get();

            if ($ibadahEvents->isEmpty()) {
                Log::info("No ibadah events found for date", ['target_date' => $this->targetDate]);
                return;
            }

            $anggota = Anggota::whereNotNull('email')->get();
            $sentCount = 0;
            $failedCount = 0;

            foreach ($ibadahEvents as $event) {
                foreach ($anggota as $member) {
                    try {
                        Mail::to($member->email)
                            ->send(new IbadahReminder($event, $member, $this->when));
                        
                        $sentCount++;
                        
                        Log::debug("Ibadah reminder sent successfully", [
                            'event_id' => $event->id_pelaksanaan,
                            'email' => $member->email
                        ]);
                        
                        usleep(100000); // 0.1 seconds delay
                        
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Failed to send ibadah reminder", [
                            'event_id' => $event->id_pelaksanaan,
                            'email' => $member->email,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("SendIbadahReminders job completed", [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount
            ]);

        } catch (\Exception $e) {
            Log::error("SendIbadahReminders job failed", [
                'error' => $e->getMessage(),
                'target_date' => $this->targetDate
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("SendIbadahReminders job failed permanently", [
            'target_date' => $this->targetDate,
            'error' => $exception->getMessage()
        ]);
    }
}