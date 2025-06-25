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
                            
                            <!-- Search Bar -->
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="searchAnggota" placeholder="Cari nama anggota...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    Total: <span id="totalAnggota">{{ $anggota->count() }}</span> anggota | 
                                    Ditampilkan: <span id="visibleAnggota">{{ $anggota->count() }}</span> anggota |
                                    Dipilih: <span id="selectedAnggota">{{ count($kehadiran) }}</span> anggota
                                </small>
                            </div>
                            
                            <!-- Control Buttons -->
                            <div class="mb-2 d-flex gap-2 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label fw-bold" for="selectAll">
                                        Pilih Semua
                                    </label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectVisible">
                                    <i class="fas fa-check-square"></i> Pilih yang Terlihat
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectVisible">
                                    <i class="fas fa-square"></i> Hapus Pilihan yang Terlihat
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" id="toggleVisible">
                                    <i class="fas fa-exchange-alt"></i> Toggle yang Terlihat
                                </button>
                            </div>
                            
                            <!-- Member List -->
                            <div class="card">
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    <div class="row" id="anggotaContainer">
                                        @foreach($anggota->chunk(ceil($anggota->count() / 3)) as $chunk)
                                            <div class="col-md-4">
                                                @foreach($chunk as $a)
                                                    <div class="form-check mb-2 anggota-item" data-name="{{ strtolower($a->nama) }}">
                                                        <input class="form-check-input anggota-checkbox" type="checkbox" id="anggota_{{ $a->id_anggota }}" name="anggota[]" value="{{ $a->id_anggota }}" {{ in_array($a->id_anggota, $kehadiran) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="anggota_{{ $a->id_anggota }}">
                                                            {{ $a->nama }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- No results message -->
                                    <div id="noResults" class="text-center text-muted py-3" style="display: none;">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <p class="mb-0">Tidak ada anggota yang ditemukan</p>
                                        <small>Coba gunakan kata kunci yang berbeda</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Presensi (<span id="selectedCount">{{ count($kehadiran) }}</span>)
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
        // Update counters
        function updateCounters() {
            const totalAnggota = $('.anggota-item').length;
            const visibleAnggota = $('.anggota-item:visible').length;
            const selectedAnggota = $('.anggota-checkbox:checked').length;
            
            $('#totalAnggota').text(totalAnggota);
            $('#visibleAnggota').text(visibleAnggota);
            $('#selectedAnggota').text(selectedAnggota);
            $('#selectedCount').text(selectedAnggota);
            
            // Update selectAll checkbox state
            const visibleCheckboxes = $('.anggota-item:visible .anggota-checkbox');
            const checkedVisibleCheckboxes = $('.anggota-item:visible .anggota-checkbox:checked');
            
            if (visibleCheckboxes.length > 0 && checkedVisibleCheckboxes.length === visibleCheckboxes.length) {
                $('#selectAll').prop('checked', true).prop('indeterminate', false);
            } else if (checkedVisibleCheckboxes.length > 0) {
                $('#selectAll').prop('checked', false).prop('indeterminate', true);
            } else {
                $('#selectAll').prop('checked', false).prop('indeterminate', false);
            }
        }
        
        // Search functionality
        $('#searchAnggota').on('input', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            let hasResults = false;
            
            $('.anggota-item').each(function() {
                const name = $(this).data('name');
                if (name.includes(searchTerm)) {
                    $(this).show();
                    hasResults = true;
                } else {
                    $(this).hide();
                }
            });
            
            // Show/hide no results message
            if (hasResults || searchTerm === '') {
                $('#noResults').hide();
                $('#anggotaContainer').show();
            } else {
                $('#noResults').show();
                $('#anggotaContainer').hide();
            }
            
            updateCounters();
        });
        
        // Clear search
        $('#clearSearch').click(function() {
            $('#searchAnggota').val('');
            $('.anggota-item').show();
            $('#noResults').hide();
            $('#anggotaContainer').show();
            updateCounters();
        });
        
        // Select all visible checkboxes
        $('#selectAll').click(function() {
            const isChecked = this.checked;
            $('.anggota-item:visible .anggota-checkbox').prop('checked', isChecked);
            updateCounters();
        });
        
        // Select visible button
        $('#selectVisible').click(function() {
            $('.anggota-item:visible .anggota-checkbox').prop('checked', true);
            updateCounters();
        });
        
        // Deselect visible button
        $('#deselectVisible').click(function() {
            $('.anggota-item:visible .anggota-checkbox').prop('checked', false);
            updateCounters();
        });
        
        // Toggle visible button
        $('#toggleVisible').click(function() {
            $('.anggota-item:visible .anggota-checkbox').each(function() {
                $(this).prop('checked', !$(this).prop('checked'));
            });
            updateCounters();
        });
        
        // Update counters when individual checkboxes are clicked
        $('.anggota-checkbox').click(function() {
            updateCounters();
        });
        
        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Ctrl+F or Cmd+F to focus search
            if ((e.ctrlKey || e.metaKey) && e.which === 70 && !e.shiftKey) {
                e.preventDefault();
                $('#searchAnggota').focus();
            }
            
            // Escape to clear search
            if (e.which === 27) {
                $('#clearSearch').click();
            }
            
            // Ctrl+A or Cmd+A to select all visible
            if ((e.ctrlKey || e.metaKey) && e.which === 65 && $('#searchAnggota').is(':focus')) {
                e.preventDefault();
                $('#selectVisible').click();
            }
        });
        
        // Focus search on page load if there are many members
        if ($('.anggota-item').length > 20) {
            $('#searchAnggota').focus();
        }
        
        // Initial counter update
        updateCounters();
        
        // Auto-save search term in session storage
        const savedSearch = sessionStorage.getItem('anggotaSearch');
        if (savedSearch) {
            $('#searchAnggota').val(savedSearch).trigger('input');
        }
        
        $('#searchAnggota').on('input', function() {
            sessionStorage.setItem('anggotaSearch', $(this).val());
        });
    });
</script>

<style>
    .anggota-item {
        transition: all 0.2s ease;
    }
    
    .anggota-item:hover {
        background-color: rgba(0,123,255,.1);
        border-radius: 4px;
        padding: 2px 4px;
        margin: -2px -4px;
    }
    
    #searchAnggota:focus {
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
    }
    
    .btn-group-sm .btn {
        font-size: 0.875rem;
    }
    
    /* Highlight matching text */
    .highlight {
        background-color: yellow;
        font-weight: bold;
    }
</style>
@endsection