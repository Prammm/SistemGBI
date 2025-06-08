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
use App\Models\Anggota;
use Carbon\Carbon;

class KomselReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pelaksanaan;
    public $komsel;
    public $anggota;
    public $reminderType;

    public function __construct(PelaksanaanKegiatan $pelaksanaan, Komsel $komsel, Anggota $anggota, $reminderType = 'day_before')
    {
        $this->pelaksanaan = $pelaksanaan;
        $this->komsel = $komsel;
        $this->anggota = $anggota;
        $this->reminderType = $reminderType;
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'week_before' => 'Pengingat Pertemuan Komsel Minggu Depan',
            'day_before' => 'Pengingat Pertemuan Komsel Besok',
            'day_of' => 'Pengingat Pertemuan Komsel Hari Ini'
        ];

        return new Envelope(
            subject: ($subjects[$this->reminderType] ?? $subjects['day_before']) . ' - GBI Situbondo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enhanced-komsel-reminder',
            with: [
                'nama_anggota' => $this->anggota->nama,
                'nama_komsel' => $this->komsel->nama_komsel,
                'tanggal' => Carbon::parse($this->pelaksanaan->tanggal_kegiatan)->format('d F Y'),
                'hari' => Carbon::parse($this->pelaksanaan->tanggal_kegiatan)->isoFormat('dddd'),
                'jam_mulai' => Carbon::parse($this->pelaksanaan->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($this->pelaksanaan->jam_selesai)->format('H:i'),
                'lokasi' => $this->pelaksanaan->lokasi ?: $this->komsel->lokasi ?: 'Lokasi akan diinformasikan',
                'pemimpin' => $this->komsel->pemimpin ? $this->komsel->pemimpin->nama : 'Akan diinformasikan',
                'pemimpin_contact' => $this->komsel->pemimpin ? $this->komsel->pemimpin->no_telepon : null,
                'reminder_type' => $this->reminderType,
                'attendance_url' => route('kehadiran.scan', $this->pelaksanaan->id_pelaksanaan),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}