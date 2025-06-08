<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\Anggota;
use App\Models\Komsel;
use App\Mail\KomselReminder;
use App\Mail\IbadahReminder;
use App\Mail\PelayananReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDailyReminders extends Command
{
    protected $signature = 'notification:send-reminders
                            {--type=all : Type of reminders (pelayanan|komsel|ibadah|all)}
                            {--when=day_before : When to send (week_before|day_before|day_of)}
                            {--dry-run : Run without sending actual emails}';

    protected $description = 'Send daily reminder notifications';

    public function handle()
    {
        $type = $this->option('type');
        $when = $this->option('when');
        $dryRun = $this->option('dry-run');

        $this->info("Sending {$when} reminders for {$type}...");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No emails will be sent");
        }

        $targetDate = $this->getTargetDate($when);
        $this->info("Target date: " . $targetDate->format('Y-m-d'));

        $sentCount = 0;

        if ($type === 'all' || $type === 'pelayanan') {
            $sentCount += $this->sendPelayananReminders($targetDate, $when, $dryRun);
        }

        if ($type === 'all' || $type === 'komsel') {
            $sentCount += $this->sendKomselReminders($targetDate, $when, $dryRun);
        }

        if ($type === 'all' || $type === 'ibadah') {
            $sentCount += $this->sendIbadahReminders($targetDate, $when, $dryRun);
        }

        $this->info("Total reminders sent: {$sentCount}");
        return 0;
    }

    private function getTargetDate($when)
    {
        switch ($when) {
            case 'week_before':
                return Carbon::now()->addWeek();
            case 'day_of':
                return Carbon::now();
            case 'day_before':
            default:
                return Carbon::now()->addDay();
        }
    }

    private function sendPelayananReminders($targetDate, $when, $dryRun)
    {
        $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
            ->where('tanggal_pelayanan', $targetDate->format('Y-m-d'))
            ->where('status_konfirmasi', 'belum')
            ->get();

        $sent = 0;
        
        foreach ($jadwalPelayanan as $jadwal) {
            if (!$jadwal->anggota || !$jadwal->anggota->email) {
                continue;
            }

            try {
                if (!$dryRun) {
                    Mail::to($jadwal->anggota->email)
                        ->send(new PelayananReminder($jadwal, $when));
                }
                
                $this->info("✓ Pelayanan reminder sent to {$jadwal->anggota->email}");
                $sent++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to send pelayanan reminder: " . $e->getMessage());
                Log::error("Pelayanan reminder failed", [
                    'jadwal_id' => $jadwal->id_pelayanan,
                    'email' => $jadwal->anggota->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $sent;
    }

    private function sendKomselReminders($targetDate, $when, $dryRun)
    {
        $komselEvents = PelaksanaanKegiatan::with(['kegiatan'])
            ->whereHas('kegiatan', function($q) {
                $q->where('tipe_kegiatan', 'komsel');
            })
            ->where('tanggal_kegiatan', $targetDate->format('Y-m-d'))
            ->get();

        $sent = 0;

        foreach ($komselEvents as $event) {
            $komselName = str_replace('Komsel - ', '', $event->kegiatan->nama_kegiatan);
            $komsel = Komsel::where('nama_komsel', $komselName)->first();
            
            if (!$komsel) continue;

            foreach ($komsel->anggota as $anggota) {
                if (!$anggota->email) continue;

                try {
                    if (!$dryRun) {
                        Mail::to($anggota->email)
                            ->send(new KomselReminder($event, $komsel, $anggota, $when));
                    }
                    
                    $this->info("✓ Komsel reminder sent to {$anggota->email}");
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("✗ Failed to send komsel reminder: " . $e->getMessage());
                    Log::error("Komsel reminder failed", [
                        'event_id' => $event->id_pelaksanaan,
                        'email' => $anggota->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $sent;
    }

    private function sendIbadahReminders($targetDate, $when, $dryRun)
    {
        $ibadahEvents = PelaksanaanKegiatan::with(['kegiatan'])
            ->whereHas('kegiatan', function($q) {
                $q->where('tipe_kegiatan', 'ibadah');
            })
            ->where('tanggal_kegiatan', $targetDate->format('Y-m-d'))
            ->get();

        if ($ibadahEvents->isEmpty()) {
            return 0;
        }

        $anggota = Anggota::whereNotNull('email')->get();
        $sent = 0;

        foreach ($ibadahEvents as $event) {
            foreach ($anggota as $member) {
                try {
                    if (!$dryRun) {
                        Mail::to($member->email)
                            ->send(new IbadahReminder($event, $member, $when));
                    }
                    
                    $this->info("✓ Ibadah reminder sent to {$member->email}");
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("✗ Failed to send ibadah reminder: " . $e->getMessage());
                    Log::error("Ibadah reminder failed", [
                        'event_id' => $event->id_pelaksanaan,
                        'email' => $member->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $sent;
    }
}