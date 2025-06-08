@extends('emails.layouts.church')

@section('title', 'Notifikasi Ketidakhadiran Berturut-turut')
@section('header-title', 'Perhatian Khusus Diperlukan')
@section('header-subtitle', 'Notifikasi Ketidakhadiran Anggota')

@section('content')
<div class="greeting">Shalom, {{ $leader_nama }}</div>

<p>Kami ingin memberitahukan bahwa salah satu anggota {{ $tipe_kegiatan === 'komsel' ? 'komsel' : 'jemaat' }} memerlukan perhatian khusus dari Anda.</p>

<div class="event-details">
    <h3>Detail Anggota</h3>
    <div class="detail-item">
        <span class="detail-label">Nama:</span>
        <span class="detail-value">{{ $anggota_nama }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Keluarga:</span>
        <span class="detail-value">{{ $keluarga ?? 'Tidak terdaftar' }}</span>
    </div>
    @if($anggota_kontak)
    <div class="detail-item">
        <span class="detail-label">Kontak:</span>
        <span class="detail-value">{{ $anggota_kontak }}</span>
    </div>
    @endif
    @if($anggota_alamat)
    <div class="detail-item">
        <span class="detail-label">Alamat:</span>
        <span class="detail-value">{{ $anggota_alamat }}</span>
    </div>
    @endif
</div>

<div class="alert alert-warning">
    <strong>{{ $anggota_nama }}</strong> telah tidak hadir sebanyak <strong>{{ $absence_count }} kali berturut-turut</strong> pada {{ $tipe_kegiatan === 'komsel' ? 'pertemuan komsel' : 'ibadah' }}.
</div>

@if(!empty($recent_absences))
<div class="event-details">
    <h3>Riwayat Ketidakhadiran Terakhir</h3>
    @foreach($recent_absences as $absence)
    <div class="detail-item">
        <span class="detail-label">{{ $absence['tanggal'] }}:</span>
        <span class="detail-value">{{ $absence['kegiatan'] }} @if($absence['lokasi']) - {{ $absence['lokasi'] }} @endif</span>
    </div>
    @endforeach
</div>
@endif

<p><strong>Tindakan yang Disarankan:</strong></p>
<ul>
    <li>Menghubungi {{ $anggota_nama }} untuk menanyakan kabar dan keadaannya</li>
    <li>Memberikan dukungan pastoral yang diperlukan</li>
    <li>Mengunjungi jika memungkinkan</li>
    <li>Melaporkan hasil kunjungan kepada pengurus gereja</li>
</ul>

<p>Mari kita bersama-sama merawat domba-domba yang Tuhan percayakan kepada kita.</p>

<div class="contact-info">
    <p><em>"Berbahagialah orang yang memperhatikan orang yang lemah, TUHAN akan melepaskan dia pada waktu celaka." - Mazmur 41:1</em></p>
</div>
@endsection