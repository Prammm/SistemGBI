@extends('layouts.app')

@section('title', 'Edit Keluarga')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Keluarga</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('keluarga.index') }}">Keluarga</a></li>
        <li class="breadcrumb-item active">Edit Keluarga</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-home me-1"></i>
                    Form Edit Keluarga
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('keluarga.update', $keluarga->id_keluarga) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="nama_keluarga" class="form-label">Nama Keluarga</label>
                            <input type="text" class="form-control @error('nama_keluarga') is-invalid @enderror" id="nama_keluarga" name="nama_keluarga" value="{{ old('nama_keluarga', $keluarga->nama_keluarga) }}" required>
                            @error('nama_keluarga')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('keluarga.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection