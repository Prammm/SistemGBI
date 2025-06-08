<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\Anggota;
use App\Models\Komsel;
use App\Mail\PelayananReminder;
use App\Mail\KomselReminder;
use App\Mail\IbadahReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDailyReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 2;

    protected $reminderType;
    protected $targetDate;

    public function __construct($reminderType = 'day_before')
    {
        $this->reminderType = $reminderType;
        $this->targetDate = $this->getTargetDate($reminderType);
    }

    public function handle()
    {
        Log::info("Starting daily reminders processing", [
            'reminder_type' => $this->reminderType,
            'target_date' => $this->targetDate->format('Y-m-d')
        ]);

        $emailJobs = [];

        // Send Pelayanan Reminders
        $emailJobs = array_merge($emailJobs, $this->preparePelayananReminders());

        // Send Komsel Reminders
        $emailJobs = array_merge($emailJobs, $this->prepareKomselReminders());

        // Send Ibadah Reminders
        $emailJobs = array_merge($emailJobs, $this->prepareIbadahReminders());

        // Dispatch all email jobs
        foreach ($emailJobs as $emailJob) {
            SendReminderEmail::dispatch($emailJob);
        }

        Log::info("Daily reminders processing completed", [
            'reminder_jobs_queued' => count($emailJobs)
        ]);
    }

    private function getTargetDate($reminderType)
    {
        switch ($reminderType) {
            case 'week_before':
                return Carbon::now()->addWeek();
            case 'day_of':
                return Carbon::now();
            case 'day_before':
            default:
                return Carbon::now()->addDay();
        }
    }

    private function preparePelayananReminders()
    {
        $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
            ->where('tanggal_pelayanan', $this->targetDate->format('Y-m-d'))
            ->where('status_konfirmasi', 'belum')
            ->get();

        $emailJobs = [];

        foreach ($jadwalPelayanan as $jadwal) {
            if (!$jadwal->anggota || !$jadwal->anggota->email) {
                continue;
            }

            $emailJobs[] = [
                'type' => 'pelayanan_reminder',
                'email' => $jadwal->anggota->email,
                'mail_class' => PelayananReminder::class,
                'mail_data' => [$jadwal, $this->reminderType]
            ];
        }

        return $emailJobs;
    }

    private function prepareKomselReminders()
    {
        $komselEvents = PelaksanaanKegiatan::with(['kegiatan'])
            ->whereHas('kegiatan', function($q) {
                $q->where('tipe_kegiatan', 'komsel');
            })
            ->where('tanggal_kegiatan', $this->targetDate->format('Y-m-d'))
            ->get();

        $emailJobs = [];

        foreach ($komselEvents as $event) {
            $komselName = str_replace('Komsel - ', '', $event->kegiatan->nama_kegiatan);
            $komsel = Komsel::where('nama_komsel', $komselName)->first();
            
            if (!$komsel) continue;

            foreach ($komsel->anggota as $anggota) {
                if (!$anggota->email) continue;

                $emailJobs[] = [
                    'type' => 'komsel_reminder',
                    'email' => $anggota->email,
                    'mail_class' => KomselReminder::class,
                    'mail_data' => [$event, $komsel, $anggota, $this->reminderType]
                ];
            }
        }

        return $emailJobs;
    }

    private function prepareIbadahReminders()
    {
        $ibadahEvents = PelaksanaanKegiatan::with(['kegiatan'])
            ->whereHas('kegiatan', function($q) {
                $q->where('tipe_kegiatan', 'ibadah');
            })
            ->where('tanggal_kegiatan', $this->targetDate->format('Y-m-d'))
            ->get();

        if ($ibadahEvents->isEmpty()) {
            return [];
        }

        $anggota = Anggota::whereNotNull('email')->get();
        $emailJobs = [];

        foreach ($ibadahEvents as $event) {
            foreach ($anggota as $member) {
                $emailJobs[] = [
                    'type' => 'ibadah_reminder',
                    'email' => $member->email,
                    'mail_class' => IbadahReminder::class,
                    'mail_data' => [$event, $member, $this->reminderType]
                ];
            }
        }

        return $emailJobs;
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Daily reminders job failed", [
            'reminder_type' => $this->reminderType,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}