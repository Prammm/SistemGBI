@extends('layouts.app')

@section('title', 'Tambah Jadwal Kegiatan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Jadwal Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelaksanaan.index') }}">Jadwal Kegiatan</a></li>
        <li class="breadcrumb-item active">Tambah Jadwal</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-plus me-1"></i>
                    Form Tambah Jadwal
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
                    
                    <form action="{{ route('pelaksanaan.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="id_kegiatan" class="form-label">Kegiatan</label>
                            <select class="form-select @error('id_kegiatan') is-invalid @enderror" id="id_kegiatan" name="id_kegiatan" required>
                                <option value="">Pilih Kegiatan</option>
                                @foreach($kegiatan as $k)
                                    <option value="{{ $k->id_kegiatan }}" {{ old('id_kegiatan', request('id_kegiatan')) == $k->id_kegiatan ? 'selected' : '' }}>
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
                            <input type="date" class="form-control @error('tanggal_kegiatan') is-invalid @enderror" id="tanggal_kegiatan" name="tanggal_kegiatan" value="{{ old('tanggal_kegiatan') }}" required>
                            @error('tanggal_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control @error('jam_mulai') is-invalid @enderror" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai') }}" required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
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
                        
                        <!-- Opsi Jadwal Berulang -->
                        <div class="card mt-4 mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-repeat me-1"></i>
                                    Jadwal Berulang
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_recurring">
                                            Buat jadwal berulang
                                        </label>
                                    </div>
                                    <small class="text-muted">Centang untuk membuat jadwal yang berulang secara otomatis</small>
                                </div>
                                
                                <div id="recurring-options" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recurring_type" class="form-label">Tipe Pengulangan</label>
                                                <select class="form-select @error('recurring_type') is-invalid @enderror" id="recurring_type" name="recurring_type">
                                                    <option value="">Pilih Tipe</option>
                                                    <option value="weekly" {{ old('recurring_type') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                                    <option value="monthly" {{ old('recurring_type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                                </select>
                                                @error('recurring_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recurring_end_date" class="form-label">Berakhir Pada</label>
                                                <input type="date" class="form-control @error('recurring_end_date') is-invalid @enderror" id="recurring_end_date" name="recurring_end_date" value="{{ old('recurring_end_date') }}">
                                                @error('recurring_end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Tanggal terakhir jadwal berulang</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Catatan:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li><strong>Mingguan:</strong> Jadwal akan berulang setiap minggu pada hari yang sama</li>
                                            <li><strong>Bulanan:</strong> Jadwal akan berulang setiap bulan pada tanggal yang sama</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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
        
        // Toggle recurring options
        $('#is_recurring').change(function() {
            if($(this).is(':checked')) {
                $('#recurring-options').slideDown();
                $('#recurring_type').prop('required', true);
                $('#recurring_end_date').prop('required', true);
            } else {
                $('#recurring-options').slideUp();
                $('#recurring_type').prop('required', false);
                $('#recurring_end_date').prop('required', false);
            }
        });
        
        // Show recurring options if already checked (for old input)
        if($('#is_recurring').is(':checked')) {
            $('#recurring-options').show();
            $('#recurring_type').prop('required', true);
            $('#recurring_end_date').prop('required', true);
        }
        
        // Set minimum date for recurring end date
        $('#tanggal_kegiatan').change(function() {
            const selectedDate = $(this).val();
            if(selectedDate) {
                $('#recurring_end_date').attr('min', selectedDate);
            }
        });
        
        // Set initial minimum date
        const initialDate = $('#tanggal_kegiatan').val();
        if(initialDate) {
            $('#recurring_end_date').attr('min', initialDate);
        }
    });
</script>
@endsection