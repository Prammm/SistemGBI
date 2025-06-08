<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;
    public $backoff = [30, 60, 120]; // Wait 30s, 1m, 2m between retries

    protected $emailJob;

    public function __construct($emailJob)
    {
        $this->emailJob = $emailJob;
    }

    public function handle()
    {
        try {
            $mailClass = $this->emailJob['mail_class'];
            $mailData = $this->emailJob['mail_data'];
            
            $mail = new $mailClass(...$mailData);
            
            Mail::to($this->emailJob['email'])->send($mail);
            
            Log::info("Reminder email sent successfully", [
                'type' => $this->emailJob['type'],
                'email' => $this->emailJob['email']
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send reminder email", [
                'type' => $this->emailJob['type'],
                'email' => $this->emailJob['email'],
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Reminder email job failed permanently", [
            'type' => $this->emailJob['type'],
            'email' => $this->emailJob['email'],
            'error' => $exception->getMessage()
        ]);
    }
}