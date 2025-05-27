@extends('layouts.app')

@section('title', 'Keluarga')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Keluarga</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Keluarga</li>
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
            <i class="fas fa-home me-1"></i>
            Daftar Keluarga
            <a href="{{ route('keluarga.create') }}" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-plus"></i> Tambah Keluarga
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Keluarga</th>
                            <th>Jumlah Anggota</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keluarga as $k)
                            <tr>
                                <td>{{ $k->nama_keluarga }}</td>
                                <td>{{ $k->anggota_count }}</td>
                                <td>
                                    <a href="{{ route('keluarga.show', $k->id_keluarga) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('keluarga.edit', $k->id_keluarga) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('keluarga.destroy', $k->id_keluarga) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus keluarga ini?')">
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