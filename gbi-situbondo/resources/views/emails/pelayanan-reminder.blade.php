@extends('emails.layouts.church')

@section('title', 'Pengingat Jadwal Pelayanan')
@section('header-title', 'Pengingat Jadwal Pelayanan')
@section('header-subtitle', 'Melayani dengan Sukacita')

@section('content')
<div class="greeting">Shalom, {{ $nama }}</div>

@if($reminder_type === 'week_before')
    <p>Ini adalah pengingat untuk jadwal pelayanan Anda minggu depan.</p>
@elseif($reminder_type === 'day_of')
    <p>Hari ini adalah jadwal pelayanan Anda! Bersiaplah untuk melayani dengan sukacita.</p>
@else
    <p>Ini adalah pengingat untuk jadwal pelayanan Anda besok.</p>
@endif

<div class="event-details">
    <h3>Detail Pelayanan</h3>
    <div class="detail-item">
        <span class="detail-label">Kegiatan:</span>
        <span class="detail-value">{{ $kegiatan }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Posisi:</span>
        <span class="detail-value"><strong>{{ $posisi }}</strong></span>
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
</div>

@if($reminder_type !== 'day_of')
<div style="text-align: center; margin: 30px 0;">
    <p><strong>Silahkan konfirmasi kehadiran Anda:</strong></p>
    <a href="{{ $confirmation_url }}" class="button button-primary">✓ Saya Akan Hadir</a>
    <a href="{{ $reject_url }}" class="button button-secondary">✗ Tidak Bisa Hadir</a>
</div>
@endif

<div class="alert alert-info">
    <strong>Persiapan Pelayanan:</strong>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li>Datang 30 menit sebelum acara dimulai</li>
        <li>Berpakaian rapi dan sopan</li>
        <li>Berdoa sebelum melayani</li>
        <li>Koordinasi dengan tim pelayanan lainnya</li>
    </ul>
</div>

<p>Terima kasih atas kesediaan Anda untuk melayani. Tuhan akan memberkati pelayanan Anda!</p>

<div class="contact-info">
    <p><strong>Kontak darurat:</strong><br>
    Telepon: {{ $contact_info['phone'] ?? env('CHURCH_PHONE', '+62 123 456 789') }}<br>
    Email: {{ $contact_info['email'] ?? env('CHURCH_EMAIL', 'info@gbisitubondo.org') }}</p>
    
    <p style="margin-top: 15px;"><em>"Apa pun juga yang kamu perbuat, perbuatlah dengan segenap hatimu seperti untuk Tuhan dan bukan untuk manusia." - Kolose 3:23</em></p>
</div>
@endsection