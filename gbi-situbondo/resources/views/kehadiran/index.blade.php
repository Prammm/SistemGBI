@extends('layouts.app')

@section('title', 'Presensi Kehadiran')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Presensi Kehadiran</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Presensi Kehadiran</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Jadwal Kegiatan Terdekat
                </div>
                <div class="card-body">
                    @if(count($pelaksanaan) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kegiatan</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pelaksanaan as $p)
                                        <tr>
                                            <td>{{ $p->kegiatan->nama_kegiatan }}</td>
                                            <td>{{ \Carbon\Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($p->jam_selesai)->format('H:i') }}
                                            </td>
                                            <td>
                                                <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $p->id_pelaksanaan]) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-clipboard-check"></i> Presensi
                                                </a>
                                                <a href="{{ route('kehadiran.scan', $p->id_pelaksanaan) }}" class="btn btn-success btn-sm">
                                                    <i class="fas fa-qrcode"></i> QR Code
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Tidak ada jadwal kegiatan terdekat.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Menu Presensi
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-4">
                            <a href="{{ route('kehadiran.scan') }}" class="text-decoration-none">
                                <div class="card h-100 bg-light">
                                    <div class="card-body">
                                        <i class="fas fa-qrcode fa-3x mb-3 text-primary"></i>
                                        <h5 class="card-title">Scan QR Code</h5>
                                        <p class="card-text">Tampilkan QR code untuk Presensi kehadiran jemaat</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="{{ route('kehadiran.create') }}" class="text-decoration-none">
                                <div class="card h-100 bg-light">
                                    <div class="card-body">
                                        <i class="fas fa-clipboard-list fa-3x mb-3 text-success"></i>
                                        <h5 class="card-title">Presensi Manual</h5>
                                        <p class="card-text">Catat kehadiran jemaat secara manual dengan daftar nama</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="{{ route('kehadiran.laporan') }}" class="text-decoration-none">
                                <div class="card h-100 bg-light">
                                    <div class="card-body">
                                        <i class="fas fa-chart-pie fa-3x mb-3 text-info"></i>
                                        <h5 class="card-title">Laporan Kehadiran</h5>
                                        <p class="card-text">Lihat laporan kehadiran jemaat berdasarkan periode</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="{{ route('pelaksanaan.index') }}" class="text-decoration-none">
                                <div class="card h-100 bg-light">
                                    <div class="card-body">
                                        <i class="fas fa-calendar-day fa-3x mb-3 text-warning"></i>
                                        <h5 class="card-title">Jadwal Kegiatan</h5>
                                        <p class="card-text">Lihat semua jadwal kegiatan gereja</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection