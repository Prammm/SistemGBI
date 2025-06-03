@extends('layouts.app')

@section('title', 'Generator Jadwal Pelayanan Advanced')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Generator Jadwal Pelayanan Advanced</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item active">Generator Advanced</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-magic me-1"></i>
                    Generator Jadwal Pelayanan Otomatis Advanced
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
                    
                    <form action="{{ route('pelayanan.generate') }}" method="POST" id="generator-form">
                        @csrf
                        
                        <!-- Generator Type Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Tipe Generator</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="generation_type" id="single" value="single" checked>
                                                    <label class="form-check-label" for="single">
                                                        <strong>Single Event</strong><br>
                                                        <small class="text-muted">Generate jadwal untuk satu atau beberapa kegiatan tertentu</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="generation_type" id="bulk_monthly" value="bulk_monthly">
                                                    <label class="form-check-label" for="bulk_monthly">
                                                        <strong>Bulk Monthly</strong><br>
                                                        <small class="text-muted">Generate jadwal untuk seluruh bulan dengan rotasi otomatis</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Single Event Configuration -->
                        <div class="row mb-4" id="single-config">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Pilih Kegiatan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Kegiatan yang akan dijadwalkan:</label>
                                            <select class="form-select" name="id_pelaksanaan[]" id="pelaksanaan-select" multiple>
                                                @foreach($pelaksanaan as $p)
                                                    <option value="{{ $p->id_pelaksanaan }}">
                                                        {{ $p->kegiatan->nama_kegiatan }} - 
                                                        {{ \Carbon\Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') }} 
                                                        {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Monthly Configuration -->
                        <div class="row mb-4" id="monthly-config" style="display: none;">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Pengaturan Bulanan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Bulan/Tahun:</label>
                                                    <input type="month" class="form-control" name="month_year" value="{{ date('Y-m') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Max Pelayanan per Bulan:</label>
                                                    <input type="number" class="form-control" name="max_services_per_month" value="3" min="1" max="10">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Algorithm Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">Algoritma Penjadwalan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="algorithm" id="balanced" value="balanced" checked>
                                                    <label class="form-check-label" for="balanced">
                                                        <strong>Balanced</strong><br>
                                                        <small class="text-muted">Kombinasi seimbang antara reguler, rotasi, dan ketersediaan</small>
                                                    </label>
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="algorithm" id="regular_priority" value="regular_priority">
                                                    <label class="form-check-label" for="regular_priority">
                                                        <strong>Regular Priority</strong><br>
                                                        <small class="text-muted">Mengutamakan pemain reguler untuk konsistensi pelayanan</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="algorithm" id="fair_rotation" value="fair_rotation">
                                                    <label class="form-check-label" for="fair_rotation">
                                                        <strong>Fair Rotation</strong><br>
                                                        <small class="text-muted">Rotasi adil untuk memberikan kesempatan yang sama</small>
                                                    </label>
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="algorithm" id="workload_based" value="workload_based">
                                                    <label class="form-check-label" for="workload_based">
                                                        <strong>Workload Based</strong><br>
                                                        <small class="text-muted">Berdasarkan beban kerja untuk distribusi yang merata</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bobot_reguler" class="form-label">Bobot Pemain Reguler (1-10):</label>
                                                    <input type="range" class="form-range" id="bobot_reguler" name="bobot_reguler" min="1" max="10" value="5">
                                                    <div class="d-flex justify-content-between text-muted small">
                                                        <span>1 (Minimal)</span>
                                                        <span id="bobot-value">5</span>
                                                        <span>10 (Maksimal)</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="avoid_consecutive" name="avoid_consecutive" value="1">
                                                    <label class="form-check-label" for="avoid_consecutive">
                                                        Hindari jadwal berturut-turut untuk anggota yang sama
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Template Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Template Jadwal</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Pilih Template (Opsional):</label>
                                            <select class="form-select" name="template_id" id="template-select">
                                                <option value="">-- Pilih Template atau Custom --</option>
                                                @foreach($templates as $template)
                                                    <option value="{{ $template->id }}" data-positions="{{ json_encode($template->posisi_required) }}">
                                                        {{ $template->nama_template }} - {{ $template->deskripsi }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Position Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">Posisi yang Akan Dijadwalkan</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($positionCategories as $category => $positions)
                                            <div class="mb-4">
                                                <h6 class="text-primary border-bottom pb-2">{{ $category }}</h6>
                                                <div class="row">
                                                    @foreach($positions as $position)
                                                        <div class="col-md-3">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input position-checkbox" type="checkbox" 
                                                                    id="pos_{{ Str::slug($position) }}" 
                                                                    name="positions[]" 
                                                                    value="{{ $position }}"
                                                                    {{ in_array($position, ['Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Drum', 'Sound System', 'Multimedia', 'Usher']) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="pos_{{ Str::slug($position) }}">
                                                                    {{ $position }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="select-all-positions">Pilih Semua</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-positions">Batalkan Semua</button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-common-positions">Posisi Umum</button>
                                            <button type="button" class="btn btn-sm btn-outline-info" id="select-music-positions">Tim Musik</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Member Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0">Pilih Anggota Pelayanan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="search-anggota" placeholder="Cari anggota...">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                        </div>
                                        
                                        <div class="row" id="anggota-list">
                                            @foreach($anggota->chunk(ceil($anggota->count() / 3)) as $chunk)
                                                <div class="col-md-4">
                                                    @foreach($chunk as $a)
                                                        <div class="form-check mb-2 anggota-item" data-name="{{ strtolower($a->nama) }}">
                                                            <input class="form-check-input anggota-checkbox" type="checkbox" 
                                                                id="anggota_{{ $a->id_anggota }}" 
                                                                name="anggota[]" 
                                                                value="{{ $a->id_anggota }}" 
                                                                checked>
                                                            <label class="form-check-label" for="anggota_{{ $a->id_anggota }}">
                                                                {{ $a->nama }}
                                                                
                                                                @php
                                                                    $regularPositions = $a->spesialisasi
                                                                        ->where('is_reguler', true)
                                                                        ->pluck('posisi')
                                                                        ->take(3)
                                                                        ->implode(', ');
                                                                @endphp
                                                                
                                                                @if($regularPositions)
                                                                    <br><small class="text-success">
                                                                        <i class="fas fa-star"></i> {{ $regularPositions }}
                                                                        @if($a->spesialisasi->where('is_reguler', true)->count() > 3)
                                                                            <span class="text-muted">+{{ $a->spesialisasi->where('is_reguler', true)->count() - 3 }} lagi</span>
                                                                        @endif
                                                                    </small>
                                                                @endif
                                                                
                                                                @php
                                                                    $availability = '';
                                                                    if (!empty($a->ketersediaan_hari)) {
                                                                        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                                                                        $availableDays = collect($a->ketersediaan_hari)->map(fn($day) => $days[$day])->implode(',');
                                                                        $availability = $availableDays;
                                                                    }
                                                                @endphp
                                                                
                                                                @if($availability)
                                                                    <br><small class="text-info">
                                                                        <i class="fas fa-calendar"></i> {{ $availability }}
                                                                    </small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="select-all-anggota">Pilih Semua</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-anggota">Batalkan Semua</button>
                                            <button type="button" class="btn btn-sm btn-outline-success" id="select-regular-anggota">Pilih Pemain Reguler</button>
                                            <button type="button" class="btn btn-sm btn-outline-info" id="select-available-anggota">Pilih yang Available</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview & Generate -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Preview & Generate</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h6>Posisi Dipilih</h6>
                                                    <span class="badge bg-success fs-6" id="positions-count">0</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h6>Anggota Dipilih</h6>
                                                    <span class="badge bg-info fs-6" id="anggota-count">0</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h6>Estimasi Kebutuhan</h6>
                                                    <span class="badge bg-warning fs-6" id="requirement-ratio">0:0</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 text-center">
                                            <button type="button" class="btn btn-outline-primary me-2" id="preview-btn">
                                                <i class="fas fa-eye"></i> Preview
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-magic"></i> Generate Jadwal
                                            </button>
                                            <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary ms-2">Batal</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Penjadwalan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="preview-content">
                    <!-- Preview content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="$('#generator-form').submit()">Proses Generate</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#pelaksanaan-select').select2({
            placeholder: "Pilih Kegiatan",
            allowClear: true
        });
        
        $('#template-select').select2({
            placeholder: "Pilih Template",
            allowClear: true
        });
        
        // Toggle configuration based on generation type
        $('input[name="generation_type"]').change(function() {
            if ($(this).val() === 'single') {
                $('#single-config').show();
                $('#monthly-config').hide();
            } else {
                $('#single-config').hide();
                $('#monthly-config').show();
            }
        });
        
        // Update bobot reguler display
        $('#bobot_reguler').on('input', function() {
            $('#bobot-value').text($(this).val());
        });
        
        // Template selection auto-fill positions
        $('#template-select').change(function() {
            const selectedOption = $(this).find('option:selected');
            const positions = selectedOption.data('positions');
            
            if (positions) {
                $('.position-checkbox').prop('checked', false);
                positions.forEach(function(position) {
                    $(`input[value="${position}"]`).prop('checked', true);
                });
                updateCounters();
            }
        });
        
        // Position selection buttons
        $('#select-all-positions').click(function() {
            $('.position-checkbox').prop('checked', true);
            updateCounters();
        });
        
        $('#deselect-all-positions').click(function() {
            $('.position-checkbox').prop('checked', false);
            updateCounters();
        });
        
        $('#select-common-positions').click(function() {
            $('.position-checkbox').prop('checked', false);
            const commonPositions = ['Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Drum', 'Sound System', 'Multimedia', 'Usher'];
            commonPositions.forEach(function(position) {
                $(`input[value="${position}"]`).prop('checked', true);
            });
            updateCounters();
        });
        
        $('#select-music-positions').click(function() {
            $('.position-checkbox').prop('checked', false);
            const musicPositions = ['Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Bass', 'Drum'];
            musicPositions.forEach(function(position) {
                $(`input[value="${position}"]`).prop('checked', true);
            });
            updateCounters();
        });
        
        // Member selection buttons
        $('#select-all-anggota').click(function() {
            $('.anggota-checkbox').prop('checked', true);
            updateCounters();
        });
        
        $('#deselect-all-anggota').click(function() {
            $('.anggota-checkbox').prop('checked', false);
            updateCounters();
        });
        
        $('#select-regular-anggota').click(function() {
            $('.anggota-checkbox').prop('checked', false);
            $('.anggota-checkbox').each(function() {
                const label = $(this).next('label');
                if (label.find('.text-success').length > 0) {
                    $(this).prop('checked', true);
                }
            });
            updateCounters();
        });
        
        $('#select-available-anggota').click(function() {
            $('.anggota-checkbox').prop('checked', false);
            $('.anggota-checkbox').each(function() {
                const label = $(this).next('label');
                if (label.find('.text-info').length > 0) {
                    $(this).prop('checked', true);
                }
            });
            updateCounters();
        });
        
        // Search functionality
        $('#search-anggota').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.anggota-item').each(function() {
                const name = $(this).data('name');
                if (name.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Update counters when checkboxes change
        $('.position-checkbox, .anggota-checkbox').change(function() {
            updateCounters();
        });
        
        // Preview functionality
        $('#preview-btn').click(function() {
            generatePreview();
        });
        
        // Initialize counters
        updateCounters();
        
        function updateCounters() {
            const positionsCount = $('.position-checkbox:checked').length;
            const anggotaCount = $('.anggota-checkbox:checked').length;
            
            $('#positions-count').text(positionsCount);
            $('#anggota-count').text(anggotaCount);
            
            if (positionsCount > 0 && anggotaCount > 0) {
                const ratio = Math.round((anggotaCount / positionsCount) * 10) / 10;
                $('#requirement-ratio').text(`${anggotaCount}:${positionsCount} (${ratio}:1)`);
                
                if (ratio < 1) {
                    $('#requirement-ratio').removeClass('bg-warning bg-success').addClass('bg-danger');
                } else if (ratio < 2) {
                    $('#requirement-ratio').removeClass('bg-danger bg-success').addClass('bg-warning');
                } else {
                    $('#requirement-ratio').removeClass('bg-danger bg-warning').addClass('bg-success');
                }
            } else {
                $('#requirement-ratio').text('0:0');
            }
        }
        
        function generatePreview() {
            const formData = new FormData($('#generator-form')[0]);
            
            // Show loading
            $('#preview-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Generating preview...</div>');
            $('#previewModal').modal('show');
            
            // Simulate preview generation (replace with actual AJAX call)
            setTimeout(function() {
                const positionsCount = $('.position-checkbox:checked').length;
                const anggotaCount = $('.anggota-checkbox:checked').length;
                const generationType = $('input[name="generation_type"]:checked').val();
                const algorithm = $('input[name="algorithm"]:checked').val();
                
                let previewHtml = `
                    <div class="alert alert-info">
                        <h6>Konfigurasi Preview:</h6>
                        <ul class="mb-0">
                            <li>Tipe: ${generationType === 'single' ? 'Single Event' : 'Bulk Monthly'}</li>
                            <li>Algoritma: ${algorithm}</li>
                            <li>Posisi: ${positionsCount} posisi</li>
                            <li>Anggota: ${anggotaCount} anggota</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6>Estimasi Hasil:</h6>
                        <p>Berdasarkan konfigurasi ini, sistem akan mencoba mengalokasikan ${positionsCount} posisi dengan ${anggotaCount} anggota yang tersedia.</p>
                        
                        ${anggotaCount / positionsCount < 1 ? 
                            '<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Peringatan: Jumlah anggota kurang dari jumlah posisi yang dibutuhkan.</p>' : 
                            '<p class="text-success"><i class="fas fa-check"></i> Jumlah anggota mencukupi untuk semua posisi.</p>'
                        }
                    </div>
                `;
                
                $('#preview-content').html(previewHtml);
            }, 1000);
        }
    });
</script>
@endsection