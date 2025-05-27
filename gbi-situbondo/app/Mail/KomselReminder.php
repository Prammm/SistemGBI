<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PelaksanaanKegiatan;
use App\Models\Komsel;
use Carbon\Carbon;

class KomselReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $pelaksanaan;
    public $komsel;

    /**
     * Create a new message instance.
     */
    public function __construct(PelaksanaanKegiatan $pelaksanaan, Komsel $komsel)
    {
        $this->pelaksanaan = $pelaksanaan;
        $this->komsel = $komsel;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Pertemuan Komsel - GBI Situbondo',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.komsel-reminder',
            with: [
                'nama_komsel' => $this->komsel->nama_komsel,
                'tanggal' => Carbon::parse($this->pelaksanaan->tanggal_kegiatan)->format('d F Y'),
                'jam_mulai' => Carbon::parse($this->pelaksanaan->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($this->pelaksanaan->jam_selesai)->format('H:i'),
                'lokasi' => $this->pelaksanaan->lokasi ?: $this->komsel->lokasi ?: 'Gereja',
                'pemimpin' => $this->komsel->pemimpin ? $this->komsel->pemimpin->nama : 'Belum ditentukan',
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