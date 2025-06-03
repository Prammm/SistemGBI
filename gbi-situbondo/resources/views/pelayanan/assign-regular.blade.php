@extends('layouts.app')

@section('title', 'Assign Regular - ' . $anggota->nama)

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Assign Pemain Reguler</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.members') }}">Anggota Pelayanan</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.member-profile', $anggota->id_anggota) }}">{{ $anggota->nama }}</a></li>
        <li class="breadcrumb-item active">Assign Regular</li>
    </ol>
    
    <div class="row">
        <!-- Member Info -->
        <div class="col-xl-4">
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user me-1"></i>
                    Informasi Anggota
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                    <h4>{{ $anggota->nama }}</h4>
                    <p class="text-muted">{{ $anggota->email ?: 'Email tidak tersedia' }}</p>
                    
                    @php
                        $currentRegular = $anggota->spesialisasi->where('is_reguler', true)->count();
                        $totalSpecs = $anggota->spesialisasi->count();
                    @endphp
                    
                    <div class="row text-center mt-4">
                        <div class="col-6">
                            <div class="border rounded p-2 bg-light">
                                <h5 class="text-success mb-0">{{ $currentRegular }}</h5>
                                <small class="text-muted">Posisi Reguler</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 bg-light">
                                <h5 class="text-info mb-0">{{ $totalSpecs }}</h5>
                                <small class="text-muted">Total Spesialisasi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Guidelines -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-lightbulb me-1"></i>
                    Panduan Assign Regular
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Pemain Reguler:</strong> Melayani secara konsisten setiap minggu/bulan
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-users text-primary"></i>
                            <strong>Rekomendasi:</strong> 1-3 posisi reguler per anggota
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-balance-scale text-warning"></i>
                            <strong>Pertimbangan:</strong> Kemampuan, komitmen, dan ketersediaan
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-rotate text-info"></i>
                            <strong>Rotasi:</strong> Non-reguler tetap mendapat kesempatan
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Assignment Form -->
        <div class="col-xl-8">
            <form action="{{ route('pelayanan.save-regular-assignment', $anggota->id_anggota) }}" method="POST">
                @csrf
                
                <!-- Current Specializations -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-star me-1"></i>
                        Assign Posisi Reguler dari Spesialisasi yang Ada
                    </div>
                    <div class="card-body">
                        @if($anggota->spesialisasi->isNotEmpty())
                            <p class="text-muted mb-3">Pilih posisi mana yang akan dijadikan posisi reguler:</p>
                            
                            @foreach($positionCategories as $category => $positions)
                                @php
                                    $categorySpecs = $anggota->spesialisasi->whereIn('posisi', $positions);
                                @endphp
                                
                                @if($categorySpecs->isNotEmpty())
                                    <div class="mb-4">
                                        <h6 class="text-primary border-bottom pb-2">{{ $category }}</h6>
                                        <div class="row">
                                            @foreach($categorySpecs as $spec)
                                                <div class="col-md-6 mb-3">
                                                    <div class="card {{ $spec->is_reguler ? 'border-success' : 'border-light' }}">
                                                        <div class="card-body p-3">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input regular-toggle" 
                                                                    type="checkbox" 
                                                                    id="regular_{{ $spec->id }}" 
                                                                    name="regular_positions[]" 
                                                                    value="{{ $spec->posisi }}"
                                                                    {{ $spec->is_reguler ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="regular_{{ $spec->id }}">
                                                                    {{ $spec->posisi }}
                                                                </label>
                                                            </div>
                                                            
                                                            <div class="mt-2">
                                                                <small class="text-muted">
                                                                    <strong>Prioritas:</strong> {{ $spec->prioritas }}/10
                                                                </small>
                                                                @if($spec->catatan)
                                                                    <br><small class="text-muted">{{ $spec->catatan }}</small>
                                                                @endif
                                                            </div>
                                                            
                                                            @php
                                                                $positionServices = $anggota->jadwalPelayanan()
                                                                    ->where('posisi', $spec->posisi)
                                                                    ->count();
                                                                $lastService = $anggota->getLastServiceDate($spec->posisi);
                                                            @endphp
                                                            
                                                            <div class="mt-2">
                                                                <span class="badge bg-secondary">{{ $positionServices }} kali pelayanan</span>
                                                                @if($lastService)
                                                                    <br><small class="text-muted">
                                                                        Terakhir: {{ \Carbon\Carbon::parse($lastService)->diffForHumans() }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                                <h5>Belum Ada Spesialisasi</h5>
                                <p class="text-muted">Anggota ini belum memiliki spesialisasi. Tambahkan spesialisasi terlebih dahulu.</p>
                                <a href="{{ route('pelayanan.availability', $anggota->id_anggota) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Spesialisasi
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Add New Specializations -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-plus me-1"></i>
                        Tambah Spesialisasi Baru (Opsional)
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Tambahkan spesialisasi baru untuk anggota ini:</p>
                        
                        <div id="new-specializations-container">
                            <!-- Dynamic specializations will be added here -->
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary" id="add-specialization">
                            <i class="fas fa-plus"></i> Tambah Spesialisasi
                        </button>
                    </div>
                </div>
                
                <!-- Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <i class="fas fa-list-check me-1"></i>
                        Summary Assignment
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Akan menjadi Posisi Reguler:</h6>
                                <div id="regular-summary" class="text-muted">
                                    Belum ada posisi yang dipilih
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Rekomendasi:</h6>
                                <div id="recommendation" class="small">
                                    <div class="alert alert-info py-2 mb-2">
                                        <i class="fas fa-info-circle"></i> Pilih 1-3 posisi yang paling sesuai dengan kemampuan anggota
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save"></i> Simpan Assignment
                        </button>
                        <a href="{{ route('pelayanan.member-profile', $anggota->id_anggota) }}" class="btn btn-secondary btn-lg ms-3">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Position Selection -->
<div class="modal fade" id="positionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Posisi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @foreach($positionCategories as $category => $positions)
                    <div class="mb-3">
                        <h6 class="text-primary">{{ $category }}</h6>
                        @foreach($positions as $position)
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 mb-1 position-btn" data-position="{{ $position }}">
                                {{ $position }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let specializationIndex = 0;
        
        // Update summary when regular positions change
        $('.regular-toggle').change(function() {
            updateRegularSummary();
        });
        
        // Add new specialization
        $('#add-specialization').click(function() {
            $('#positionModal').modal('show');
        });
        
        // Handle position selection from modal
        $('.position-btn').click(function() {
            const position = $(this).data('position');
            addNewSpecialization(position);
            $('#positionModal').modal('hide');
        });
        
        // Remove specialization
        $(document).on('click', '.remove-specialization', function() {
            $(this).closest('.new-specialization-item').remove();
            updateRegularSummary();
        });
        
        // Update summary when new specialization regular status changes
        $(document).on('change', '.new-regular-toggle', function() {
            updateRegularSummary();
        });
        
        function addNewSpecialization(position) {
            // Check if position already exists
            const existingPositions = [];
            $('.regular-toggle').each(function() {
                existingPositions.push($(this).val());
            });
            $('.new-position-name').each(function() {
                existingPositions.push($(this).text());
            });
            
            if (existingPositions.includes(position)) {
                alert('Posisi ' + position + ' sudah ada dalam daftar spesialisasi.');
                return;
            }
            
            const newSpec = `
                <div class="row mb-3 new-specialization-item border rounded p-3 bg-light">
                    <div class="col-md-6">
                        <h6 class="new-position-name">${position}</h6>
                        <input type="hidden" name="new_specializations[${specializationIndex}][posisi]" value="${position}">
                        <div class="form-group">
                            <label class="form-label">Prioritas (1-10):</label>
                            <input type="range" class="form-range" name="new_specializations[${specializationIndex}][prioritas]" min="1" max="10" value="5" oninput="this.nextElementSibling.textContent = this.value">
                            <div class="text-center"><span class="badge bg-secondary">5</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input new-regular-toggle" type="checkbox" name="regular_positions[]" value="${position}" id="new_regular_${specializationIndex}">
                            <label class="form-check-label" for="new_regular_${specializationIndex}">
                                <strong>Jadikan Reguler</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-start">
                        <button type="button" class="btn btn-danger btn-sm remove-specialization">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#new-specializations-container').append(newSpec);
            specializationIndex++;
            updateRegularSummary();
        }
        
        function updateRegularSummary() {
            const regularPositions = [];
            
            // From existing specializations
            $('.regular-toggle:checked').each(function() {
                regularPositions.push($(this).val());
            });
            
            // From new specializations
            $('.new-regular-toggle:checked').each(function() {
                regularPositions.push($(this).val());
            });
            
            if (regularPositions.length === 0) {
                $('#regular-summary').html('<span class="text-muted">Belum ada posisi yang dipilih</span>');
                $('#recommendation').html(`
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Minimal pilih 1 posisi reguler
                    </div>
                `);
            } else {
                const positionBadges = regularPositions.map(pos => 
                    `<span class="badge bg-success me-1 mb-1">${pos}</span>`
                ).join('');
                
                $('#regular-summary').html(positionBadges);
                
                let recommendation = '';
                if (regularPositions.length === 1) {
                    recommendation = `
                        <div class="alert alert-success py-2 mb-0">
                            <i class="fas fa-check-circle"></i> Bagus! Fokus pada 1 posisi reguler
                        </div>
                    `;
                } else if (regularPositions.length <= 3) {
                    recommendation = `
                        <div class="alert alert-success py-2 mb-0">
                            <i class="fas fa-check-circle"></i> Ideal! ${regularPositions.length} posisi reguler
                        </div>
                    `;
                } else {
                    recommendation = `
                        <div class="alert alert-warning py-2 mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Terlalu banyak! Pertimbangkan untuk mengurangi
                        </div>
                    `;
                }
                
                $('#recommendation').html(recommendation);
            }
        }
        
        // Initialize summary
        updateRegularSummary();
        
        // Form validation
        $('form').submit(function(e) {
            const regularCount = $('.regular-toggle:checked, .new-regular-toggle:checked').length;
            
            if (regularCount === 0) {
                e.preventDefault();
                alert('Harap pilih minimal satu posisi reguler.');
                return false;
            }
            
            if (regularCount > 5) {
                if (!confirm('Anda memilih ' + regularCount + ' posisi reguler. Ini mungkin terlalu banyak. Yakin ingin melanjutkan?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
        
        // Visual feedback for regular toggle
        $('.regular-toggle').change(function() {
            const card = $(this).closest('.card');
            if ($(this).is(':checked')) {
                card.removeClass('border-light').addClass('border-success');
            } else {
                card.removeClass('border-success').addClass('border-light');
            }
        });
        
        // Auto-check regular when adding high priority specialization
        $(document).on('change', 'input[type="range"]', function() {
            const value = parseInt($(this).val());
            const badge = $(this).next().find('span');
            badge.text(value);
            
            // Auto-suggest regular for high priority (8-10)
            if (value >= 8) {
                badge.removeClass('bg-secondary').addClass('bg-success');
                const regularToggle = $(this).closest('.new-specialization-item').find('.new-regular-toggle');
                if (!regularToggle.is(':checked')) {
                    regularToggle.prop('checked', true);
                    updateRegularSummary();
                }
            } else {
                badge.removeClass('bg-success').addClass('bg-secondary');
            }
        });
    });
</script>

<style>
    .position-btn:hover {
        background-color: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
    }
    
    .new-specialization-item {
        transition: all 0.3s ease;
    }
    
    .new-specialization-item:hover {
        background-color: #f8f9fa !important;
        border-color: var(--bs-primary) !important;
    }
    
    .form-check-input:checked {
        background-color: var(--bs-success);
        border-color: var(--bs-success);
    }
    
    .border-success {
        border-color: var(--bs-success) !important;
        background-color: rgba(25, 135, 84, 0.05);
    }
    
    .form-range::-webkit-slider-thumb {
        background: var(--bs-primary);
    }
    
    .form-range::-moz-range-thumb {
        background: var(--bs-primary);
        border: none;
    }
</style>
@endsection