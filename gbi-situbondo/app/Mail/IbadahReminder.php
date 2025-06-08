<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PelaksanaanKegiatan;
use App\Models\Anggota;
use Carbon\Carbon;

class EnhancedIbadahReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ibadah;
    public $anggota;
    public $reminderType;

    public function __construct(PelaksanaanKegiatan $ibadah, Anggota $anggota, $reminderType = 'day_before')
    {
        $this->ibadah = $ibadah;
        $this->anggota = $anggota;
        $this->reminderType = $reminderType;
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'week_before' => 'Pengingat Ibadah Minggu Depan',
            'day_before' => 'Pengingat Ibadah Besok',
            'day_of' => 'Pengingat Ibadah Hari Ini'
        ];

        return new Envelope(
            subject: ($subjects[$this->reminderType] ?? $subjects['day_before']) . ' - GBI Situbondo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enhanced-ibadah-reminder',
            with: [
                'nama_anggota' => $this->anggota->nama,
                'nama_ibadah' => $this->ibadah->kegiatan->nama_kegiatan,
                'tanggal' => Carbon::parse($this->ibadah->tanggal_kegiatan)->format('d F Y'),
                'hari' => Carbon::parse($this->ibadah->tanggal_kegiatan)->isoFormat('dddd'),
                'jam_mulai' => Carbon::parse($this->ibadah->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($this->ibadah->jam_selesai)->format('H:i'),
                'lokasi' => $this->ibadah->lokasi ?: 'Gereja GBI Situbondo',
                'reminder_type' => $this->reminderType,
                'attendance_url' => route('kehadiran.scan', $this->ibadah->id_pelaksanaan),
                'contact_info' => [
                    'phone' => env('CHURCH_PHONE', '+62 123 456 789'),
                    'email' => env('CHURCH_EMAIL', 'info@gbisitubondo.org')
                ]
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}