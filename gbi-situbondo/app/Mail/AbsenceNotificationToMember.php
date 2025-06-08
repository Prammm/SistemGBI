<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Anggota;

class AbsenceNotificationToMember extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $anggota;
    public $absenceCount;
    public $tipeKegiatan;
    public $recentAbsences;

    public function __construct(Anggota $anggota, $absenceCount, $tipeKegiatan, $recentAbsences = [])
    {
        $this->anggota = $anggota;
        $this->absenceCount = $absenceCount;
        $this->tipeKegiatan = $tipeKegiatan;
        $this->recentAbsences = $recentAbsences;
    }

    public function envelope(): Envelope
    {
        $subject = $this->tipeKegiatan === 'komsel' 
            ? 'Kami Merindukan Kehadiran Anda di Komsel'
            : 'Kami Merindukan Kehadiran Anda di Ibadah';

        return new Envelope(
            subject: $subject . ' - GBI Situbondo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.absence-notification-member',
            with: [
                'anggota_nama' => $this->anggota->nama,
                'absence_count' => $this->absenceCount,
                'tipe_kegiatan' => $this->tipeKegiatan,
                'recent_absences' => $this->recentAbsences,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}