@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Pengguna</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen Pengguna</a></li>
        <li class="breadcrumb-item active">Detail Pengguna</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Informasi Pengguna
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama</div>
                        <div class="col-md-8">{{ $user->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Email</div>
                        <div class="col-md-8">{{ $user->email }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Role</div>
                        <div class="col-md-8">{{ $user->role ? $user->role->nama_role : 'Tidak ada role' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Terkait Anggota</div>
                        <div class="col-md-8">{{ $user->anggota ? $user->anggota->nama : 'Tidak terkait anggota' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal Dibuat</div>
                        <div class="col-md-8">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Terakhir Update</div>
                        <div class="col-md-8">{{ $user->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        
        @if($user->anggota)
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-circle me-1"></i>
                    Informasi Anggota
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama Lengkap</div>
                        <div class="col-md-8">{{ $user->anggota->nama }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal Lahir</div>
                        <div class="col-md-8">{{ \Carbon\Carbon::parse($user->anggota->tanggal_lahir)->format('d/m/Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Jenis Kelamin</div>
                        <div class="col-md-8">{{ $user->anggota->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Alamat</div>
                        <div class="col-md-8">{{ $user->anggota->alamat ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">No. Telepon</div>
                        <div class="col-md-8">{{ $user->anggota->no_telepon ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Keluarga</div>
                        <div class="col-md-8">{{ $user->anggota->keluarga ? $user->anggota->keluarga->nama_keluarga : '-' }}</div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('anggota.show', $user->anggota->id_anggota) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> Lihat Detail Anggota
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection