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
                    
                    @php
                        $eventDate = \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan);
                        try {
                            $eventStartTime = $eventDate->copy()->setTimeFromTimeString($pelaksanaan->jam_mulai);
                        } catch (\Exception $e) {
                            $eventStartTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                                $eventDate->format('Y-m-d') . ' ' . substr($pelaksanaan->jam_mulai, 0, 5));
                        }
                        $canEditSchedule = \Carbon\Carbon::now()->lt($eventStartTime);
                    @endphp
                    
                    @if(!$canEditSchedule)
                        <div class="alert alert-danger">
                            <i class="fas fa-lock me-1"></i>
                            <strong>Jadwal Tidak Dapat Diedit:</strong> Kegiatan ini sudah berlangsung pada 
                            {{ $eventStartTime->format('d/m/Y H:i') }}. Jadwal yang sudah berlangsung tidak dapat diedit.
                        </div>
                    @endif
                    
                    <form action="{{ route('pelaksanaan.update', $pelaksanaan->id_pelaksanaan) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="id_kegiatan" class="form-label">Kegiatan</label>
                            <select class="form-select @error('id_kegiatan') is-invalid @enderror" 
                                    id="id_kegiatan" 
                                    name="id_kegiatan" 
                                    required
                                    {{ ($pelaksanaan->is_recurring || $pelaksanaan->parent_id) ? 'disabled' : '' }}>
                                <option value="">Pilih Kegiatan</option>
                                @foreach($kegiatan as $k)
                                    <option value="{{ $k->id_kegiatan }}" {{ old('id_kegiatan', $pelaksanaan->id_kegiatan) == $k->id_kegiatan ? 'selected' : '' }}>
                                        {{ $k->nama_kegiatan }} ({{ ucfirst($k->tipe_kegiatan) }})
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Hidden field for disabled select -->
                            @if($pelaksanaan->is_recurring || $pelaksanaan->parent_id)
                                <input type="hidden" name="id_kegiatan" value="{{ $pelaksanaan->id_kegiatan }}">
                                <small class="text-warning">
                                    <i class="fas fa-info-circle"></i>
                                    Kegiatan tidak dapat diubah untuk jadwal berulang.
                                </small>
                            @endif
                            
                            @error('id_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                            <input type="date" 
                                   class="form-control @error('tanggal_kegiatan') is-invalid @enderror" 
                                   id="tanggal_kegiatan" 
                                   name="tanggal_kegiatan" 
                                   value="{{ old('tanggal_kegiatan', $pelaksanaan->tanggal_kegiatan->format('Y-m-d')) }}" 
                                   required
                                   min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            @error('tanggal_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                    <input type="time" 
                                           class="form-control @error('jam_mulai') is-invalid @enderror" 
                                           id="jam_mulai" 
                                           name="jam_mulai" 
                                           value="{{ old('jam_mulai', substr($pelaksanaan->jam_mulai, 0, 5)) }}" 
                                           required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                    <input type="time" 
                                           class="form-control @error('jam_selesai') is-invalid @enderror" 
                                           id="jam_selesai" 
                                           name="jam_selesai" 
                                           value="{{ old('jam_selesai', substr($pelaksanaan->jam_selesai, 0, 5)) }}" 
                                           required>
                                    @error('jam_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" 
                                   class="form-control @error('lokasi') is-invalid @enderror" 
                                   id="lokasi" 
                                   name="lokasi" 
                                   value="{{ old('lokasi', $pelaksanaan->lokasi) }}">
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
                                                        id="recurring_type" 
                                                        name="recurring_type" 
                                                        required>
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
                                                @php
                                                    $currentEndDate = $pelaksanaan->recurring_end_date;
                                                    $canEditEndDate = $currentEndDate && \Carbon\Carbon::now()->lt($currentEndDate);
                                                @endphp
                                                
                                                <input type="date" 
                                                       class="form-control @error('recurring_end_date') is-invalid @enderror" 
                                                       id="recurring_end_date" 
                                                       name="recurring_end_date" 
                                                       value="{{ old('recurring_end_date', $pelaksanaan->recurring_end_date ? $pelaksanaan->recurring_end_date->format('Y-m-d') : '') }}" 
                                                       required
                                                       min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                                       {{ !$canEditEndDate ? 'readonly' : '' }}>
                                                
                                                @if(!$canEditEndDate)
                                                    <small class="text-warning">
                                                        <i class="fas fa-lock"></i>
                                                        Tanggal berakhir sudah tercapai atau terlewat, tidak dapat diubah.
                                                    </small>
                                                @else
                                                    <small class="text-muted">
                                                        Tanggal terakhir jadwal berulang
                                                    </small>
                                                @endif
                                                
                                                @error('recurring_end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Informasi Penting:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Total jadwal dalam seri: {{ $pelaksanaan->children()->count() + 1 }}</li>
                                            <li>Perubahan akan mempengaruhi jadwal yang belum berlangsung</li>
                                            <li>Jadwal yang sudah berlangsung tidak akan berubah (data kehadiran aman)</li>
                                            @if($canEditEndDate)
                                                <li><strong>Perpanjang tanggal berakhir:</strong> Akan otomatis generate jadwal baru</li>
                                                <li><strong>Percepat tanggal berakhir:</strong> Akan menghapus jadwal yang belum berlangsung</li>
                                            @endif
                                        </ul>
                                    </div>
                                    
                                    @if($canEditEndDate)
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <strong>Perhatian:</strong> Perubahan pada tanggal berakhir akan:
                                            <ul class="mb-0 mt-2">
                                                <li>Memperbarui semua jadwal yang belum berlangsung</li>
                                                <li>Generate jadwal baru jika tanggal diperpanjang</li>
                                                <li>Menghapus jadwal masa depan jika tanggal dipercepat</li>
                                            </ul>
                                        </div>
                                    @endif
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
                    
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Dapat Diedit:</div>
                        <div class="col-7">
                            @if($canEditSchedule)
                                <span class="badge bg-success">Ya</span>
                            @else
                                <span class="badge bg-danger">Tidak</span>
                                <br><small class="text-muted">Sudah berlangsung</small>
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
                        
                        @php
                            $completedCount = $pelaksanaan->children()
                                ->where('tanggal_kegiatan', '<', \Carbon\Carbon::now()->format('Y-m-d'))
                                ->count();
                            if (\Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->isPast()) {
                                $completedCount++;
                            }
                        @endphp
                        
                        <div class="row mb-2">
                            <div class="col-5 fw-bold">Selesai:</div>
                            <div class="col-7">{{ $completedCount }} jadwal</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-bold">Mendatang:</div>
                            <div class="col-7">{{ ($pelaksanaan->children()->count() + 1) - $completedCount }} jadwal</div>
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
                    
                    <hr>
                    
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
            
            @if($pelaksanaan->is_recurring)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-cogs me-1"></i>
                        Pengaturan Lanjutan
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('pelaksanaan.series', $pelaksanaan->id_pelaksanaan) }}" 
                               class="btn btn-outline-info btn-sm">
                                <i class="fas fa-list"></i> Lihat Semua Jadwal Seri
                            </a>
                            
                            <form action="{{ route('pelaksanaan.destroy-series', $pelaksanaan->id_pelaksanaan) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-outline-danger btn-sm w-100" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus SELURUH seri jadwal berulang ini?')">
                                    <i class="fas fa-trash-alt"></i> Hapus Seluruh Seri
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Only initialize select2 if not disabled
        if (!$('#id_kegiatan').prop('disabled')) {
            $('#id_kegiatan').select2({
                placeholder: "Pilih Kegiatan",
                allowClear: true
            });
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
        
        // Warning for end date changes
        $('#recurring_end_date').change(function() {
            const originalValue = "{{ $pelaksanaan->recurring_end_date ? $pelaksanaan->recurring_end_date->format('Y-m-d') : '' }}";
            const newValue = $(this).val();
            
            if (originalValue && newValue && originalValue !== newValue) {
                let message = '';
                if (newValue > originalValue) {
                    message = 'Perpanjangan tanggal berakhir akan menghasilkan jadwal baru secara otomatis.';
                } else {
                    message = 'Mempersingkat tanggal berakhir akan menghapus jadwal yang belum berlangsung.';
                }
                
                if (!confirm(message + ' Apakah Anda yakin ingin melanjutkan?')) {
                    $(this).val(originalValue);
                }
            }
        });
    });
</script>
@endsection