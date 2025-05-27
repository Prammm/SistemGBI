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

class PelayananReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $jadwal;

    /**
     * Create a new message instance.
     */
    public function __construct(JadwalPelayanan $jadwal)
    {
        $this->jadwal = $jadwal;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Jadwal Pelayanan - GBI Situbondo',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pelayanan-reminder',
            with: [
                'nama' => $this->jadwal->anggota->nama,
                'kegiatan' => $this->jadwal->kegiatan->nama_kegiatan,
                'posisi' => $this->jadwal->posisi,
                'tanggal' => Carbon::parse($this->jadwal->tanggal_pelayanan)->format('d F Y'),
                'jam_mulai' => isset($this->jadwal->kegiatan->pelaksanaan) ? Carbon::parse($this->jadwal->kegiatan->pelaksanaan->first()->jam_mulai)->format('H:i') : '00:00',
                'lokasi' => isset($this->jadwal->kegiatan->pelaksanaan) ? $this->jadwal->kegiatan->pelaksanaan->first()->lokasi : 'Gereja',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}