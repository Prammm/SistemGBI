@extends('layouts.app')

@section('title', 'Pengaturan Ketersediaan & Spesialisasi')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Pengaturan Ketersediaan & Spesialisasi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item active">Ketersediaan {{ $anggota->nama }}</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="row">
        <!-- Profile Summary -->
        <div class="col-xl-4">
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user-circle me-1"></i>
                    Profile Pelayan
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-4x text-muted"></i>
                    </div>
                    <h5>{{ $anggota->nama }}</h5>
                    <p class="text-muted">{{ $anggota->email ?: 'Email tidak tersedia' }}</p>
                    
                    @php
                        $totalServices = $anggota->jadwalPelayanan()->count();
                        $regularPositions = $anggota->spesialisasi()->where('is_reguler', true)->count();
                        $totalPositions = $anggota->spesialisasi()->count();
                    @endphp
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h6 class="text-primary mb-0">{{ $totalServices }}</h6>
                                <small class="text-muted">Total Pelayanan</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h6 class="text-success mb-0">{{ $regularPositions }}</h6>
                                <small class="text-muted">Posisi Reguler</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h6 class="text-info mb-0">{{ $totalPositions }}</h6>
                                <small class="text-muted">Total Posisi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Main Form -->
        <div class="col-xl-8">
            <form action="{{ route('pelayanan.save-availability') }}" method="POST" id="availability-form">
                @csrf
                <input type="hidden" name="id_anggota" value="{{ $anggota->id_anggota }}">
                
                <!-- Day Availability -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-calendar-days me-1"></i>
                        Ketersediaan Hari
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Pilih hari-hari dimana Anda tersedia untuk melayani:</p>
                        
                        <div class="row">
                            @php
                                $days = [
                                    0 => ['name' => 'Minggu', 'color' => 'danger'],
                                    1 => ['name' => 'Senin', 'color' => 'primary'],
                                    2 => ['name' => 'Selasa', 'color' => 'primary'],
                                    3 => ['name' => 'Rabu', 'color' => 'primary'],
                                    4 => ['name' => 'Kamis', 'color' => 'primary'],
                                    5 => ['name' => 'Jumat', 'color' => 'primary'],
                                    6 => ['name' => 'Sabtu', 'color' => 'warning']
                                ];
                            @endphp
                            
                            @foreach($days as $value => $day)
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input day-checkbox" type="checkbox" 
                                            id="day_{{ $value }}" 
                                            name="ketersediaan_hari[]" 
                                            value="{{ $value }}" 
                                            {{ in_array($value, $anggota->ketersediaan_hari ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="day_{{ $value }}">
                                            <div class="card border-{{ $day['color'] }} h-100">
                                                <div class="card-body text-center p-2">
                                                    <i class="fas fa-calendar-day text-{{ $day['color'] }} fa-2x mb-2"></i>
                                                    <h6 class="mb-0">{{ $day['name'] }}</h6>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="select-all-days">
                                <i class="fas fa-check-double"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="select-weekdays">
                                <i class="fas fa-business-time"></i> Hari Kerja
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="select-weekend">
                                <i class="fas fa-calendar-week"></i> Weekend
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Time Availability -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-clock me-1"></i>
                        Ketersediaan Jam
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Tetapkan rentang waktu dimana Anda tersedia:</p>
                        
                        <div id="time-slots-container">
                            @if(!empty($anggota->ketersediaan_jam))
                                @foreach($anggota->ketersediaan_jam as $index => $timeSlot)
                                    @php
                                        list($start, $end) = explode('-', $timeSlot);
                                    @endphp
                                    <div class="row mb-3 time-slot border rounded p-3 bg-light">
                                        <div class="col-md-4">
                                            <label class="form-label">Jam Mulai</label>
                                            <input type="time" class="form-control time-start" name="ketersediaan_jam_start[]" value="{{ $start }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Jam Selesai</label>
                                            <input type="time" class="form-control time-end" name="ketersediaan_jam_end[]" value="{{ $end }}" required>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-time-slot me-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <span class="text-muted time-duration"></span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row mb-3 time-slot border rounded p-3 bg-light">
                                    <div class="col-md-4">
                                        <label class="form-label">Jam Mulai</label>
                                        <input type="time" class="form-control time-start" name="ketersediaan_jam_start[]" value="08:00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Jam Selesai</label>
                                        <input type="time" class="form-control time-end" name="ketersediaan_jam_end[]" value="22:00" required>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-time-slot me-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <span class="text-muted time-duration"></span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-success" id="add-time-slot">
                                <i class="fas fa-plus"></i> Tambah Slot Waktu
                            </button>
                            <button type="button" class="btn btn-secondary" id="add-preset-morning">
                                <i class="fas fa-sun"></i> Preset Pagi (06:00-09:00)
                            </button>
                            <button type="button" class="btn btn-secondary" id="add-preset-evening">
                                <i class="fas fa-moon"></i> Preset Malam (17:00-21:00)
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Blackout Dates -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-ban me-1"></i>
                        Tanggal Tidak Tersedia (Blackout Dates)
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Tetapkan tanggal-tanggal spesifik dimana Anda tidak tersedia:</p>
                        
                        <div id="blackout-dates-container">
                            @if(!empty($anggota->blackout_dates))
                                @foreach($anggota->blackout_dates as $index => $date)
                                    <div class="row mb-2 blackout-date-item">
                                        <div class="col-md-8">
                                            <input type="date" class="form-control" name="blackout_dates[]" value="{{ $date }}">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-danger btn-sm remove-blackout-date">
                                                <i class="fas fa-times"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        
                        <button type="button" class="btn btn-warning" id="add-blackout-date">
                            <i class="fas fa-plus"></i> Tambah Tanggal
                        </button>
                    </div>
                </div>
                
                <!-- Specializations -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-star me-1"></i>
                        Spesialisasi & Posisi
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Tetapkan posisi-posisi yang bisa Anda layani dan prioritasnya:</p>
                        
                        <div id="specializations-container">
                            @if($anggota->spesialisasi->isNotEmpty())
                                @foreach($anggota->spesialisasi as $spec)
                                    <div class="row mb-3 specialization-item border rounded p-3 bg-light">
                                        <div class="col-md-4">
                                            <label class="form-label">Posisi</label>
                                            <select class="form-select" name="spesialisasi[{{ $loop->index }}][posisi]" required>
                                                <option value="">-- Pilih Posisi --</option>
                                                @foreach($positionCategories as $category => $positions)
                                                    <optgroup label="{{ $category }}">
                                                        @foreach($positions as $position)
                                                            <option value="{{ $position }}" {{ $spec->posisi === $position ? 'selected' : '' }}>
                                                                {{ $position }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Prioritas (1-10)</label>
                                            <input type="range" class="form-range" name="spesialisasi[{{ $loop->index }}][prioritas]" min="1" max="10" value="{{ $spec->prioritas }}" oninput="this.nextElementSibling.textContent = this.value">
                                            <div class="text-center"><span class="badge bg-secondary">{{ $spec->prioritas }}</span></div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="spesialisasi[{{ $loop->index }}][is_reguler]" value="1" {{ $spec->is_reguler ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    <span class="regular-label">{{ $spec->is_reguler ? 'Reguler' : 'Tidak Reguler' }}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-specialization">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        
                        <button type="button" class="btn btn-primary" id="add-specialization">
                            <i class="fas fa-plus"></i> Tambah Spesialisasi
                        </button>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-sticky-note me-1"></i>
                        Catatan Khusus
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="catatan_khusus" rows="3" placeholder="Catatan tambahan tentang ketersediaan atau preferensi pelayanan...">{{ $anggota->catatan_khusus }}</textarea>
                    </div>
                </div>
                
                <!-- Submit -->
                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                        <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary btn-lg ms-3">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let specializationIndex = {{ $anggota->spesialisasi->count() }};
        
        // Day selection handlers
        $('#select-all-days').click(function() {
            $('.day-checkbox').prop('checked', true);
        });
        
        $('#select-weekdays').click(function() {
            $('.day-checkbox').prop('checked', false);
            $('#day_1, #day_2, #day_3, #day_4, #day_5').prop('checked', true);
        });
        
        $('#select-weekend').click(function() {
            $('.day-checkbox').prop('checked', false);
            $('#day_0, #day_6').prop('checked', true);
        });
        
        // Time slot management
        $('#add-time-slot').click(function() {
            addTimeSlot();
        });
        
        $('#add-preset-morning').click(function() {
            addTimeSlot('06:00', '09:00');
        });
        
        $('#add-preset-evening').click(function() {
            addTimeSlot('17:00', '21:00');
        });
        
        $(document).on('click', '.remove-time-slot', function() {
            if ($('.time-slot').length > 1) {
                $(this).closest('.time-slot').remove();
            } else {
                alert('Minimal harus ada satu slot waktu.');
            }
        });
        
        // Calculate and display time duration
        $(document).on('change', '.time-start, .time-end', function() {
            updateTimeDuration($(this).closest('.time-slot'));
        });
        
        // Blackout dates management
        $('#add-blackout-date').click(function() {
            const newDate = `
                <div class="row mb-2 blackout-date-item">
                    <div class="col-md-8">
                        <input type="date" class="form-control" name="blackout_dates[]" min="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-danger btn-sm remove-blackout-date">
                            <i class="fas fa-times"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            $('#blackout-dates-container').append(newDate);
        });
        
        $(document).on('click', '.remove-blackout-date', function() {
            $(this).closest('.blackout-date-item').remove();
        });
        
        // Specialization management
        $('#add-specialization').click(function() {
            addSpecialization();
        });
        
        $(document).on('click', '.remove-specialization', function() {
            $(this).closest('.specialization-item').remove();
        });
        
        // Regular toggle handler
        $(document).on('change', '.form-check-input[name*="is_reguler"]', function() {
            const label = $(this).closest('.form-check').find('.regular-label');
            label.text($(this).is(':checked') ? 'Reguler' : 'Tidak Reguler');
        });
        
        // Quick actions
        $('#copy-from-template').click(function() {
            // Show modal to select template or other member
            showCopyModal();
        });
        
        $('#set-full-availability').click(function() {
            $('.day-checkbox').prop('checked', true);
            $('#time-slots-container').empty();
            addTimeSlot('06:00', '22:00');
        });
        
        $('#set-weekend-only').click(function() {
            $('.day-checkbox').prop('checked', false);
            $('#day_0, #day_6').prop('checked', true);
            $('#time-slots-container').empty();
            addTimeSlot('06:00', '09:00');
            addTimeSlot('17:00', '21:00');
        });
        
        $('#clear-all').click(function() {
            if (confirm('Yakin ingin menghapus semua pengaturan?')) {
                $('.day-checkbox').prop('checked', false);
                $('#time-slots-container').empty();
                addTimeSlot('08:00', '22:00');
                $('#blackout-dates-container').empty();
                $('#specializations-container').empty();
                $('textarea[name="catatan_khusus"]').val('');
            }
        });
        
        // Form validation
        $('#availability-form').submit(function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Format time slots
            formatTimeSlots();
        });
        
        // Initialize
        updateAllTimeDurations();
        
        function addTimeSlot(start = '', end = '') {
            const timeSlot = `
                <div class="row mb-3 time-slot border rounded p-3 bg-light">
                    <div class="col-md-4">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control time-start" name="ketersediaan_jam_start[]" value="${start}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control time-end" name="ketersediaan_jam_end[]" value="${end}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-time-slot me-2">
                            <i class="fas fa-trash"></i>
                        </button>
                        <span class="text-muted time-duration"></span>
                    </div>
                </div>
            `;
            
            $('#time-slots-container').append(timeSlot);
            
            if (start && end) {
                updateTimeDuration($('#time-slots-container .time-slot:last'));
            }
        }
        
        function addSpecialization() {
            const specialization = `
                <div class="row mb-3 specialization-item border rounded p-3 bg-light">
                    <div class="col-md-4">
                        <label class="form-label">Posisi</label>
                        <select class="form-select" name="spesialisasi[${specializationIndex}][posisi]" required>
                            <option value="">-- Pilih Posisi --</option>
                            @foreach($positionCategories as $category => $positions)
                                <optgroup label="{{ $category }}">
                                    @foreach($positions as $position)
                                        <option value="{{ $position }}">{{ $position }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prioritas (1-10)</label>
                        <input type="range" class="form-range" name="spesialisasi[${specializationIndex}][prioritas]" min="1" max="10" value="5" oninput="this.nextElementSibling.querySelector('span').textContent = this.value">
                        <div class="text-center"><span class="badge bg-secondary">5</span></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="spesialisasi[${specializationIndex}][is_reguler]" value="1">
                            <label class="form-check-label">
                                <span class="regular-label">Tidak Reguler</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-specialization">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#specializations-container').append(specialization);
            specializationIndex++;
        }
        
        function updateTimeDuration(timeSlotElement) {
            const startTime = timeSlotElement.find('.time-start').val();
            const endTime = timeSlotElement.find('.time-end').val();
            
            if (startTime && endTime) {
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);
                
                if (end > start) {
                    const diffMs = end - start;
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                    
                    let duration = '';
                    if (diffHours > 0) duration += `${diffHours}j `;
                    if (diffMinutes > 0) duration += `${diffMinutes}m`;
                    
                    timeSlotElement.find('.time-duration').text(`(${duration})`).removeClass('text-danger').addClass('text-muted');
                } else {
                    timeSlotElement.find('.time-duration').text('(Invalid)').removeClass('text-muted').addClass('text-danger');
                }
            } else {
                timeSlotElement.find('.time-duration').text('');
            }
        }
        
        function updateAllTimeDurations() {
            $('.time-slot').each(function() {
                updateTimeDuration($(this));
            });
        }
        
        function validateForm() {
            // Check if at least one day is selected
            if ($('.day-checkbox:checked').length === 0) {
                alert('Harap pilih minimal satu hari ketersediaan.');
                return false;
            }
            
            // Validate time slots
            let isValidTimeSlots = true;
            $('.time-slot').each(function() {
                const start = $(this).find('.time-start').val();
                const end = $(this).find('.time-end').val();
                
                if (!start || !end) {
                    alert('Harap lengkapi semua slot waktu.');
                    isValidTimeSlots = false;
                    return false;
                }
                
                if (start >= end) {
                    alert('Jam selesai harus lebih besar dari jam mulai.');
                    isValidTimeSlots = false;
                    return false;
                }
            });
            
            if (!isValidTimeSlots) return false;
            
            // Check for overlapping time slots
            const timeSlots = [];
            $('.time-slot').each(function() {
                const start = $(this).find('.time-start').val();
                const end = $(this).find('.time-end').val();
                timeSlots.push({start, end});
            });
            
            for (let i = 0; i < timeSlots.length; i++) {
                for (let j = i + 1; j < timeSlots.length; j++) {
                    if (timeSlotsOverlap(timeSlots[i], timeSlots[j])) {
                        alert('Terdapat slot waktu yang bertumpang tindih.');
                        return false;
                    }
                }
            }
            
            // Validate specializations
            const selectedPositions = [];
            $('.specialization-item select').each(function() {
                const position = $(this).val();
                if (position) {
                    if (selectedPositions.includes(position)) {
                        alert(`Posisi "${position}" sudah dipilih sebelumnya.`);
                        isValidTimeSlots = false;
                        return false;
                    }
                    selectedPositions.push(position);
                }
            });
            
            return isValidTimeSlots;
        }
        
        function timeSlotsOverlap(slot1, slot2) {
            const start1 = new Date(`2000-01-01T${slot1.start}`);
            const end1 = new Date(`2000-01-01T${slot1.end}`);
            const start2 = new Date(`2000-01-01T${slot2.start}`);
            const end2 = new Date(`2000-01-01T${slot2.end}`);
            
            return start1 < end2 && start2 < end1;
        }
        
        function formatTimeSlots() {
            // Remove existing hidden time slot inputs
            $('input[name="ketersediaan_jam[]"]').remove();
            
            // Create formatted time slots
            $('.time-slot').each(function() {
                const start = $(this).find('.time-start').val();
                const end = $(this).find('.time-end').val();
                
                if (start && end) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'ketersediaan_jam[]',
                        value: `${start}-${end}`
                    }).appendTo('#availability-form');
                }
            });
        }
        
        function showCopyModal() {
            // This would show a modal to copy settings from templates or other members
            // For now, just show an alert
            alert('Fitur copy dari template akan segera tersedia!');
        }
    });
</script>

<style>
    .form-check-card .card {
        cursor: pointer;
        transition: all 0.3s ease;
        border-width: 2px;
    }
    
    .form-check-card input:checked + label .card {
        border-color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .form-check-card input {
        display: none;
    }
    
    .time-slot {
        transition: all 0.3s ease;
    }
    
    .time-slot:hover {
        background-color: #f8f9fa !important;
        border-color: var(--bs-primary) !important;
    }
    
    .specialization-item {
        transition: all 0.3s ease;
    }
    
    .specialization-item:hover {
        background-color: #f8f9fa !important;
        border-color: var(--bs-success) !important;
    }
    
    .blackout-date-item {
        transition: all 0.3s ease;
    }
    
    .blackout-date-item:hover {
        background-color: #fff3cd;
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