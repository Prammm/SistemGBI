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
        <div class="col-xl-12">
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
    
    </div>
</div>
@endsection