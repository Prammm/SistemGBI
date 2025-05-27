@extends('layouts.app')

@section('title', 'Generate Jadwal Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Generate Jadwal Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Jadwal Pelayanan</a></li>
        <li class="breadcrumb-item active">Generate Jadwal</li>
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
                    <i class="fas fa-magic me-1"></i>
                    Form Generate Jadwal Pelayanan
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Fitur ini akan membuat jadwal pelayanan secara otomatis berdasarkan parameter yang Anda tentukan.
                        Sistem akan menjadwalkan petugas yang paling jarang melayani dalam periode tertentu untuk memastikan distribusi tugas yang merata.
                    </div>
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('pelayanan.generate') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="id_kegiatan" class="form-label">Kegiatan</label>
                            <select class="form-select @error('id_kegiatan') is-invalid @enderror" id="id_kegiatan" name="id_kegiatan" required>
                                <option value="">Pilih Kegiatan</option>
                                @foreach($kegiatan as $k)
                                    <option value="{{ $k->id_kegiatan }}" {{ old('id_kegiatan') == $k->id_kegiatan ? 'selected' : '' }}>
                                        {{ $k->nama_kegiatan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', \Carbon\Carbon::now()->addMonths(3)->format('Y-m-d')) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Hari yang Dijadwalkan</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hari0" name="hari[]" value="0" {{ (is_array(old('hari')) && in_array('0', old('hari'))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hari0">Minggu</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hari1" name="hari[]" value="1" {{ (is_array(old('hari')) && in_array('1', old('hari'))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hari1">Senin</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hari2" name="hari[]" value="2" {{ (is_array(old('hari')) && in_array('2', old('hari'))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hari2">Selasa</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hari3" name="hari[]" value="3" {{ (is_array(old('hari')) && in_array('3', old('hari'))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hari3">Rabu</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hari4" name="hari[]" value="4" {{ (is_array(old('hari')) && in_array('4', old('hari'))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hari4">Kamis</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hari5" name="hari[]" value="5" {{ (is_array(old('hari')) && in_array('5', old('hari'))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hari5">Jumat</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hari6" name="hari[]" value="6" {{ (is_array(old('hari')) && in_array('6', old('hari'))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hari6">Sabtu</label>
                                    </div>
                                </div>
                            </div>
                            @error('hari')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-magic"></i> Generate Jadwal
                            </button>
                            <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary">Batal</a>
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
                    <h5>Cara Kerja Generator Jadwal</h5>
                    <p>Generator jadwal pelayanan bekerja dengan prinsip-prinsip berikut:</p>
                    <ol>
                        <li>Sistem akan membuat jadwal untuk tanggal yang sesuai dengan hari yang dipilih dalam rentang tanggal yang ditentukan.</li>
                        <li>Untuk setiap posisi, sistem akan memilih petugas yang paling jarang melayani dalam posisi tersebut.</li>
                        <li>Sistem akan memastikan bahwa seorang petugas tidak dijadwalkan di lebih dari satu posisi pada tanggal yang sama.</li>
                        <li>Jika sudah ada jadwal pada tanggal tertentu, sistem akan melewati tanggal tersebut.</li>
                        <li>Petugas akan dipilih berdasarkan riwayat pelayanan mereka sebelumnya.</li>
                    </ol>
                    <p class="text-danger">Catatan: Generator ini berfungsi optimal jika sudah ada data riwayat pelayanan sebelumnya.</p>
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
        });
        
        // Check at least one day is selected
        $('form').submit(function(e) {
            if (!$('input[name="hari[]"]:checked').length) {
                e.preventDefault();
                alert('Pilih minimal satu hari untuk dijadwalkan.');
            }
        });
    });
</script>
@endsection