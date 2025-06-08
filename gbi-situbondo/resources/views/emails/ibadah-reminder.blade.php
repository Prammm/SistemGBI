@extends('emails.layouts.church')

@section('title', 'Pengingat Ibadah')
@section('header-title', 'Pengingat Ibadah')
@section('header-subtitle', 'Mari Memuji dan Menyembah Bersama')

@section('content')
<div class="greeting">Shalom, {{ $nama_anggota }}</div>

@if($reminder_type === 'week_before')
    <p>Kami mengundang Anda untuk bergabung dalam ibadah minggu depan.</p>
@elseif($reminder_type === 'day_of')
    <p>Hari ini adalah hari yang indah untuk bersekutu dan menyembah Tuhan bersama!</p>
@else
    <p>Mari bersiap untuk menyembah Tuhan bersama keluarga besar GBI Situbondo besok!</p>
@endif

<div class="event-details">
    <h3>Detail Ibadah</h3>
    <div class="detail-item">
        <span class="detail-label">Ibadah:</span>
        <span class="detail-value"><strong>{{ $nama_ibadah }}</strong></span>
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

<div class="alert alert-info">
    <strong>Persiapan Ibadah:</strong>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li>Datang 15 menit sebelum ibadah dimulai</li>
        <li>Berdoa untuk hati yang siap menerima Firman</li>
        <li>Bawa Alkitab dan buku nyanyian</li>
        <li>Siapkan persembahan dengan sukacita</li>
    </ul>
</div>

@if($reminder_type === 'day_of')
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $attendance_url }}" class="button button-primary">📱 Presensi Online</a>
</div>
@endif

<p>Kehadiran Anda akan memperkaya ibadah kita bersama. Mari datang dengan hati yang gembira untuk memuji dan menyembah Tuhan!</p>

<div class="contact-info">
    <p><strong>Kontak gereja:</strong><br>
    Telepon: {{ $contact_info['phone'] ?? env('CHURCH_PHONE', '+62 123 456 789') }}<br>
    Email: {{ $contact_info['email'] ?? env('CHURCH_EMAIL', 'info@gbisitubondo.org') }}</p>
    
    <p style="margin-top: 15px;"><em>"Marilah kita pergi ke rumah TUHAN!" - Mazmur 122:1</em></p>
</div>
@endsection