<!-- resources/views/kehadiran/scan.blade.php -->
@extends('layouts.app')

@section('title', 'QR Code Presensi')

@section('styles')
<style>
    .qr-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
    }
    
    .qr-code {
        padding: 20px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .qr-info {
        margin-top: 20px;
        text-align: center;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">QR Code Presensi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kehadiran.index') }}">Presensi Kehadiran</a></li>
        <li class="breadcrumb-item active">QR Code</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-qrcode me-1"></i>
            QR Code untuk Presensi Kehadiran
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>Kegiatan:</strong> {{ $pelaksanaan->kegiatan->nama_kegiatan }} <br>
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('d/m/Y') }} <br>
                <strong>Waktu:</strong> {{ \Carbon\Carbon::parse($pelaksanaan->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pelaksanaan->jam_selesai)->format('H:i') }} <br>
                <strong>Lokasi:</strong> {{ $pelaksanaan->lokasi ?: '-' }}
            </div>
            
            <div class="qr-wrapper">
                <div class="qr-code">
                    <div id="qrcode"></div>
                </div>
                <div class="qr-info">
                    <p class="text-muted">Scan QR code ini untuk melakukan Presensi kehadiran</p>
                    <p class="text-muted">Gunakan kamera ponsel atau aplikasi QR code scanner</p>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $pelaksanaan->id_pelaksanaan]) }}" class="btn btn-primary">
                    <i class="fas fa-clipboard-list"></i> Input Presensi Manual
                </a>
                <a href="{{ route('kehadiran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ $qrUrl }}",
            width: 300,
            height: 300,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>
@endsection