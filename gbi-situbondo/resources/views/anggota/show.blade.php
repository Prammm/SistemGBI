@extends('layouts.app')

@section('title', 'Detail Anggota')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Anggota</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Anggota Jemaat</a></li>
        <li class="breadcrumb-item active">Detail Anggota</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Informasi Anggota
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama Lengkap</div>
                        <div class="col-md-8">{{ $anggota->nama }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal Lahir</div>
                        <div class="col-md-8">{{ \Carbon\Carbon::parse($anggota->tanggal_lahir)->format('d/m/Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Jenis Kelamin</div>
                        <div class="col-md-8">{{ $anggota->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Alamat</div>
                        <div class="col-md-8">{{ $anggota->alamat ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">No. Telepon</div>
                        <div class="col-md-8">{{ $anggota->no_telepon ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Email</div>
                        <div class="col-md-8">{{ $anggota->email ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Keluarga</div>
                        <div class="col-md-8">
                            @if($anggota->keluarga)
                                <a href="{{ route('keluarga.show', $anggota->id_keluarga) }}">
                                    {{ $anggota->keluarga->nama_keluarga }}
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Orang Tua</div>
                        <div class="col-md-8">
                            @if($anggota->orangtua)
                                <a href="{{ route('anggota.show', $anggota->id_ortu) }}">
                                    {{ $anggota->orangtua->nama }}
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('anggota.edit', $anggota->id_anggota) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('anggota.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Kelompok Sel
                </div>
                <div class="card-body">
                    @if(count($anggota->komsel) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Komsel</th>
                                        <th>Hari</th>
                                        <th>Waktu</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($anggota->komsel as $komsel)
                                        <tr>
                                            <td>
                                                <a href="{{ route('komsel.show', $komsel->id_komsel) }}">
                                                    {{ $komsel->nama_komsel }}
                                                </a>
                                            </td>
                                            <td>{{ $komsel->hari }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($komsel->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($komsel->jam_selesai)->format('H:i') }}
                                            </td>
                                            <td>{{ $komsel->lokasi ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Tidak tergabung dalam kelompok sel manapun.</p>
                    @endif
                </div>
            </div>
            
            @if(count($anggota->anak) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-child me-1"></i>
                    Daftar Anak
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Jenis Kelamin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($anggota->anak as $anak)
                                    <tr>
                                        <td>
                                            <a href="{{ route('anggota.show', $anak->id_anggota) }}">
                                                {{ $anak->nama }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($anak->tanggal_lahir)->format('d/m/Y') }}</td>
                                        <td>{{ $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection