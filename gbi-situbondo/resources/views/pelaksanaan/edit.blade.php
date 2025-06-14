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
                    @if($pelaksanaan->is_recurring)
                        <span class="badge bg-primary ms-2">
                            <i class="fas fa-repeat"></i> Jadwal Berulang
                        </span>
                    @elseif($pelaksanaan->parent_id)
                        <span class="badge bg-info ms-2">
                            <i class="fas fa-link"></i> Bagian dari Seri
                        </span>
                    @endif
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
                    
                    @if($pelaksanaan->parent_id)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Perhatian:</strong> Ini adalah bagian dari jadwal berulang. 
                            Perubahan hanya berlaku untuk jadwal ini saja.
                            <br>
                            <a href="{{ route('pelaksanaan.edit', $pelaksanaan->parent_id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-edit"></i> Edit Jadwal Induk
                            </a>
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
                            <input type="date" class="form-control @error('tanggal_kegiatan') is-invalid @enderror" 
                                   id="tanggal_kegiatan" name="tanggal_kegiatan" 
                                   value="{{ old('tanggal_kegiatan', $pelaksanaan->tanggal_kegiatan->format('Y-m-d')) }}" required>
                            @error('tanggal_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control @error('jam_mulai') is-invalid @enderror" 
                                           id="jam_mulai" name="jam_mulai" 
                                           value="{{ old('jam_mulai', substr($pelaksanaan->jam_mulai, 0, 5)) }}" required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                    <input type="time" class="form-control @error('jam_selesai') is-invalid @enderror" 
                                           id="jam_selesai" name="jam_selesai" 
                                           value="{{ old('jam_selesai', substr($pelaksanaan->jam_selesai, 0, 5)) }}" required>
                                    @error('jam_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" class="form-control @error('lokasi') is-invalid @enderror" 
                                   id="lokasi" name="lokasi" value="{{ old('lokasi', $pelaksanaan->lokasi) }}">
                            @error('lokasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @if($pelaksanaan->is_recurring)
                            <!-- Opsi Edit Jadwal Berulang -->
                            <div class="card mt-4 mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-repeat me-1"></i>
                                        Pengaturan Jadwal Berulang
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recurring_type" class="form-label">Tipe Pengulangan</label>
                                                <select class="form-select @error('recurring_type') is-invalid @enderror" 
                                                        id="recurring_type" name="recurring_type" required>
                                                    <option value="">Pilih Tipe</option>
                                                    <option value="weekly" {{ old('recurring_type', $pelaksanaan->recurring_type) == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                                    <option value="monthly" {{ old('recurring_type', $pelaksanaan->recurring_type) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                                </select>
                                                @error('recurring_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recurring_end_date" class="form-label">Berakhir Pada</label>
                                                <input type="date" class="form-control @error('recurring_end_date') is-invalid @enderror" 
                                                       id="recurring_end_date" name="recurring_end_date" 
                                                       value="{{ old('recurring_end_date', $pelaksanaan->recurring_end_date ? $pelaksanaan->recurring_end_date->format('Y-m-d') : '') }}" 
                                                       required>
                                                @error('recurring_end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Tanggal terakhir jadwal berulang</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <strong>Perhatian:</strong> Perubahan pada jadwal berulang akan mempengaruhi semua jadwal yang belum terlaksana dalam seri ini.
                                        <ul class="mb-0 mt-2">
                                            <li>Total jadwal dalam seri: {{ $pelaksanaan->children()->count() + 1 }}</li>
                                            <li>Jadwal yang sudah terlaksana tidak akan berubah</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('pelaksanaan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            @if($pelaksanaan->is_recurring)
                                <a href="{{ route('pelaksanaan.series', $pelaksanaan->id_pelaksanaan) }}" class="btn btn-info">
                                    <i class="fas fa-list"></i> Lihat Semua Seri
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Informasi Jadwal
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Status:</div>
                        <div class="col-7">
                            @if($pelaksanaan->is_recurring)
                                <span class="badge bg-primary">Jadwal Induk Berulang</span>
                            @elseif($pelaksanaan->parent_id)
                                <span class="badge bg-info">Bagian dari Seri</span>
                            @else
                                <span class="badge bg-secondary">Jadwal Tunggal</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($pelaksanaan->is_recurring)
                        <div class="row mb-2">
                            <div class="col-5 fw-bold">Tipe:</div>
                            <div class="col-7">{{ ucfirst($pelaksanaan->recurring_type) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-bold">Berakhir:</div>
                            <div class="col-7">{{ $pelaksanaan->recurring_end_date ? $pelaksanaan->recurring_end_date->format('d/m/Y') : '-' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-bold">Total Seri:</div>
                            <div class="col-7">{{ $pelaksanaan->children()->count() + 1 }} jadwal</div>
                        </div>
                    @endif
                    
                    @if($pelaksanaan->parent_id)
                        <div class="row mb-2">
                            <div class="col-5 fw-bold">Induk Seri:</div>
                            <div class="col-7">
                                <a href="{{ route('pelaksanaan.show', $pelaksanaan->parent_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Dibuat:</div>
                        <div class="col-7">{{ $pelaksanaan->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Diubah:</div>
                        <div class="col-7">{{ $pelaksanaan->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
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