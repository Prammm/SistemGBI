@extends('layouts.app')

@section('title', 'Jadwal Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Jadwal Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Jadwal Pelayanan</li>
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
    
    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Menu Pelayanan
                    
                    @if(Auth::user()->id_role <= 3)
                        <div class="float-end">
                            <a href="{{ route('pelayanan.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Jadwal
                            </a>
                            
                            @if(Auth::user()->id_role <= 2)
                                <a href="{{ route('pelayanan.generator') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-magic"></i> Generate Jadwal
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-check me-1"></i>
            Jadwal Pelayanan Mendatang
        </div>
        <div class="card-body">
            @if($jadwalPelayanan->isNotEmpty())
                @foreach($jadwalPelayanan as $tanggal => $jadwalList)
                    <h5 class="border-bottom pb-2">
                        {{ \Carbon\Carbon::parse($tanggal)->format('l, d F Y') }}
                    </h5>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Kegiatan</th>
                                    <th>Posisi</th>
                                    <th>Petugas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jadwalList as $jadwal)
                                    <tr>
                                        <td>{{ $jadwal->kegiatan->nama_kegiatan }}</td>
                                        <td>{{ $jadwal->posisi }}</td>
                                        <td>
                                            <a href="{{ route('anggota.show', $jadwal->anggota->id_anggota) }}">
                                                {{ $jadwal->anggota->nama }}
                                            </a>
                                        </td>
                                        <td>
                                            @switch($jadwal->status_konfirmasi)
                                                @case('belum')
                                                    <span class="badge bg-warning">Belum Konfirmasi</span>
                                                    @break
                                                @case('terima')
                                                    <span class="badge bg-success">Diterima</span>
                                                    @break
                                                @case('tolak')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Unknown</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if(Auth::user()->id_role <= 3 || (Auth::user()->id_anggota && Auth::user()->id_anggota == $jadwal->id_anggota))
                                                @if($jadwal->status_konfirmasi == 'belum')
                                                    <a href="{{ route('pelayanan.konfirmasi', [$jadwal->id_pelayanan, 'terima']) }}" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Terima
                                                    </a>
                                                    <a href="{{ route('pelayanan.konfirmasi', [$jadwal->id_pelayanan, 'tolak']) }}" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Tolak
                                                    </a>
                                                @endif
                                                
                                                @if(Auth::user()->id_role <= 3)
                                                    <form action="{{ route('pelayanan.destroy', $jadwal->id_pelayanan) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @else
                <p class="text-center">Tidak ada jadwal pelayanan mendatang.</p>
            @endif
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Riwayat Pelayanan
        </div>
        <div class="card-body">
            @if($riwayatPelayanan->isNotEmpty())
                @foreach($riwayatPelayanan as $tanggal => $jadwalList)
                    <h5 class="border-bottom pb-2">
                        {{ \Carbon\Carbon::parse($tanggal)->format('l, d F Y') }}
                    </h5>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Kegiatan</th>
                                    <th>Posisi</th>
                                    <th>Petugas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jadwalList as $jadwal)
                                    <tr>
                                        <td>{{ $jadwal->kegiatan->nama_kegiatan }}</td>
                                        <td>{{ $jadwal->posisi }}</td>
                                        <td>
                                            <a href="{{ route('anggota.show', $jadwal->anggota->id_anggota) }}">
                                                {{ $jadwal->anggota->nama }}
                                            </a>
                                        </td>
                                        <td>
                                            @switch($jadwal->status_konfirmasi)
                                                @case('belum')
                                                    <span class="badge bg-warning">Belum Konfirmasi</span>
                                                    @break
                                                @case('terima')
                                                    <span class="badge bg-success">Diterima</span>
                                                    @break
                                                @case('tolak')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Unknown</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @else
                <p class="text-center">Belum ada riwayat pelayanan.</p>
            @endif
        </div>
    </div>
</div>
@endsection