@extends('layouts.app')

@section('title', 'Presensi Kehadiran')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Presensi Kehadiran</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kehadiran.index') }}">Presensi Kehadiran</a></li>
        <li class="breadcrumb-item active">Form Presensi</li>
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
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-clipboard-check me-1"></i>
                    Form Presensi Kehadiran
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
                    
                    <div class="alert alert-info">
                        <strong>Kegiatan:</strong> {{ $pelaksanaan->kegiatan->nama_kegiatan }} <br>
                        <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('d/m/Y') }} <br>
                        <strong>Waktu:</strong> {{ \Carbon\Carbon::parse($pelaksanaan->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pelaksanaan->jam_selesai)->format('H:i') }} <br>
                        <strong>Lokasi:</strong> {{ $pelaksanaan->lokasi ?: '-' }}
                    </div>
                    
                    <form action="{{ route('kehadiran.store') }}" method="POST">
                        @csrf
                        
                        <input type="hidden" name="id_pelaksanaan" value="{{ $pelaksanaan->id_pelaksanaan }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Daftar Anggota</label>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label fw-bold" for="selectAll">
                                        Pilih Semua
                                    </label>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    <div class="row">
                                        @foreach($anggota->chunk(ceil($anggota->count() / 3)) as $chunk)
                                            <div class="col-md-4">
                                                @foreach($chunk as $a)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input anggota-checkbox" type="checkbox" id="anggota_{{ $a->id_anggota }}" name="anggota[]" value="{{ $a->id_anggota }}" {{ in_array($a->id_anggota, $kehadiran) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="anggota_{{ $a->id_anggota }}">
                                                            {{ $a->nama }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Presensi
                            </button>
                            <a href="{{ route('kehadiran.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
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
        // Handle select all checkbox
        $('#selectAll').click(function() {
            $('.anggota-checkbox').prop('checked', this.checked);
        });
        
        // Update selectAll status based on individual checkboxes
        $('.anggota-checkbox').click(function() {
            if($('.anggota-checkbox:checked').length == $('.anggota-checkbox').length) {
                $('#selectAll').prop('checked', true);
            } else {
                $('#selectAll').prop('checked', false);
            }
        });
        
        // Set initial state of selectAll
        if($('.anggota-checkbox:checked').length == $('.anggota-checkbox').length && $('.anggota-checkbox').length > 0) {
            $('#selectAll').prop('checked', true);
        }
    });
</script>
@endsection