@extends('layouts.app')

@section('title', 'Edit Jadwal Kegiatan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Jadwal Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelaksanaan.index') }}">Jadwal Kegiatan</a></li>
        <li class="breadcrumb-item active">Edit Jadwal</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Form Edit Jadwal
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
                    
                    <form action="{{ route('pelaksanaan.update', $pelaksanaan->id_pelaksanaan) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="id_kegiatan" class="form-label">Kegiatan</label>
                            <select class="form-select @error('id_kegiatan') is-invalid @enderror" id="id_kegiatan" name="id_kegiatan" required>
                                <option value="">Pilih Kegiatan</option>
                                @foreach($kegiatan as $k)
                                    <option value="{{ $k->id_kegiatan }}" {{ old('id_kegiatan', $pelaksanaan->id_kegiatan) == $k->id_kegiatan ? 'selected' : '' }}>
                                        {{ $k->nama_kegiatan }} ({{ ucfirst($k->tipe_kegiatan) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                            <input type="date" class="form-control @error('tanggal_kegiatan') is-invalid @enderror" id="tanggal_kegiatan" name="tanggal_kegiatan" value="{{ old('tanggal_kegiatan', $pelaksanaan->tanggal_kegiatan) }}" required>
                            @error('tanggal_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control @error('jam_mulai') is-invalid @enderror" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', substr($pelaksanaan->jam_mulai, 0, 5)) }}" required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                    <input type="time" class="form-control @error('jam_selesai') is-invalid @enderror" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', substr($pelaksanaan->jam_selesai, 0, 5)) }}" required>
                                    @error('jam_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi" name="lokasi" value="{{ old('lokasi', $pelaksanaan->lokasi) }}">
                            @error('lokasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('pelaksanaan.index') }}" class="btn btn-secondary">Batal</a>
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
        $('#id_kegiatan').select2({
            placeholder: "Pilih Kegiatan",
            allowClear: true
        });
    });
</script>
@endsection