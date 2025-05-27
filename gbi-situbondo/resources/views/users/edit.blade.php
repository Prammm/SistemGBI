@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Pengguna</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen Pengguna</a></li>
        <li class="breadcrumb-item active">Edit Pengguna</li>
    </ol>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-edit me-1"></i>
                    Form Edit Pengguna
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_role" class="form-label">Role</label>
                            <select class="form-select @error('id_role') is-invalid @enderror" id="id_role" name="id_role" required>
                                <option value="">Pilih Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id_role }}" {{ (old('id_role', $user->id_role) == $role->id_role) ? 'selected' : '' }}>
                                        {{ $role->nama_role }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_anggota" class="form-label">Anggota Jemaat</label>
                            <select class="form-select @error('id_anggota') is-invalid @enderror" id="id_anggota" name="id_anggota">
                                <option value="">Tidak Terkait Anggota</option>
                                @foreach($anggota as $a)
                                    <option value="{{ $a->id_anggota }}" {{ (old('id_anggota', $user->id_anggota) == $a->id_anggota) ? 'selected' : '' }}>
                                        {{ $a->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_anggota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection