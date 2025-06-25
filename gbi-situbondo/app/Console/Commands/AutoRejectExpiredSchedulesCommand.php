<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JadwalPelayanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoRejectExpiredSchedulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pelayanan:auto-reject-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reject expired service schedules that are still pending confirmation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-reject process for expired schedules...');
        
        // Find expired schedules with status 'belum'
        $expiredSchedules = JadwalPelayanan::where('status_konfirmasi', 'belum')
            ->where('tanggal_pelayanan', '<', Carbon::now()->format('Y-m-d'))
            ->with(['anggota', 'kegiatan', 'pelaksanaan'])
            ->get();
        
        $rejectedCount = 0;
        
        foreach ($expiredSchedules as $jadwal) {
            $jadwal->update(['status_konfirmasi' => 'tolak']);
            
            // Log the auto-rejection
            Log::info("Auto-rejected expired schedule", [
                'id_pelayanan' => $jadwal->id_pelayanan,
                'anggota' => $jadwal->anggota->nama ?? 'Unknown',
                'tanggal_pelayanan' => $jadwal->tanggal_pelayanan,
                'posisi' => $jadwal->posisi,
                'kegiatan' => $jadwal->kegiatan->nama_kegiatan ?? 'Unknown'
            ]);
            
            $rejectedCount++;
        }
        
        if ($rejectedCount > 0) {
            $this->info("Auto-rejected {$rejectedCount} expired schedules");
            Log::info("Auto-rejected {$rejectedCount} expired schedules");
        } else {
            $this->info("No expired schedules found to reject");
        }
        
        return Command::SUCCESS;
    }
}