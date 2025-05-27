@extends('layouts.app')

@section('title', 'Tambah Kelompok Sel')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Kelompok Sel</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('komsel.index') }}">Kelompok Sel</a></li>
        <li class="breadcrumb-item active">Tambah Kelompok Sel</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Form Tambah Kelompok Sel
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
                    
                    <form action="{{ route('komsel.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_komsel" class="form-label">Nama Kelompok Sel</label>
                                    <input type="text" class="form-control @error('nama_komsel') is-invalid @enderror" id="nama_komsel" name="nama_komsel" value="{{ old('nama_komsel') }}" required>
                                    @error('nama_komsel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_pemimpin" class="form-label">Pemimpin</label>
                                    <select class="form-select @error('id_pemimpin') is-invalid @enderror" id="id_pemimpin" name="id_pemimpin">
                                        <option value="">Pilih Pemimpin</option>
                                        @foreach($anggota as $a)
                                            <option value="{{ $a->id_anggota }}" {{ old('id_pemimpin') == $a->id_anggota ? 'selected' : '' }}>
                                                {{ $a->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_pemimpin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari" class="form-label">Hari</label>
                                    <select class="form-select @error('hari') is-invalid @enderror" id="hari" name="hari" required>
                                        <option value="">Pilih Hari</option>
                                        <option value="Senin" {{ old('hari') == 'Senin' ? 'selected' : '' }}>Senin</option>
                                        <option value="Selasa" {{ old('hari') == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                                        <option value="Rabu" {{ old('hari') == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                                        <option value="Kamis" {{ old('hari') == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                                        <option value="Jumat" {{ old('hari') == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                                        <option value="Sabtu" {{ old('hari') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                                        <option value="Minggu" {{ old('hari') == 'Minggu' ? 'selected' : '' }}>Minggu</option>
                                    </select>
                                    @error('hari')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control @error('jam_mulai') is-invalid @enderror" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai') }}" required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                    <input type="time" class="form-control @error('jam_selesai') is-invalid @enderror" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai') }}" required>
                                    @error('jam_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi" name="lokasi" value="{{ old('lokasi') }}">
                            @error('lokasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="anggota" class="form-label">Anggota</label>
                            <select class="form-select @error('anggota') is-invalid @enderror" id="anggota" name="anggota[]" multiple>
                                @foreach($anggota as $a)
                                    <option value="{{ $a->id_anggota }}" {{ (is_array(old('anggota')) && in_array($a->id_anggota, old('anggota'))) ? 'selected' : '' }}>
                                        {{ $a->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Tahan tombol Ctrl untuk memilih beberapa anggota.</div>
                            @error('anggota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('komsel.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#id_pemimpin').select2({
            placeholder: "Pilih Pemimpin Komsel",
            allowClear: true
        });
        
        $('#anggota').select2({
            placeholder: "Pilih Anggota Komsel",
            allowClear: true
        });
    });
</script>
@endsection