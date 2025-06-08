<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    protected $notification;

    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    public function handle()
    {
        try {
            $mailClass = $this->notification['mail_class'];
            $mailData = $this->notification['mail_data'];
            
            $mail = new $mailClass(...array_values($mailData));
            
            Mail::to($this->notification['email'])->send($mail);
            
            Log::info("Notification email sent successfully", [
                'type' => $this->notification['type'],
                'email' => $this->notification['email']
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send notification email", [
                'type' => $this->notification['type'],
                'email' => $this->notification['email'],
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Notification email job failed permanently", [
            'type' => $this->notification['type'],
            'email' => $this->notification['email'],
            'error' => $exception->getMessage()
        ]);
    }
}