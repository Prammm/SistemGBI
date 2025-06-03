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
                            </div>
                            <div class="card-body">
                                <div id="petugas-container">
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
                                                                
                                                                // Check if this person is regular for this position
                                                                $isReguler = $a->spesialisasi->contains('posisi', $posisi) && 
                                                                           $a->spesialisasi->where('posisi', $posisi)->where('is_reguler', true)->isNotEmpty();
                                                                
                                                                // Check if already scheduled in this pelaksanaan
                                                                $alreadyScheduled = false;
                                                                if (isset($existingJadwal) && $existingJadwal->isNotEmpty()) {
                                                                    $alreadyScheduled = $existingJadwal->where('posisi', $posisi)->where('id_anggota', $a->id_anggota)->isNotEmpty();
                                                                }
                                                            @endphp
                                                            
                                                            <option 
                                                                value="{{ $a->id_anggota }}" 
                                                                {{ $alreadyScheduled ? 'selected' : '' }}
                                                                {{ !$isAvailable ? 'disabled' : '' }}
                                                                data-reguler="{{ $isReguler ? 'true' : 'false' }}"
                                                                data-available="{{ $isAvailable ? 'true' : 'false' }}"
                                                            >
                                                                {{ $a->nama }}
                                                                @if($isReguler) (Regular) @endif
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
                                    @endforeach
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="button" id="add-petugas" class="btn btn-success">
                                            <i class="fas fa-plus"></i> Tambah Posisi Lain
                                        </button>
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
            
            // Add reguler badge if applicable
            if (isReguler) {
                $result.append(' <span class="badge bg-success ms-1">Regular</span>');
            }
            
            // Add availability indicator
            if (!isAvailable) {
                $result.append(' <span class="badge bg-danger ms-1">Tidak Tersedia</span>');
            }
            
            return $result;
        }
        
        // Add new position field
        let positionCounter = {{ count($posisiOptions) }};
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
                    
                    if (!isAvailable) {
                        $result.append(' <span class="badge bg-danger ms-1">Tidak Tersedia</span>');
                    }
                    
                    return $result;
                }
            }).removeClass('anggota-select-new');
            
            positionCounter++;
        });
        
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
            
            if (!isValid) {
                e.preventDefault();
                alert('Terdapat kesalahan:\n' + errorMessages.join('\n'));
                return false;
            }
            
            return true;
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