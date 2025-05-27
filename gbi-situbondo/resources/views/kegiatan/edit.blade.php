@extends('layouts.app')

@section('title', 'Edit Kegiatan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kegiatan.index') }}">Daftar Kegiatan</a></li>
        <li class="breadcrumb-item active">Edit Kegiatan</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Form Edit Kegiatan
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
                    
                    <form action="{{ route('kegiatan.update', $kegiatan->id_kegiatan) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required>
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipe_kegiatan" class="form-label">Tipe Kegiatan</label>
                            <select class="form-select @error('tipe_kegiatan') is-invalid @enderror" id="tipe_kegiatan" name="tipe_kegiatan" required>
                                <option value="">Pilih Tipe Kegiatan</option>
                                <option value="ibadah" {{ old('tipe_kegiatan', $kegiatan->tipe_kegiatan) == 'ibadah' ? 'selected' : '' }}>Ibadah</option>
                                <option value="komsel" {{ old('tipe_kegiatan', $kegiatan->tipe_kegiatan) == 'komsel' ? 'selected' : '' }}>Kelompok Sel</option>
                                <option value="pelayanan" {{ old('tipe_kegiatan', $kegiatan->tipe_kegiatan) == 'pelayanan' ? 'selected' : '' }}>Pelayanan</option>
                                <option value="pelatihan" {{ old('tipe_kegiatan', $kegiatan->tipe_kegiatan) == 'pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                <option value="lainnya" {{ old('tipe_kegiatan', $kegiatan->tipe_kegiatan) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('tipe_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                       
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                       
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection