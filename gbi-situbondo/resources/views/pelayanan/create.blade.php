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
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label">Posisi</label>
                                                    <input type="text" class="form-control" name="petugas[{{ $loop->index }}][posisi]" value="{{ $posisi }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Anggota</label>
                                                    <select class="form-select anggota-select" name="petugas[{{ $loop->index }}][id_anggota]" data-posisi="{{ $posisi }}">
                                                        <option value="">-- Pilih Anggota --</option>
                                                        @foreach($anggota as $a)
                                                            @php
                                                                $isAvailable = true;
                                                                $eventDay = \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->dayOfWeek;
                                                                $eventStart = $pelaksanaan->jam_mulai;
                                                                $eventEnd = $pelaksanaan->jam_selesai;
                                                                
                                                                // Check availability if set
                                                                if (!empty($a->ketersediaan_hari) && !empty($a->ketersediaan_jam)) {
                                                                    // Check day
                                                                    if (!in_array($eventDay, $a->ketersediaan_hari)) {
                                                                        $isAvailable = false;
                                                                    }
                                                                    
                                                                    // Check time
                                                                    if ($isAvailable) {
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
                                                                $isReguler = $a->jadwalPelayanan
                                                                    ->where('posisi', $posisi)
                                                                    ->where('is_reguler', true)
                                                                    ->count() > 0;
                                                                    
                                                                // Get the last time they served in this position
                                                                $lastServed = $a->jadwalPelayanan
                                                                    ->where('posisi', $posisi)
                                                                    ->sortByDesc('tanggal_pelayanan')
                                                                    ->first();
                                                                    
                                                                $lastServedInfo = '';
                                                                if ($lastServed) {
                                                                    $lastServedDate = \Carbon\Carbon::parse($lastServed->tanggal_pelayanan);
                                                                    $lastServedInfo = $lastServedDate->format('d/m/Y');
                                                                    $daysDiff = $lastServedDate->diffInDays(now());
                                                                    $lastServedInfo .= " ({$daysDiff} hari lalu)";
                                                                }
                                                                
                                                                // Check if already scheduled in this pelaksanaan
                                                                $alreadyScheduled = isset($jadwalByPosisi[$posisi]) && $jadwalByPosisi[$posisi]->id_anggota == $a->id_anggota;
                                                            @endphp
                                                            
                                                            <option 
                                                                value="{{ $a->id_anggota }}" 
                                                                {{ $alreadyScheduled ? 'selected' : '' }}
                                                                {{ !$isAvailable ? 'disabled' : '' }}
                                                                data-reguler="{{ $isReguler }}"
                                                                data-last-served="{{ $lastServedInfo }}"
                                                            >
                                                                {{ $a->nama }}
                                                                {{ $isReguler ? '(Regular)' : '' }}
                                                                {{ !$isAvailable ? '(Tidak Tersedia)' : '' }}
                                                                {{ $lastServedInfo ? " - Last: $lastServedInfo" : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger btn-sm remove-petugas-row">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="col-md-2">

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
                            <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                            <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary">Batal</a>
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
        // Initialize Select2 for anggota selection
        $('.anggota-select').select2({
            placeholder: "Pilih Anggota",
            allowClear: true,
            templateResult: formatAnggotaSelection
        });

        $('#petugas-container').on('click', '.remove-petugas-row', function() {
            $(this).closest('.petugas-row').remove();
        });
        
        // Function to format anggota selection with availability and last served info
        function formatAnggotaSelection(anggota) {
            if (!anggota.id) {
                return anggota.text;
            }
            
            const $anggota = $(
                '<span>' + anggota.text + '</span>'
            );
            
            // Add reguler badge if applicable
            if ($(anggota.element).data('reguler')) {
                $anggota.append(' <span class="badge bg-success">Regular</span>');
            }
            
            // Add last served info if available
            const lastServed = $(anggota.element).data('last-served');
            if (lastServed) {
                $anggota.append(' <small class="text-muted">(Last: ' + lastServed + ')</small>');
            }
            
            return $anggota;
        }
        
        // Add new position field
        let positionCounter = {{ count($posisiOptions) }};
        $('#add-petugas').click(function() {
            const newRow = `
                <div class="row mb-3 petugas-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Posisi</label>
                            <input type="text" class="form-control" name="petugas[${positionCounter}][posisi]" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Anggota</label>
                            <select class="form-select anggota-select-new" name="petugas[${positionCounter}][id_anggota]" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach($anggota as $a)
                                    @php
                                        $isAvailable = true;
                                        $eventDay = \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->dayOfWeek;
                                        $eventStart = $pelaksanaan->jam_mulai;
                                        $eventEnd = $pelaksanaan->jam_selesai;
                                        
                                        // Check availability if set
                                        if (!empty($a->ketersediaan_hari) && !empty($a->ketersediaan_jam)) {
                                            // Check day
                                            if (!in_array($eventDay, $a->ketersediaan_hari)) {
                                                $isAvailable = false;
                                            }
                                            
                                            // Check time
                                            if ($isAvailable) {
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
                                    >
                                        {{ $a->nama }}
                                        {{ !$isAvailable ? '(Tidak Tersedia)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                    </div>
                </div>
            `;
            
            $('#petugas-container').append(newRow);
            
            // Initialize Select2 for the new row
            $('.anggota-select-new').select2({
                placeholder: "Pilih Anggota",
                allowClear: true
            }).removeClass('anggota-select-new');
            
            positionCounter++;
        });
    });
</script>
@endsection