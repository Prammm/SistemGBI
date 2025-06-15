<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\JadwalPelayanan;
use App\Mail\PelayananReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPelayananReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 1; // Ubah ke 1 untuk debugging
    
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
        Log::info("=== SendPelayananReminders JOB STARTED ===", [
            'target_date' => $this->targetDate,
            'when' => $this->when,
            'job_id' => $this->job->getJobId(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            // STEP 1: Query jadwal
            $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->where('tanggal_pelayanan', $this->targetDate)
                ->where('status_konfirmasi', 'belum')
                ->get();

            Log::info("STEP 1: Query completed", [
                'total_found' => $jadwalPelayanan->count(),
                'target_date' => $this->targetDate
            ]);

            // STEP 2: Filter yang punya email
            $jadwalWithEmail = $jadwalPelayanan->filter(function($jadwal) {
                return $jadwal->anggota && $jadwal->anggota->email;
            });

            Log::info("STEP 2: Filter by email", [
                'with_email' => $jadwalWithEmail->count(),
                'without_email' => $jadwalPelayanan->count() - $jadwalWithEmail->count()
            ]);

            // STEP 3: Detail setiap jadwal
            foreach ($jadwalPelayanan as $index => $jadwal) {
                Log::info("Jadwal #{$index}", [
                    'id_pelayanan' => $jadwal->id_pelayanan,
                    'anggota_nama' => $jadwal->anggota ? $jadwal->anggota->nama : 'NULL',
                    'anggota_email' => $jadwal->anggota ? $jadwal->anggota->email : 'NULL',
                    'posisi' => $jadwal->posisi,
                    'status_konfirmasi' => $jadwal->status_konfirmasi,
                    'tanggal_pelayanan' => $jadwal->tanggal_pelayanan
                ]);
            }

            if ($jadwalWithEmail->isEmpty()) {
                Log::warning("No schedules with email found for sending");
                return;
            }

            $sentCount = 0;
            $failedCount = 0;

            // STEP 4: Kirim email satu per satu
            foreach ($jadwalWithEmail as $jadwal) {
                try {
                    Log::info("Attempting to send email", [
                        'to' => $jadwal->anggota->email,
                        'anggota' => $jadwal->anggota->nama,
                        'posisi' => $jadwal->posisi
                    ]);

                    // Test mail configuration terlebih dahulu
                    $mailConfig = [
                        'driver' => config('mail.default'),
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'username' => config('mail.mailers.smtp.username'),
                        'encryption' => config('mail.mailers.smtp.encryption'),
                    ];
                    
                    Log::info("Mail configuration", $mailConfig);

                    Mail::to($jadwal->anggota->email)
                        ->send(new PelayananReminder($jadwal, $this->when));
                    
                    $sentCount++;
                    
                    Log::info("✅ EMAIL SENT SUCCESSFULLY", [
                        'to' => $jadwal->anggota->email,
                        'anggota' => $jadwal->anggota->nama,
                        'jadwal_id' => $jadwal->id_pelayanan
                    ]);
                    
                    // Delay untuk menghindari spam
                    sleep(1);
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error("❌ EMAIL FAILED", [
                        'to' => $jadwal->anggota->email,
                        'jadwal_id' => $jadwal->id_pelayanan,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info("=== JOB COMPLETED ===", [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_processed' => $jadwalWithEmail->count(),
                'target_date' => $this->targetDate
            ]);

        } catch (\Exception $e) {
            Log::error("=== JOB FAILED ===", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'target_date' => $this->targetDate
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("=== JOB FAILED PERMANENTLY ===", [
            'target_date' => $this->targetDate,
            'when' => $this->when,
            'error' => $exception->getMessage()
        ]);
    }
}