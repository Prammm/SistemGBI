<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PelayananController;

class AutoRejectExpiredSchedules extends Command
{
    protected $signature = 'pelayanan:auto-reject-expired';
    protected $description = 'Auto reject expired schedules that are still pending';

    public function handle()
    {
        $controller = new PelayananController();
        $rejectedCount = $controller->autoRejectExpiredSchedules();
        
        $this->info("Auto-rejected {$rejectedCount} expired schedules");
        
        return 0;
    }
}