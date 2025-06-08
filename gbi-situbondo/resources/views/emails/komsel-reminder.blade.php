@extends('emails.layouts.church')

@section('title', 'Pengingat Pertemuan Komsel')
@section('header-title', 'Pengingat Pertemuan Komsel')
@section('header-subtitle', 'Bertumbuh Bersama dalam Firman')

@section('content')
<div class="greeting">Shalom, {{ $nama_anggota }}</div>

@if($reminder_type === 'week_before')
    <p>Kami ingin mengingatkan Anda tentang pertemuan komsel minggu depan.</p>
@elseif($reminder_type === 'day_of')
    <p>Hari ini adalah hari pertemuan komsel kita! Mari berkumpul untuk saling menguatkan dalam iman.</p>
@else
    <p>Mari bersiap untuk pertemuan komsel kita besok!</p>
@endif

<div class="event-details">
    <h3>Detail Pertemuan</h3>
    <div class="detail-item">
        <span class="detail-label">Komsel:</span>
        <span class="detail-value"><strong>{{ $nama_komsel }}</strong></span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Hari/Tanggal:</span>
        <span class="detail-value">{{ $hari }}, {{ $tanggal }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Waktu:</span>
        <span class="detail-value">{{ $jam_mulai }} - {{ $jam_selesai }} WIB</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Lokasi:</span>
        <span class="detail-value">{{ $lokasi }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Pemimpin:</span>
        <span class="detail-value">{{ $pemimpin }}@if($pemimpin_contact) ({{ $pemimpin_contact }})@endif</span>
    </div>
</div>

<div class="alert alert-info">
    <strong>Persiapan Pertemuan:</strong>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li>Berdoa untuk pertemuan yang berkat</li>
        <li>Siapkan hati untuk menerima Firman Tuhan</li>
        <li>Bawa Alkitab dan buku catatan</li>
        <li>Datang tepat waktu</li>
    </ul>
</div>

@if($reminder_type === 'day_of')
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $attendance_url }}" class="button button-primary">📱 Presensi Online</a>
</div>
@endif

<p>Kehadiran Anda sangat berharga untuk membangun persekutuan yang semakin erat. Mari bertumbuh bersama dalam iman!</p>

<div class="contact-info">
    <p><em>"Dan baiklah kita saling memperhatikan supaya kita saling mendorong dalam kasih dan dalam pekerjaan baik. Janganlah kita menjauhkan diri dari pertemuan-pertemuan ibadah kita, seperti dibiasakan oleh beberapa orang, tetapi marilah kita saling menasihati." - Ibrani 10:24-25</em></p>
</div>
@endsection