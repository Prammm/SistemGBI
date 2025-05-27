@extends('layouts.app')

@section('title', 'Anggota Jemaat')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Anggota Jemaat</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Anggota Jemaat</li>
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
            Daftar Anggota
            <a href="{{ route('anggota.create') }}" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-plus"></i> Tambah Anggota
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jenis Kelamin</th>
                            <th>Tanggal Lahir</th>
                            <th>Keluarga</th>
                            <th>No. Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anggota as $a)
                            <tr>
                                <td>{{ $a->nama }}</td>
                                <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ \Carbon\Carbon::parse($a->tanggal_lahir)->format('d/m/Y') }}</td>
                                <td>{{ $a->keluarga ? $a->keluarga->nama_keluarga : '-' }}</td>
                                <td>{{ $a->no_telepon ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('anggota.show', $a->id_anggota) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('anggota.edit', $a->id_anggota) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('anggota.destroy', $a->id_anggota) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">
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