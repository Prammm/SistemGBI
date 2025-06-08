<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\JadwalPelayanan;
use Carbon\Carbon;

class EnhancedPelayananReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $jadwal;
    public $reminderType; // 'week_before', 'day_before', 'day_of'

    public function __construct(JadwalPelayanan $jadwal, $reminderType = 'day_before')
    {
        $this->jadwal = $jadwal;
        $this->reminderType = $reminderType;
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'week_before' => 'Pengingat Jadwal Pelayanan Minggu Depan',
            'day_before' => 'Pengingat Jadwal Pelayanan Besok',
            'day_of' => 'Pengingat Jadwal Pelayanan Hari Ini'
        ];

        return new Envelope(
            subject: ($subjects[$this->reminderType] ?? $subjects['day_before']) . ' - GBI Situbondo',
        );
    }

    public function content(): Content
    {
        $pelaksanaan = $this->jadwal->pelaksanaan;
        
        return new Content(
            view: 'emails.enhanced-pelayanan-reminder',
            with: [
                'nama' => $this->jadwal->anggota->nama,
                'kegiatan' => $pelaksanaan ? $pelaksanaan->kegiatan->nama_kegiatan : 'Kegiatan',
                'posisi' => $this->jadwal->posisi,
                'tanggal' => Carbon::parse($this->jadwal->tanggal_pelayanan)->format('d F Y'),
                'hari' => Carbon::parse($this->jadwal->tanggal_pelayanan)->isoFormat('dddd'),
                'jam_mulai' => $pelaksanaan ? Carbon::parse($pelaksanaan->jam_mulai)->format('H:i') : '00:00',
                'jam_selesai' => $pelaksanaan ? Carbon::parse($pelaksanaan->jam_selesai)->format('H:i') : '00:00',
                'lokasi' => $pelaksanaan ? $pelaksanaan->lokasi : 'Gereja',
                'reminder_type' => $this->reminderType,
                'confirmation_url' => route('pelayanan.konfirmasi', ['id' => $this->jadwal->id_pelayanan, 'status' => 'terima']),
                'reject_url' => route('pelayanan.konfirmasi', ['id' => $this->jadwal->id_pelayanan, 'status' => 'tolak']),
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