@extends('layouts.app')

@section('title', 'Edit Anggota')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Anggota</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Anggota Jemaat</a></li>
        <li class="breadcrumb-item active">Edit Anggota</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-edit me-1"></i>
                    Form Edit Anggota
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
                    
                    <form action="{{ route('anggota.update', $anggota->id_anggota) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $anggota->nama) }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('Y-m-d') : '') }}" required>
                                    @error('tanggal_lahir')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_ortu" class="form-label">Orang Tua</label>
                                    <select class="form-select @error('id_ortu') is-invalid @enderror" id="id_ortu" name="id_ortu">
                                        <option value="">Pilih Orang Tua</option>
                                        @foreach($allAnggota as $a)
                                            <option value="{{ $a->id_anggota }}" data-keluarga="{{ $a->id_keluarga }}" {{ old('id_ortu', $anggota->id_ortu) == $a->id_anggota ? 'selected' : '' }}>
                                                {{ $a->nama }} @if($a->keluarga) ({{ $a->keluarga->nama_keluarga }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_ortu')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Mengubah orang tua akan otomatis mengubah keluarga yang sama.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_keluarga" class="form-label">Keluarga</label>
                                    <select class="form-select @error('id_keluarga') is-invalid @enderror" id="id_keluarga" name="id_keluarga">
                                        <option value="">Pilih Keluarga</option>
                                        @foreach($keluarga as $k)
                                            <option value="{{ $k->id_keluarga }}" {{ old('id_keluarga', $anggota->id_keluarga) == $k->id_keluarga ? 'selected' : '' }}>
                                                {{ $k->nama_keluarga }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_keluarga')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Akan berubah otomatis jika orang tua diubah.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_telepon" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $anggota->no_telepon) }}">
                                    @error('no_telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $anggota->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="komsel" class="form-label">Kelompok Sel</label>
                                    <select class="form-select @error('komsel') is-invalid @enderror" id="komsel" name="komsel[]" multiple>
                                        @foreach($komsel as $k)
                                            <option value="{{ $k->id_komsel }}" {{ (is_array(old('komsel', $anggotaKomsel)) && in_array($k->id_komsel, old('komsel', $anggotaKomsel))) ? 'selected' : '' }}>
                                                {{ $k->nama_komsel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('komsel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3">{{ old('alamat', $anggota->alamat) }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('anggota.index') }}" class="btn btn-secondary">Batal</a>
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
        // Initialize multiple select
        $('#komsel').select2({
            placeholder: "Pilih Kelompok Sel",
            allowClear: true
        });

        // Store original values
        var originalKeluarga = $('#id_keluarga').val();
        var originalOrtu = $('#id_ortu').val();

        // Auto-fill keluarga when parent is selected
        $('#id_ortu').change(function() {
            var selectedOption = $(this).find('option:selected');
            var keluargaId = selectedOption.data('keluarga');
            var currentValue = $(this).val();
            
            // Only auto-fill if parent changed and has a family
            if (currentValue !== originalOrtu && keluargaId) {
                $('#id_keluarga').val(keluargaId);
                $('#id_keluarga').addClass('bg-warning');
                
                // Show warning message
                if (!$('#parent-change-warning').length) {
                    $('#id_keluarga').parent().append('<small id="parent-change-warning" class="text-warning"><i class="fas fa-exclamation-triangle"></i> Keluarga akan berubah karena orang tua diubah. Pastikan perubahan ini sesuai.</small>');
                }
            } else if (currentValue === originalOrtu) {
                // Reset to original if parent selection is reverted
                $('#id_keluarga').val(originalKeluarga);
                $('#id_keluarga').removeClass('bg-warning');
                $('#parent-change-warning').remove();
            } else if (!currentValue) {
                // Clear family if no parent selected (and it wasn't manually set)
                $('#id_keluarga').removeClass('bg-warning');
                $('#parent-change-warning').remove();
            }
        });

        // Reset keluarga highlight when manually changed
        $('#id_keluarga').change(function() {
            $(this).removeClass('bg-warning');
            $('#parent-change-warning').remove();
        });

        // Show confirmation if both parent and family are changed
        $('form').submit(function(e) {
            var parentChanged = $('#id_ortu').val() !== originalOrtu;
            var familyChanged = $('#id_keluarga').val() !== originalKeluarga;
            
            if (parentChanged && familyChanged) {
                if (!confirm('Anda mengubah orang tua dan keluarga. Ini akan mempengaruhi hubungan keluarga. Apakah Anda yakin?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
@endsection