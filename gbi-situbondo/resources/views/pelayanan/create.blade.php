@extends('layouts.app')

@section('title', 'Tambah Jadwal Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Jadwal Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Jadwal Pelayanan</a></li>
        <li class="breadcrumb-item active">Tambah Jadwal</li>
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
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-plus me-1"></i>
            Form Tambah Jadwal Pelayanan
        </div>
        <div class="card-body">
            <form action="{{ route('pelayanan.store') }}" method="POST" id="jadwalForm">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_kegiatan" class="form-label">Kegiatan</label>
                            <select class="form-select @error('id_kegiatan') is-invalid @enderror" id="id_kegiatan" name="id_kegiatan" required>
                                @foreach($allKegiatan as $k)
                                    <option value="{{ $k->id_kegiatan }}" {{ $kegiatan->id_kegiatan == $k->id_kegiatan ? 'selected' : '' }}>
                                        {{ $k->nama_kegiatan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_pelayanan" class="form-label">Tanggal Pelayanan</label>
                            <input type="date" class="form-control @error('tanggal_pelayanan') is-invalid @enderror" id="tanggal_pelayanan" name="tanggal_pelayanan" value="{{ $tanggal }}" required>
                            @error('tanggal_pelayanan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h5>Petugas Pelayanan</h5>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tentukan posisi dan petugas untuk pelayanan ini. Tambahkan baris sesuai kebutuhan.
                    </div>
                </div>
                
                <div id="petugas-container">
                    @if(count($existingJadwal) > 0)
                        @foreach($existingJadwal as $index => $jadwal)
                            <div class="row mb-3 petugas-row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="form-label">Posisi</label>
                                        <select class="form-select posisi-select" name="petugas[{{ $index }}][posisi]" required>
                                            <option value="">Pilih Posisi</option>
                                            @foreach($posisiOptions as $posisi)
                                                <option value="{{ $posisi }}" {{ $jadwal->posisi == $posisi ? 'selected' : '' }}>{{ $posisi }}</option>
                                            @endforeach
                                            <option value="custom" {{ !in_array($jadwal->posisi, $posisiOptions) ? 'selected' : '' }}>Lainnya...</option>
                                        </select>
                                        <div class="custom-posisi-container mt-2 {{ !in_array($jadwal->posisi, $posisiOptions) ? '' : 'd-none' }}">
                                            <input type="text" class="form-control custom-posisi" value="{{ !in_array($jadwal->posisi, $posisiOptions) ? $jadwal->posisi : '' }}" placeholder="Masukkan posisi lainnya">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="form-label">Petugas</label>
                                        <select class="form-select petugas-select" name="petugas[{{ $index }}][id_anggota]" required>
                                            <option value="">Pilih Petugas</option>
                                            @foreach($anggota as $a)
                                                <option value="{{ $a->id_anggota }}" {{ $jadwal->id_anggota == $a->id_anggota ? 'selected' : '' }}>{{ $a->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-petugas">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row mb-3 petugas-row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="form-label">Posisi</label>
                                    <select class="form-select posisi-select" name="petugas[0][posisi]" required>
                                        <option value="">Pilih Posisi</option>
                                        @foreach($posisiOptions as $posisi)
                                            <option value="{{ $posisi }}">{{ $posisi }}</option>
                                        @endforeach
                                        <option value="custom">Lainnya...</option>
                                    </select>
                                    <div class="custom-posisi-container mt-2 d-none">
                                        <input type="text" class="form-control custom-posisi" placeholder="Masukkan posisi lainnya">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="form-label">Petugas</label>
                                    <select class="form-select petugas-select" name="petugas[0][id_anggota]" required>
                                        <option value="">Pilih Petugas</option>
                                        @foreach($anggota as $a)
                                            <option value="{{ $a->id_anggota }}">{{ $a->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-petugas">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="mb-3">
                    <button type="button" class="btn btn-success" id="add-petugas">
                        <i class="fas fa-plus"></i> Tambah Petugas
                    </button>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#id_kegiatan').select2();
        
        // Redirect to the correct URL when changing kegiatan or tanggal
        $('#id_kegiatan, #tanggal_pelayanan').change(function() {
            const kegiatan = $('#id_kegiatan').val();
            const tanggal = $('#tanggal_pelayanan').val();
            
            if (kegiatan && tanggal) {
                window.location.href = "{{ route('pelayanan.create') }}?id_kegiatan=" + kegiatan + "&tanggal=" + tanggal;
            }
        });
        
        // Initialize Select2 for existing selects
        $('.petugas-select').select2();
        
        // Handle custom position
        $(document).on('change', '.posisi-select', function() {
            const customPosisiContainer = $(this).siblings('.custom-posisi-container');
            const customPosisi = customPosisiContainer.find('.custom-posisi');
            
            if ($(this).val() === 'custom') {
                customPosisiContainer.removeClass('d-none');
                customPosisi.prop('required', true);
                
                // Update select value when typing custom position
                customPosisi.on('input', function() {
                    $(this).closest('.form-group').find('.posisi-select').attr('data-custom-value', $(this).val());
                });
            } else {
                customPosisiContainer.addClass('d-none');
                customPosisi.prop('required', false);
            }
        });
        
        // Add petugas row
        let rowIndex = $('.petugas-row').length;
        
        $('#add-petugas').click(function() {
            const newRow = `
                <div class="row mb-3 petugas-row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-label">Posisi</label>
                            <select class="form-select posisi-select" name="petugas[${rowIndex}][posisi]" required>
                                <option value="">Pilih Posisi</option>
                                @foreach($posisiOptions as $posisi)
                                    <option value="{{ $posisi }}">{{ $posisi }}</option>
                                @endforeach
                                <option value="custom">Lainnya...</option>
                            </select>
                            <div class="custom-posisi-container mt-2 d-none">
                                <input type="text" class="form-control custom-posisi" placeholder="Masukkan posisi lainnya">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-label">Petugas</label>
                            <select class="form-select petugas-select" name="petugas[${rowIndex}][id_anggota]" required>
                                <option value="">Pilih Petugas</option>
                                @foreach($anggota as $a)
                                    <option value="{{ $a->id_anggota }}">{{ $a->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-petugas">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            
            $('#petugas-container').append(newRow);
            
            // Initialize Select2 for new selects
            $('.petugas-select').select2();
            
            rowIndex++;
        });
        
        // Remove petugas row
        $(document).on('click', '.remove-petugas', function() {
            if ($('.petugas-row').length > 1) {
                $(this).closest('.petugas-row').remove();
            } else {
                alert('Minimal harus ada satu petugas.');
            }
        });
        
        // Form submit
        $('#jadwalForm').submit(function(e) {
            // Handle custom positions before submit
            $('.posisi-select').each(function() {
                if ($(this).val() === 'custom') {
                    const customValue = $(this).closest('.form-group').find('.custom-posisi').val();
                    if (customValue) {
                        $(this).val(customValue);
                    } else {
                        e.preventDefault();
                        alert('Harap isi posisi kustom.');
                    }
                }
            });
        });
    });
</script>
@endsection