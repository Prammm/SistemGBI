@extends('layouts.app')

@section('title', 'Detail Pelaksanaan Kegiatan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Pelaksanaan Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelaksanaan.index') }}">Jadwal Kegiatan</a></li>
        <li class="breadcrumb-item active">Detail Pelaksanaan</li>
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
                    <i class="fas fa-info-circle me-1"></i>
                    Informasi Pelaksanaan
                    @if($pelaksanaan->is_recurring)
                        <span class="badge bg-primary ms-2">
                            <i class="fas fa-repeat"></i> Jadwal Berulang
                        </span>
                    @elseif($pelaksanaan->parent_id)
                        <span class="badge bg-info ms-2">
                            <i class="fas fa-link"></i> Bagian dari Seri
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Kegiatan</div>
                        <div class="col-md-8">{{ $pelaksanaan->kegiatan->nama_kegiatan }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tipe Kegiatan</div>
                        <div class="col-md-8">
                            @switch($pelaksanaan->kegiatan->tipe_kegiatan)
                                @case('ibadah')
                                    <span class="badge bg-primary">Ibadah</span>
                                    @break
                                @case('komsel')
                                    <span class="badge bg-success">Kelompok Sel</span>
                                    @break
                                @case('pelayanan')
                                    <span class="badge bg-info">Pelayanan</span>
                                    @break
                                @case('pelatihan')
                                    <span class="badge bg-warning">Pelatihan</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">Lainnya</span>
                            @endswitch
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal</div>
                        <div class="col-md-8">{{ \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('d/m/Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Waktu</div>
                        <div class="col-md-8">
                            {{ \Carbon\Carbon::parse($pelaksanaan->jam_mulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($pelaksanaan->jam_selesai)->format('H:i') }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Lokasi</div>
                        <div class="col-md-8">{{ $pelaksanaan->lokasi ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Jumlah Kehadiran</div>
                        <div class="col-md-8">{{ count($pelaksanaan->kehadiran) }} orang</div>
                    </div>
                    
                    @if($pelaksanaan->is_recurring)
                        <hr>
                        <h6 class="text-primary">
                            <i class="fas fa-repeat"></i> Informasi Jadwal Berulang
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Tipe Pengulangan</div>
                            <div class="col-md-8">
                                @if($pelaksanaan->recurring_type === 'weekly')
                                    <span class="badge bg-success">Mingguan</span>
                                @elseif($pelaksanaan->recurring_type === 'monthly')
                                    <span class="badge bg-info">Bulanan</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Berakhir Pada</div>
                            <div class="col-md-8">{{ \Carbon\Carbon::parse($pelaksanaan->recurring_end_date)->format('d/m/Y') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Total Jadwal</div>
                            <div class="col-md-8">{{ $pelaksanaan->children()->count() + 1 }} jadwal</div>
                        </div>
                    @elseif($pelaksanaan->parent_id)
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Catatan:</strong> Jadwal ini merupakan bagian dari seri jadwal berulang.
                            <br>
                            <a href="{{ route('pelaksanaan.show', $pelaksanaan->parent_id) }}" class="btn btn-sm btn-outline-info mt-2">
                                <i class="fas fa-eye"></i> Lihat Jadwal Induk
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('pelaksanaan.edit', $pelaksanaan->id_pelaksanaan) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $pelaksanaan->id_pelaksanaan]) }}" class="btn btn-success">
                        <i class="fas fa-clipboard-check"></i> Presensi
                    </a>
                    <a href="{{ route('pelaksanaan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    
                    @if($pelaksanaan->is_recurring && $pelaksanaan->children()->count() > 0)
                        <a href="{{ route('pelaksanaan.series', $pelaksanaan->id_pelaksanaan) }}" class="btn btn-info">
                            <i class="fas fa-list"></i> Lihat Semua Jadwal
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-qrcode me-1"></i>
                    QR Code Presensi
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div id="qrcode"></div>
                    </div>
                    <p class="text-muted">Scan QR code ini untuk melakukan presensi kehadiran</p>
                    <a href="{{ route('kehadiran.scan', $pelaksanaan->id_pelaksanaan) }}" class="btn btn-primary">
                        <i class="fas fa-expand"></i> Tampilkan QR Code Layar Penuh
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Kehadiran Anggota
                </div>
                <div class="card-body">
                    @if(count($pelaksanaan->kehadiran) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Waktu Presensi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pelaksanaan->kehadiran->sortBy('anggota.nama') as $kehadiran)
                                        <tr>
                                            <td>
                                                <a href="{{ route('anggota.show', $kehadiran->anggota->id_anggota) }}">
                                                    {{ $kehadiran->anggota->nama }}
                                                </a>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($kehadiran->waktu_absensi)->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @switch($kehadiran->status)
                                                    @case('hadir')
                                                        <span class="badge bg-success">Hadir</span>
                                                        @break
                                                    @case('izin')
                                                        <span class="badge bg-warning">Izin</span>
                                                        @break
                                                    @case('sakit')
                                                        <span class="badge bg-info">Sakit</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-danger">Alfa</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Belum ada data kehadiran.</p>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $pelaksanaan->id_pelaksanaan]) }}" class="btn btn-primary">
                        <i class="fas fa-clipboard-check"></i> Kelola Kehadiran
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ $qrUrl }}",
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>

@endsection