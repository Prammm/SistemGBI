<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PelaksanaanKegiatan;
use Carbon\Carbon;

class IbadahReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $ibadah;

    /**
     * Create a new message instance.
     */
    public function __construct(PelaksanaanKegiatan $ibadah)
    {
        $this->ibadah = $ibadah;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Ibadah - GBI Situbondo',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ibadah-reminder',
            with: [
                'nama_ibadah' => $this->ibadah->kegiatan->nama_kegiatan,
                'tanggal' => Carbon::parse($this->ibadah->tanggal_kegiatan)->format('d F Y'),
                'jam_mulai' => Carbon::parse($this->ibadah->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($this->ibadah->jam_selesai)->format('H:i'),
                'lokasi' => $this->ibadah->lokasi ?: 'Gereja',
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