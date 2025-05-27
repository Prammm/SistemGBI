@extends('layouts.app')

@section('title', 'Daftar Kegiatan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Daftar Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Daftar Kegiatan</li>
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
            <i class="fas fa-calendar me-1"></i>
            Kegiatan Gereja
            <div class="float-end">
                <a href="{{ route('kegiatan.calendar') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-calendar-alt"></i> Tampilan Kalender
                </a>
                <a href="{{ route('kegiatan.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Kegiatan
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Kegiatan</th>
                            <th>Tipe</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kegiatan as $k)
                            <tr>
                                <td>{{ $k->nama_kegiatan }}</td>
                                <td>{{ $k->tipe_kegiatan }}</td>
                                <td>{{ Str::limit($k->deskripsi, 100) }}</td>
                                <td>
                                    <a href="{{ route('kegiatan.show', $k->id_kegiatan) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('kegiatan.edit', $k->id_kegiatan) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('kegiatan.destroy', $k->id_kegiatan) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')">
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