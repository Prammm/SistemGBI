@extends('layouts.app')

@section('title', 'Laporan Kehadiran')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Kehadiran</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kehadiran.index') }}">Presensi Kehadiran</a></li>
        <li class="breadcrumb-item active">Laporan Kehadiran</li>
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
    
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt me-1"></i>
                    Parameter Laporan Kehadiran
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
                    
                    <form action="{{ route('kehadiran.laporan.generate') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="id_kegiatan" class="form-label">Kegiatan</label>
                            <select class="form-select @error('id_kegiatan') is-invalid @enderror" id="id_kegiatan" name="id_kegiatan" required>
                                <option value="">Pilih Kegiatan</option>
                                @foreach($kegiatan as $k)
                                    <option value="{{ $k->id_kegiatan }}" {{ old('id_kegiatan') == $k->id_kegiatan ? 'selected' : '' }}>
                                        {{ $k->nama_kegiatan }} ({{ ucfirst($k->tipe_kegiatan) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', \Carbon\Carbon::now()->subDays(30)->format('Y-m-d')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Generate Laporan
                            </button>
                            <a href="{{ route('kehadiran.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Informasi
                </div>
                <div class="card-body">
                    <p>Laporan kehadiran digunakan untuk melihat statistik kehadiran anggota jemaat pada kegiatan tertentu selama periode waktu yang ditentukan.</p>
                    <p>Manfaat laporan kehadiran:</p>
                    <ul>
                        <li>Memantau tingkat kehadiran jemaat pada kegiatan rutin</li>
                        <li>Mengidentifikasi anggota yang tidak aktif</li>
                        <li>Evaluasi efektivitas kegiatan</li>
                        <li>Perencanaan dan pengambilan keputusan</li>
                    </ul>
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