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
        </div>
    </div>

    <!-- Anggota Keluarga -->
    @if($anggota->id_keluarga && count($anggotaKeluarga) > 0)
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-home me-1"></i>
                    Anggota Keluarga {{ $anggota->keluarga->nama_keluarga }}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Hubungan</th>
                                    <th>No. Telepon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($anggotaKeluarga as $keluarga)
                                    <tr>
                                        <td>
                                            <a href="{{ route('anggota.show', $keluarga->id_anggota) }}">
                                                {{ $keluarga->nama }}
                                            </a>
                                        </td>
                                        <td>{{ $keluarga->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($keluarga->tanggal_lahir)->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $anggota->getHubunganDengan($keluarga->id_anggota) }}
                                            </span>
                                        </td>
                                        <td>{{ $keluarga->no_telepon ?: '-' }}</td>
                                        <td>
                                            <a href="{{ route('anggota.show', $keluarga->id_anggota) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Daftar Anak (jika ada) -->
    @if(count($anggota->anak) > 0)
    <div class="row">
        <div class="col-xl-12">
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
                                    <th>Keluarga</th>
                                    <th>Aksi</th>
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
                                        <td>
                                            @if($anak->keluarga)
                                                <a href="{{ route('keluarga.show', $anak->id_keluarga) }}">
                                                    {{ $anak->keluarga->nama_keluarga }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('anggota.show', $anak->id_anggota) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Hubungan Keluarga Detail (jika ada) -->
    @if(count($hubunganKeluarga) > 0)
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-sitemap me-1"></i>
                    Detail Hubungan Keluarga
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Anggota</th>
                                    <th>Hubungan</th>
                                    <th>Anggota Tujuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hubunganKeluarga as $hubungan)
                                    <tr>
                                        <td>
                                            <a href="{{ route('anggota.show', $hubungan->id_anggota) }}">
                                                {{ $hubungan->anggota->nama }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $hubungan->hubungan }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('anggota.show', $hubungan->id_anggota_tujuan) }}">
                                                {{ $hubungan->anggotaTujuan->nama }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection