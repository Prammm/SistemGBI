@extends('layouts.app')

@section('title', 'Tambah Jadwal Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Jadwal Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item active">Tambah Jadwal</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-plus me-1"></i>
                    Form Jadwal Pelayanan
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
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5>Informasi Kegiatan</h5>
                                <div><strong>Kegiatan:</strong> {{ $pelaksanaan->kegiatan->nama_kegiatan }}</div>
                                <div><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('d F Y') }}</div>
                                <div><strong>Waktu:</strong> {{ \Carbon\Carbon::parse($pelaksanaan->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pelaksanaan->jam_selesai)->format('H:i') }}</div>
                                <div><strong>Lokasi:</strong> {{ $pelaksanaan->lokasi ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('pelayanan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_pelaksanaan" value="{{ $pelaksanaan->id_pelaksanaan }}">
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Pilih Kegiatan Lain</label>
                                    <select class="form-select" id="change_pelaksanaan" onchange="if(this.value) window.location.href='{{ route('pelayanan.create') }}?id_pelaksanaan='+this.value">
                                        <option value="">-- Pilih Kegiatan --</option>
                                        @foreach($allPelaksanaan as $p)
                                            <option value="{{ $p->id_pelaksanaan }}" {{ $pelaksanaan->id_pelaksanaan == $p->id_pelaksanaan ? 'selected' : '' }}>
                                                {{ $p->kegiatan->nama_kegiatan }} - {{ \Carbon\Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Tim Pelayanan</h5>
                                
                                @if($existingJadwal->isNotEmpty())
                                    <small class="text-light">
                                        <i class="fas fa-info-circle"></i> Mode Edit: Menampilkan posisi yang sudah dijadwalkan
                                    </small>
                                @endif
                            </div>
                            <div class="card-body">
                                <div id="petugas-container">
                                    @if($existingJadwal->isNotEmpty())
                                        {{-- Load existing schedule data --}}
                                        @foreach($existingJadwal as $jadwal)
                                            <div class="row mb-3 petugas-row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Posisi</label>
                                                        <input type="text" class="form-control" name="petugas[{{ $loop->index }}][posisi]" value="{{ $jadwal->posisi }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <label class="form-label">Anggota</label>
                                                        <select class="form-select anggota-select" name="petugas[{{ $loop->index }}][id_anggota]" data-posisi="{{ $jadwal->posisi }}">
                                                            <option value="">-- Pilih Anggota --</option>
                                                            @foreach($anggota as $a)
                                                                @php
                                                                    // Calculate availability for this specific member
                                                                    $isAvailable = true;
                                                                    $eventDay = \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->dayOfWeek;
                                                                    $eventStart = $pelaksanaan->jam_mulai;
                                                                    $eventEnd = $pelaksanaan->jam_selesai;
                                                                    
                                                                    // Only check availability if restrictions are set
                                                                    if (!empty($a->ketersediaan_hari) || !empty($a->ketersediaan_jam)) {
                                                                        if (!empty($a->ketersediaan_hari)) {
                                                                            if (!in_array($eventDay, $a->ketersediaan_hari)) {
                                                                                $isAvailable = false;
                                                                            }
                                                                        }
                                                                        
                                                                        if ($isAvailable && !empty($a->ketersediaan_jam)) {
                                                                            $availableDuringEvent = false;
                                                                            foreach ($a->ketersediaan_jam as $timeSlot) {
                                                                                list($availStart, $availEnd) = explode('-', $timeSlot);
                                                                                if ($eventStart >= $availStart && $eventEnd <= $availEnd) {
                                                                                    $availableDuringEvent = true;
                                                                                    break;
                                                                                }
                                                                            }
                                                                            $isAvailable = $availableDuringEvent;
                                                                        }
                                                                    }
                                                                    
                                                                    // Check if regular for this position
                                                                    $isReguler = $a->spesialisasi->contains('posisi', $jadwal->posisi) && 
                                                                            $a->spesialisasi->where('posisi', $jadwal->posisi)->where('is_reguler', true)->isNotEmpty();
                                                                    
                                                                    // Check if can serve this position
                                                                    $canServePosition = $a->spesialisasi->contains('posisi', $jadwal->posisi);
                                                                @endphp
                                                                
                                                                <option 
                                                                    value="{{ $a->id_anggota }}" 
                                                                    {{ $jadwal->id_anggota == $a->id_anggota ? 'selected' : '' }}
                                                                    data-reguler="{{ $isReguler ? 'true' : 'false' }}"
                                                                    data-available="{{ $isAvailable ? 'true' : 'false' }}"
                                                                    data-can-serve="{{ $canServePosition ? 'true' : 'false' }}"
                                                                    {{ !$canServePosition ? 'style=color:#6c757d;' : '' }}
                                                                >
                                                                    {{ $a->nama }}
                                                                    @if($isReguler) (Regular) @endif
                                                                    @if(!$isAvailable) (Tidak Tersedia) @endif
                                                                    @if(!$canServePosition) (Tidak Bisa {{ $jadwal->posisi }}) @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm remove-petugas-row me-2">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        {{-- Create new schedule mode --}}
                                        @foreach($posisiOptions as $posisi)
                                            <div class="row mb-3 petugas-row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Posisi</label>
                                                        <input type="text" class="form-control" name="petugas[{{ $loop->index }}][posisi]" value="{{ $posisi }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <label class="form-label">Anggota</label>
                                                        <select class="form-select anggota-select" name="petugas[{{ $loop->index }}][id_anggota]" data-posisi="{{ $posisi }}">
                                                            <option value="">-- Pilih Anggota --</option>
                                                            @foreach($anggota as $a)
                                                                @php
                                                                    // Same availability calculation as above
                                                                    $isAvailable = true;
                                                                    $eventDay = \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->dayOfWeek;
                                                                    $eventStart = $pelaksanaan->jam_mulai;
                                                                    $eventEnd = $pelaksanaan->jam_selesai;
                                                                    
                                                                    if (!empty($a->ketersediaan_hari) || !empty($a->ketersediaan_jam)) {
                                                                        if (!empty($a->ketersediaan_hari)) {
                                                                            if (!in_array($eventDay, $a->ketersediaan_hari)) {
                                                                                $isAvailable = false;
                                                                            }
                                                                        }
                                                                        
                                                                        if ($isAvailable && !empty($a->ketersediaan_jam)) {
                                                                            $availableDuringEvent = false;
                                                                            foreach ($a->ketersediaan_jam as $timeSlot) {
                                                                                list($availStart, $availEnd) = explode('-', $timeSlot);
                                                                                if ($eventStart >= $availStart && $eventEnd <= $availEnd) {
                                                                                    $availableDuringEvent = true;
                                                                                    break;
                                                                                }
                                                                            }
                                                                            $isAvailable = $availableDuringEvent;
                                                                        }
                                                                    }
                                                                    
                                                                    $isReguler = $a->spesialisasi->contains('posisi', $posisi) && 
                                                                            $a->spesialisasi->where('posisi', $posisi)->where('is_reguler', true)->isNotEmpty();
                                                                    
                                                                    $canServePosition = $a->spesialisasi->contains('posisi', $posisi);
                                                                @endphp
                                                                
                                                                <option 
                                                                    value="{{ $a->id_anggota }}" 
                                                                    data-reguler="{{ $isReguler ? 'true' : 'false' }}"
                                                                    data-available="{{ $isAvailable ? 'true' : 'false' }}"
                                                                    data-can-serve="{{ $canServePosition ? 'true' : 'false' }}"
                                                                    {{ !$canServePosition ? 'style=color:#6c757d;' : '' }}
                                                                >
                                                                    {{ $a->nama }}
                                                                    @if($isReguler) (Regular) @endif
                                                                    @if(!$isAvailable) (Tidak Tersedia) @endif
                                                                    @if(!$canServePosition) (Tidak Bisa {{ $posisi }}) @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm remove-petugas-row me-2">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="button" id="add-petugas" class="btn btn-success">
                                            <i class="fas fa-plus"></i> Tambah Posisi Lain
                                        </button>
                                        
                                        @if($existingJadwal->isNotEmpty())
                                            <button type="button" id="add-more-positions" class="btn btn-info ms-2">
                                                <i class="fas fa-plus-circle"></i> Tambah Posisi dari Master
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Jadwal
                            </button>
                            <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Posisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="available-positions">
                    <p>Loading posisi yang tersedia...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for kegiatan selection with search
        $('#change_pelaksanaan').select2({
            placeholder: "Cari kegiatan...",
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Select2 for anggota selection
        $('.anggota-select').select2({
            placeholder: "Pilih Anggota",
            allowClear: true,
            templateResult: formatAnggotaSelection,
            templateSelection: formatAnggotaSelection,
            width: '100%'
        });

        // Remove petugas row
        $('#petugas-container').on('click', '.remove-petugas-row', function() {
            if ($('.petugas-row').length > 1) {
                $(this).closest('.petugas-row').remove();
            } else {
                alert('Minimal harus ada satu posisi dalam jadwal pelayanan.');
            }
        });
        
        // Function to format anggota selection with availability and last served info
        function formatAnggotaSelection(anggota) {
            if (!anggota.id) {
                return anggota.text;
            }
            
            const $element = $(anggota.element);
            const isReguler = $element.data('reguler') === 'true';
            const isAvailable = $element.data('available') === 'true';
            
            let $result = $('<span></span>');
            
            // Add name
            $result.append('<span>' + anggota.text + '</span>');
            
            
            return $result;
        }
        
        // Add new position field
        let positionCounter = {{ $existingJadwal->isNotEmpty() ? $existingJadwal->count() : count($posisiOptions) }};
        $('#add-petugas').click(function() {
            const newRow = `
                <div class="row mb-3 petugas-row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Posisi</label>
                            <input type="text" class="form-control" name="petugas[${positionCounter}][posisi]" placeholder="Masukkan nama posisi" required>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label class="form-label">Anggota</label>
                            <select class="form-select anggota-select-new" name="petugas[${positionCounter}][id_anggota]" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach($anggota as $a)
                                    @php
                                        // Default to available if no availability settings
                                        $isAvailable = true;
                                        $eventDay = \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->dayOfWeek;
                                        $eventStart = $pelaksanaan->jam_mulai;
                                        $eventEnd = $pelaksanaan->jam_selesai;
                                        
                                        // Only check availability if both day and time restrictions are set
                                        if (!empty($a->ketersediaan_hari) || !empty($a->ketersediaan_jam)) {
                                            // If day restrictions exist, check them
                                            if (!empty($a->ketersediaan_hari)) {
                                                if (!in_array($eventDay, $a->ketersediaan_hari)) {
                                                    $isAvailable = false;
                                                }
                                            }
                                            
                                            // If time restrictions exist and day is available, check time
                                            if ($isAvailable && !empty($a->ketersediaan_jam)) {
                                                $availableDuringEvent = false;
                                                foreach ($a->ketersediaan_jam as $timeSlot) {
                                                    list($availStart, $availEnd) = explode('-', $timeSlot);
                                                    if ($eventStart >= $availStart && $eventEnd <= $availEnd) {
                                                        $availableDuringEvent = true;
                                                        break;
                                                    }
                                                }
                                                $isAvailable = $availableDuringEvent;
                                            }
                                        }
                                    @endphp
                                    <option 
                                        value="{{ $a->id_anggota }}" 
                                        {{ !$isAvailable ? 'disabled' : '' }}
                                        data-available="{{ $isAvailable ? 'true' : 'false' }}"
                                    >
                                        {{ $a->nama }}
                                        @if(!$isAvailable) (Tidak Tersedia) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-petugas-row me-2">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            
            $('#petugas-container').append(newRow);
            
            // Initialize Select2 for the new row
            $('.anggota-select-new').select2({
                placeholder: "Pilih Anggota",
                allowClear: true,
                width: '100%',
                templateResult: function(anggota) {
                    if (!anggota.id) {
                        return anggota.text;
                    }
                    
                    const $element = $(anggota.element);
                    const isAvailable = $element.data('available') === 'true';
                    
                    let $result = $('<span></span>');
                    $result.append('<span>' + anggota.text + '</span>');
                
                    
                    return $result;
                }
            }).removeClass('anggota-select-new');
            
            positionCounter++;
        });
        

        $('#add-more-positions').click(function() {
            loadAvailablePositions();
            $('#addPositionModal').modal('show');
        });

        function loadAvailablePositions() {
            $.get('/api/master-posisi/positions')
                .done(function(response) {
                    if (response.success) {
                        let html = '<div class="row">';
                        
                        // Get currently assigned positions
                        const assignedPositions = [];
                        $('.petugas-row input[name*="[posisi]"]').each(function() {
                            assignedPositions.push($(this).val());
                        });
                        
                        Object.keys(response.data).forEach(category => {
                            html += `<div class="col-12 mb-3">`;
                            html += `<h6 class="text-primary">${category}</h6>`;
                            
                            response.data[category].forEach(position => {
                                const isAssigned = assignedPositions.includes(position);
                                const buttonClass = isAssigned ? 'btn-secondary' : 'btn-outline-primary';
                                const disabled = isAssigned ? 'disabled' : '';
                                
                                html += `
                                    <button type="button" class="btn ${buttonClass} btn-sm me-1 mb-1 position-btn" 
                                            data-position="${position}" ${disabled}>
                                        ${position} ${isAssigned ? '(Sudah ada)' : ''}
                                    </button>
                                `;
                            });
                            
                            html += `</div>`;
                        });
                        
                        html += '</div>';
                        $('#available-positions').html(html);
                        
                        // Add click handlers
                        $('.position-btn:not([disabled])').click(function() {
                            const position = $(this).data('position');
                            addPositionFromMaster(position);
                            $('#addPositionModal').modal('hide');
                        });
                    }
                })
                .fail(function() {
                    $('#available-positions').html('<div class="alert alert-danger">Gagal memuat posisi</div>');
                });
        }
        
        // Add position from master
        function addPositionFromMaster(position) {
            const newRow = `
                <div class="row mb-3 petugas-row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Posisi</label>
                            <input type="text" class="form-control" name="petugas[${positionCounter}][posisi]" value="${position}" readonly>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label class="form-label">Anggota</label>
                            <select class="form-select anggota-select-new" name="petugas[${positionCounter}][id_anggota]" data-posisi="${position}" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach($anggota as $a)
                                    @php
                                        $canServePosition = $a->spesialisasi->contains('posisi', '${position}');
                                        $isReguler = $canServePosition && $a->spesialisasi->where('posisi', '${position}')->where('is_reguler', true)->isNotEmpty();
                                    @endphp
                                    <option value="{{ $a->id_anggota }}" data-can-serve="{{ $canServePosition ? 'true' : 'false' }}">
                                        {{ $a->nama }}
                                        @if($isReguler) (Regular) @endif
                                        @if(!$canServePosition) (Tidak Bisa ${position}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-petugas-row me-2">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            
            $('#petugas-container').append(newRow);
            
            // Initialize Select2 for new row
            $('.anggota-select-new').select2({
                placeholder: "Pilih Anggota",
                allowClear: true,
                width: '100%'
            }).removeClass('anggota-select-new');
            
            positionCounter++;
        }


        // Validation before submit
        $('form').on('submit', function(e) {
            let isValid = true;
            let errorMessages = [];
            
            // Check if all positions have selected anggota
            $('.petugas-row').each(function() {
                const posisi = $(this).find('input[name*="[posisi]"]').val();
                const anggota = $(this).find('select[name*="[id_anggota]"]').val();
                
                if (posisi && !anggota) {
                    errorMessages.push(`Posisi "${posisi}" belum memiliki anggota yang ditugaskan.`);
                    isValid = false;
                }
            });
            
            // Check for duplicate anggota assignments
            const selectedAnggota = [];
            $('.petugas-row select[name*="[id_anggota]"]').each(function() {
                const value = $(this).val();
                if (value) {
                    if (selectedAnggota.includes(value)) {
                        const anggotaName = $(this).find('option:selected').text();
                        errorMessages.push(`Anggota "${anggotaName}" sudah ditugaskan di posisi lain.`);
                        isValid = false;
                    } else {
                        selectedAnggota.push(value);
                    }
                }
            });
            
            // Check for members serving positions they can't handle
            $('.petugas-row select[name*="[id_anggota]"]').each(function() {
                const selectedOption = $(this).find('option:selected');
                const canServe = selectedOption.data('can-serve');
                const anggotaName = selectedOption.text();
                const posisi = $(this).closest('.petugas-row').find('input[name*="[posisi]"]').val();
                
                if ($(this).val() && canServe === 'false') {
                    errorMessages.push(`${anggotaName} tidak memiliki spesialisasi untuk posisi "${posisi}".`);
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Terdapat kesalahan:\n' + errorMessages.join('\n'));
                return false;
            }
            
            return true;
        });

        $('.anggota-select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const canServe = selectedOption.data('can-serve');
            const isAvailable = selectedOption.data('available');
            const isReguler = selectedOption.data('reguler');
            
            $(this).removeClass('border-success border-warning border-danger');
            
            if ($(this).val()) {
                if (canServe === 'false') {
                    $(this).addClass('border-danger');
                } else if (isAvailable === 'false') {
                    $(this).addClass('border-warning');
                } else if (isReguler === 'true') {
                    $(this).addClass('border-success');
                }
            }
        });
        
        // Real-time validation feedback
        $('body').on('change', 'select[name*="[id_anggota]"]', function() {
            const $this = $(this);
            const selectedOption = $this.find('option:selected');
            const isAvailable = selectedOption.data('available') === 'true';
            
            if (!isAvailable && $this.val()) {
                if (confirm('Anggota yang dipilih tidak tersedia pada waktu kegiatan ini. Yakin ingin melanjutkan?')) {
                    // Continue
                } else {
                    $this.val('').trigger('change');
                }
            }
        });
    });
</script>

<style>
.petugas-row {
    border: 1px solid #e3e6f0;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: #f8f9fc;
}

.petugas-row:hover {
    background-color: #eaecf4;
    border-color: #5a5c69;
}

.remove-petugas-row {
    white-space: nowrap;
}

.select2-container--default .select2-results__option[aria-disabled="true"] {
    background-color: #f8f9fa;
    color: #6c757d;
}

.badge {
    font-size: 0.75em;
}
</style>
@endsection