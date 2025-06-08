<?php
// app/Mail/AbsenceNotificationToLeader.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Anggota;

class AbsenceNotificationToLeader extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $anggota;
    public $absenceCount;
    public $tipeKegiatan;
    public $leader;
    public $recentAbsences;

    public function __construct(Anggota $anggota, $absenceCount, $tipeKegiatan, $leader = null, $recentAbsences = [])
    {
        $this->anggota = $anggota;
        $this->absenceCount = $absenceCount;
        $this->tipeKegiatan = $tipeKegiatan;
        $this->leader = $leader;
        $this->recentAbsences = $recentAbsences;
    }

    public function envelope(): Envelope
    {
        $subject = $this->tipeKegiatan === 'komsel' 
            ? 'Perhatian: Anggota Komsel Tidak Hadir Berturut-turut'
            : 'Perhatian: Anggota Jemaat Tidak Hadir Berturut-turut';

        return new Envelope(
            subject: $subject . ' - GBI Situbondo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.absence-notification-leader',
            with: [
                'anggota_nama' => $this->anggota->nama,
                'absence_count' => $this->absenceCount,
                'tipe_kegiatan' => $this->tipeKegiatan,
                'leader_nama' => $this->leader ? $this->leader->nama : 'Pengurus',
                'anggota_kontak' => $this->anggota->no_telepon,
                'anggota_alamat' => $this->anggota->alamat,
                'recent_absences' => $this->recentAbsences,
                'keluarga' => $this->anggota->keluarga ? $this->anggota->keluarga->nama_keluarga : null,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}