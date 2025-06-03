@extends('layouts.app')

@section('title', 'Generator Jadwal Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Generator Jadwal Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item active">Generator</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-magic me-1"></i>
                    Generator Jadwal Pelayanan Otomatis
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
                                                    @php
                                                        $tanggal = \Carbon\Carbon::parse($p->tanggal_kegiatan);
                                                        $dayName = $tanggal->locale('id')->dayName;
                                                        $jamMulai = \Carbon\Carbon::parse($p->jam_mulai)->format('H:i');
                                                        $jamSelesai = \Carbon\Carbon::parse($p->jam_selesai)->format('H:i');
                                                    @endphp
                                                    <option value="{{ $p->id_pelaksanaan }}" 
                                                            data-day="{{ $tanggal->dayOfWeek }}"
                                                            data-start="{{ $jamMulai }}"
                                                            data-end="{{ $jamSelesai }}"
                                                            data-date="{{ $tanggal->format('Y-m-d') }}">
                                                        {{ $p->kegiatan->nama_kegiatan }} - 
                                                        {{ $dayName }}, {{ $tanggal->format('d/m/Y') }} 
                                                        ({{ $jamMulai }} - {{ $jamSelesai }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                Pilih satu atau lebih kegiatan. Informasi hari dan jam akan digunakan untuk mencocokkan ketersediaan anggota.
                                            </small>
                                        </div>
                                        
                                        <!-- Event Details Display -->
                                        <div id="selected-events-info" class="mt-3" style="display: none;">
                                            <h6>Detail Kegiatan Terpilih:</h6>
                                            <div id="events-details"></div>
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
                        
                        <!-- Algorithm Selection - SIMPLIFIED -->
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
                                                    <input class="form-check-input" type="radio" name="algorithm" id="fair_rotation" value="fair_rotation" checked>
                                                    <label class="form-check-label" for="fair_rotation">
                                                        <strong>Fair Rotation (Recommended)</strong><br>
                                                        <small class="text-muted">Rotasi adil berdasarkan frekuensi pelayanan terakhir dan workload. Memberikan kesempatan yang sama kepada semua anggota.</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="algorithm" id="regular_priority" value="regular_priority">
                                                    <label class="form-check-label" for="regular_priority">
                                                        <strong>Regular Priority</strong><br>
                                                        <small class="text-muted">Mengutamakan pemain reguler untuk konsistensi pelayanan. Cocok untuk acara penting.</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="avoid_consecutive" name="avoid_consecutive" value="1" checked>
                                                    <label class="form-check-label" for="avoid_consecutive">
                                                        Hindari jadwal berturut-turut (dalam 1 minggu) untuk anggota yang sama
                                                    </label>
                                                </div>
                                            </div>
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
                        
                        <!-- Member Selection with Availability Info -->
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
                                        
                                        <!-- Availability Filter -->
                                        <div class="mb-3">
                                            <label class="form-label">Filter berdasarkan ketersediaan:</label>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-primary btn-sm" id="filter-all-anggota">Semua</button>
                                                <button type="button" class="btn btn-outline-success btn-sm" id="filter-available-anggota">Tersedia untuk Event Terpilih</button>
                                                <button type="button" class="btn btn-outline-warning btn-sm" id="filter-weekend-anggota">Weekend Only</button>
                                            </div>
                                        </div>
                                        
                                        <div class="row" id="anggota-list">
                                            @foreach($anggota->chunk(ceil($anggota->count() / 3)) as $chunk)
                                                <div class="col-md-4">
                                                    @foreach($chunk as $a)
                                                        <div class="form-check mb-2 anggota-item" data-name="{{ strtolower($a->nama) }}" data-id="{{ $a->id_anggota }}">
                                                            <input class="form-check-input anggota-checkbox" type="checkbox" 
                                                                id="anggota_{{ $a->id_anggota }}" 
                                                                name="anggota[]" 
                                                                value="{{ $a->id_anggota }}" 
                                                                checked>
                                                            <label class="form-check-label" for="anggota_{{ $a->id_anggota }}">
                                                                <strong>{{ $a->nama }}</strong>
                                                                
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
                                                                    <br><small class="text-info availability-info" data-days="{{ json_encode($a->ketersediaan_hari) }}" data-times="{{ json_encode($a->ketersediaan_jam) }}">
                                                                        <i class="fas fa-calendar"></i> {{ $availability }}
                                                                        @if(!empty($a->ketersediaan_jam))
                                                                            ({{ count($a->ketersediaan_jam) }} slot waktu)
                                                                        @endif
                                                                    </small>
                                                                @else
                                                                    <br><small class="text-warning">
                                                                        <i class="fas fa-exclamation-triangle"></i> Ketersediaan belum diatur
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
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6>Events Dipilih</h6>
                                                    <span class="badge bg-primary fs-6" id="events-count">0</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6>Posisi Dipilih</h6>
                                                    <span class="badge bg-success fs-6" id="positions-count">0</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6>Anggota Dipilih</h6>
                                                    <span class="badge bg-info fs-6" id="anggota-count">0</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6>Estimasi Kebutuhan</h6>
                                                    <span class="badge bg-warning fs-6" id="requirement-ratio">0:0</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 text-center">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-magic"></i> Generate Jadwal
                                            </button>
                                            <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary btn-lg ms-2">Batal</a>
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
        
        // Toggle configuration based on generation type
        $('input[name="generation_type"]').change(function() {
            if ($(this).val() === 'single') {
                $('#single-config').show();
                $('#monthly-config').hide();
            } else {
                $('#single-config').hide();
                $('#monthly-config').show();
            }
            updateCounters();
        });
        
        // Show selected event details
        $('#pelaksanaan-select').change(function() {
            const selectedOptions = $(this).find('option:selected');
            if (selectedOptions.length > 0) {
                $('#selected-events-info').show();
                let detailsHtml = '<div class="row">';
                
                selectedOptions.each(function() {
                    const option = $(this);
                    const day = option.data('day');
                    const start = option.data('start');
                    const end = option.data('end');
                    const date = option.data('date');
                    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    
                    detailsHtml += `
                        <div class="col-md-6 mb-2">
                            <div class="alert alert-info py-2">
                                <strong>${option.text()}</strong><br>
                                <small>Hari: ${dayNames[day]} (${day}) | Jam: ${start} - ${end}</small>
                            </div>
                        </div>
                    `;
                });
                
                detailsHtml += '</div>';
                $('#events-details').html(detailsHtml);
                
                // Update availability filtering
                updateAvailabilityFiltering();
            } else {
                $('#selected-events-info').hide();
            }
            updateCounters();
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
            $('.anggota-checkbox:visible').prop('checked', true);
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
        
        // Availability filtering
        $('#filter-all-anggota').click(function() {
            $('.anggota-item').show();
            $(this).addClass('active').siblings().removeClass('active');
        });
        
        $('#filter-available-anggota').click(function() {
            filterByAvailability();
            $(this).addClass('active').siblings().removeClass('active');
        });
        
        $('#filter-weekend-anggota').click(function() {
            $('.anggota-item').hide();
            $('.anggota-item').each(function() {
                const availabilityInfo = $(this).find('.availability-info');
                if (availabilityInfo.length > 0) {
                    const days = JSON.parse(availabilityInfo.data('days') || '[]');
                    if (days.includes(0) || days.includes(6)) { // Sunday or Saturday
                        $(this).show();
                    }
                } else {
                    $(this).show(); // Show if no availability set (assume available)
                }
            });
            $(this).addClass('active').siblings().removeClass('active');
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
        
        // Initialize counters
        updateCounters();
        
        function updateCounters() {
            const eventsCount = $('#pelaksanaan-select').val() ? $('#pelaksanaan-select').val().length : 0;
            const positionsCount = $('.position-checkbox:checked').length;
            const anggotaCount = $('.anggota-checkbox:checked').length;
            
            $('#events-count').text(eventsCount);
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
        
        function updateAvailabilityFiltering() {
            // This function will be called when events are selected
            // to highlight members who are available for the selected events
        }
        
        function filterByAvailability() {
            const selectedEvents = $('#pelaksanaan-select').find('option:selected');
            
            if (selectedEvents.length === 0) {
                alert('Pilih kegiatan terlebih dahulu untuk filter ketersediaan');
                return;
            }
            
            $('.anggota-item').each(function() {
                const anggotaItem = $(this);
                const availabilityInfo = anggotaItem.find('.availability-info');
                let isAvailable = true;
                
                if (availabilityInfo.length > 0) {
                    const availableDays = JSON.parse(availabilityInfo.data('days') || '[]');
                    const availableTimes = JSON.parse(availabilityInfo.data('times') || '[]');
                    
                    // Check if member is available for ANY of the selected events
                    let availableForAnyEvent = false;
                    
                    selectedEvents.each(function() {
                        const eventDay = $(this).data('day');
                        const eventStart = $(this).data('start');
                        const eventEnd = $(this).data('end');
                        
                        // Check day availability
                        if (availableDays.includes(eventDay)) {
                            // Check time availability
                            if (availableTimes.length === 0) {
                                availableForAnyEvent = true;
                            } else {
                                for (let timeSlot of availableTimes) {
                                    const [slotStart, slotEnd] = timeSlot.split('-');
                                    if (eventStart >= slotStart && eventEnd <= slotEnd) {
                                        availableForAnyEvent = true;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if (availableForAnyEvent) return false; // Break out of loop
                    });
                    
                    isAvailable = availableForAnyEvent;
                } else {
                    // If no availability set, assume available
                    isAvailable = true;
                }
                
                if (isAvailable) {
                    anggotaItem.show();
                    anggotaItem.removeClass('text-muted');
                } else {
                    anggotaItem.hide();
                    anggotaItem.addClass('text-muted');
                }
            });
        }
    });
</script>
@endsection