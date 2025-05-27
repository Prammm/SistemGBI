@extends('layouts.app')

@section('title', 'Detail Kegiatan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kegiatan.index') }}">Daftar Kegiatan</a></li>
        <li class="breadcrumb-item active">Detail Kegiatan</li>
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
                    Informasi Kegiatan
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama Kegiatan</div>
                        <div class="col-md-8">{{ $kegiatan->nama_kegiatan }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tipe Kegiatan</div>
                        <div class="col-md-8">
                            @switch($kegiatan->tipe_kegiatan)
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
                        <div class="col-md-4 fw-bold">Deskripsi</div>
                        <div class="col-md-8">{{ $kegiatan->deskripsi ?: '-' }}</div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('kegiatan.edit', $kegiatan->id_kegiatan) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-calendar-alt me-1"></i>
                        Jadwal Pelaksanaan
                    </div>
                    <div>
                        <a href="{{ route('pelaksanaan.create', ['id_kegiatan' => $kegiatan->id_kegiatan]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($kegiatan->pelaksanaan) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Lokasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kegiatan->pelaksanaan->sortByDesc('tanggal_kegiatan') as $p)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($p->jam_selesai)->format('H:i') }}
                                            </td>
                                            <td>{{ $p->lokasi ?: '-' }}</td>
                                            <td>
                                                <a href="{{ route('pelaksanaan.show', $p->id_pelaksanaan) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $p->id_pelaksanaan]) }}" class="btn btn-success btn-sm">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </a>
                                                <a href="{{ route('pelaksanaan.edit', $p->id_pelaksanaan) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('pelaksanaan.destroy', $p->id_pelaksanaan) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Belum ada jadwal pelaksanaan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection