@extends('layouts.app')

@section('title', 'Kelompok Sel')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Kelompok Sel</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Kelompok Sel</li>
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
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Daftar Kelompok Sel
            <a href="{{ route('komsel.create') }}" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-plus"></i> Tambah Komsel
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Komsel</th>
                            <th>Pemimpin</th>
                            <th>Jadwal</th>
                            <th>Jumlah Anggota</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($komsel as $k)
                            <tr>
                                <td>{{ $k->nama_komsel }}</td>
                                <td>
                                    @if($k->pemimpin)
                                        <a href="{{ route('anggota.show', $k->pemimpin->id_anggota) }}">
                                            {{ $k->pemimpin->nama }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $k->hari }}, {{ \Carbon\Carbon::parse($k->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($k->jam_selesai)->format('H:i') }}</td>
                                <td>{{ $k->anggota_count }}</td>
                                <td>{{ $k->lokasi ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('komsel.show', $k->id_komsel) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('komsel.edit', $k->id_komsel) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('komsel.destroy', $k->id_komsel) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus komsel ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection