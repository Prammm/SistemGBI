@extends('layouts.app')

@section('title', 'Detail Kelompok Sel')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Kelompok Sel</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('komsel.index') }}">Kelompok Sel</a></li>
        <li class="breadcrumb-item active">Detail Kelompok Sel</li>
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
                    Informasi Kelompok Sel
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama Komsel</div>
                        <div class="col-md-8">{{ $komsel->nama_komsel }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Pemimpin</div>
                        <div class="col-md-8">
                            @if($komsel->pemimpin)
                                <a href="{{ route('anggota.show', $komsel->pemimpin->id_anggota) }}">
                                    {{ $komsel->pemimpin->nama }}
                                </a>
                            @else
                                <span class="text-muted">Belum ada pemimpin</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Jadwal</div>
                        <div class="col-md-8">{{ $komsel->hari }}, {{ \Carbon\Carbon::parse($komsel->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($komsel->jam_selesai)->format('H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Lokasi</div>
                        <div class="col-md-8">{{ $komsel->lokasi ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Jumlah Anggota</div>
                        <div class="col-md-8">{{ count($komsel->anggota) }} orang</div>
                    </div>
                    @if($komsel->deskripsi)
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Deskripsi</div>
                        <div class="col-md-8">{{ $komsel->deskripsi }}</div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    @if(auth()->user()->id_role <= 3)
                        <a href="{{ route('komsel.edit', $komsel->id_komsel) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('komsel.jadwalkan', $komsel->id_komsel) }}" class="btn btn-success">
                            <i class="fas fa-calendar-plus"></i> Jadwalkan Pertemuan
                        </a>
                    @endif
                    <a href="{{ route('komsel.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            @if(auth()->user()->id_role <= 3)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calendar-plus me-1"></i>
                        Tambah Pertemuan Komsel
                    </div>
                    <div class="card-body">
                        <form action="{{ route('komsel.tambah-pertemuan', $komsel->id_komsel) }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="tanggal_kegiatan" class="form-label">Tanggal Pertemuan</label>
                                <input type="date" class="form-control @error('tanggal_kegiatan') is-invalid @enderror" id="tanggal_kegiatan" name="tanggal_kegiatan" value="{{ old('tanggal_kegiatan') }}" required>
                                @error('tanggal_kegiatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control @error('jam_mulai') is-invalid @enderror" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', substr($komsel->jam_mulai, 0, 5)) }}" required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                    <input type="time" class="form-control @error('jam_selesai') is-invalid @enderror" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', substr($komsel->jam_selesai, 0, 5)) }}" required>
                                    @error('jam_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lokasi" class="form-label">Lokasi</label>
                                <input type="text" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi" name="lokasi" value="{{ old('lokasi', $komsel->lokasi) }}">
                                @error('lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Pertemuan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Anggota Kelompok Sel
                </div>
                <div class="card-body">
                    @if(count($komsel->anggota) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jenis Kelamin</th>
                                        <th>No. Telepon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($komsel->anggota as $a)
                                        <tr>
                                            <td>
                                                <a href="{{ route('anggota.show', $a->id_anggota) }}">
                                                    {{ $a->nama }}
                                                </a>
                                                @if($komsel->pemimpin && $a->id_anggota == $komsel->pemimpin->id_anggota)
                                                    <span class="badge bg-success">Pemimpin</span>
                                                @endif
                                            </td>
                                            <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                            <td>{{ $a->no_telepon ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Belum ada anggota dalam kelompok sel ini.</p>
                    @endif
                </div>
                @if(auth()->user()->id_role <= 3)
                    <div class="card-footer">
                        <a href="{{ route('komsel.edit', $komsel->id_komsel) }}" class="btn btn-primary">
                            <i class="fas fa-users-cog"></i> Kelola Anggota
                        </a>
                    </div>
                @endif
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Pertemuan Komsel
                </div>
                <div class="card-body">
                    @if(count($pertemuan) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Lokasi</th>
                                        <th>Kehadiran</th>
                                        @if(auth()->user()->id_role <= 3)
                                            <th>Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pertemuan as $p)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($p->jam_selesai)->format('H:i') }}
                                            </td>
                                            <td>{{ $p->lokasi ?: '-' }}</td>
                                            <td>{{ $p->kehadiran->count() }} orang</td>
                                            @if(auth()->user()->id_role <= 3)
                                                <td>
                                                    <a href="{{ route('komsel.absensi', $p->id_pelaksanaan) }}" class="btn btn-success btn-sm">
                                                        <i class="fas fa-clipboard-check"></i> Presensi
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Belum ada jadwal pertemuan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection