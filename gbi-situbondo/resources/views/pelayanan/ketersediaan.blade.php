@extends('layouts.app')

@section('title', 'Pengaturan Ketersediaan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Pengaturan Ketersediaan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item active">Ketersediaan {{ $anggota->nama }}</li>
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
                    <i class="fas fa-calendar-check me-1"></i>
                    Pengaturan Ketersediaan Waktu
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
                    
                    <form action="{{ route('pelayanan.save-availability') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_anggota" value="{{ $anggota->id_anggota }}">
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5>Informasi Penggunaan</h5>
                                    <p>Tetapkan kapan Anda tersedia untuk melayani. Informasi ini akan digunakan saat penjadwalan layanan dan pembuatan jadwal otomatis.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Hari Ketersediaan</h5>
                                <p class="text-muted">Pilih hari-hari dimana Anda tersedia untuk melayani:</p>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="day_0" name="ketersediaan_hari[]" value="0" {{ in_array(0, $anggota->ketersediaan_hari ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_0">Minggu</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="day_1" name="ketersediaan_hari[]" value="1" {{ in_array(1, $anggota->ketersediaan_hari ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_1">Senin</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="day_2" name="ketersediaan_hari[]" value="2" {{ in_array(2, $anggota->ketersediaan_hari ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_2">Selasa</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="day_3" name="ketersediaan_hari[]" value="3" {{ in_array(3, $anggota->ketersediaan_hari ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_3">Rabu</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="day_4" name="ketersediaan_hari[]" value="4" {{ in_array(4, $anggota->ketersediaan_hari ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_4">Kamis</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="day_5" name="ketersediaan_hari[]" value="5" {{ in_array(5, $anggota->ketersediaan_hari ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_5">Jumat</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="day_6" name="ketersediaan_hari[]" value="6" {{ in_array(6, $anggota->ketersediaan_hari ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_6">Sabtu</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-secondary" id="select-all-days">Pilih Semua Hari</button>
                                            <button type="button" class="btn btn-sm btn-secondary" id="select-weekend">Pilih Weekend</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Jam Ketersediaan</h5>
                                <p class="text-muted">Tetapkan rentang waktu dimana Anda tersedia untuk melayani:</p>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <div id="time-slots-container">
                                            @if(!empty($anggota->ketersediaan_jam))
                                                @foreach($anggota->ketersediaan_jam as $index => $timeSlot)
                                                    @php
                                                        list($start, $end) = explode('-', $timeSlot);
                                                    @endphp
                                                    <div class="row mb-3 time-slot">
                                                        <div class="col-md-5">
                                                            <label class="form-label">Jam Mulai</label>
                                                            <input type="time" class="form-control time-start" name="ketersediaan_jam_start[]" value="{{ $start }}" required>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="form-label">Jam Selesai</label>
                                                            <input type="time" class="form-control time-end" name="ketersediaan_jam_end[]" value="{{ $end }}" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">&nbsp;</label>
                                                            <button type="button" class="btn btn-danger form-control remove-time-slot">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="row mb-3 time-slot">
                                                    <div class="col-md-5">
                                                        <label class="form-label">Jam Mulai</label>
                                                        <input type="time" class="form-control time-start" name="ketersediaan_jam_start[]" value="08:00" required>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label">Jam Selesai</label>
                                                        <input type="time" class="form-control time-end" name="ketersediaan_jam_end[]" value="22:00" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="button" class="btn btn-danger form-control remove-time-slot">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-success" id="add-time-slot">
                                                <i class="fas fa-plus"></i> Tambah Slot Waktu
                                            </button>
                                            
                                            <button type="button" class="btn btn-secondary" id="add-common-slots">
                                                <i class="fas fa-clock"></i> Tambah Slot Umum
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty($positions))
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Posisi Reguler</h5>
                                <p class="text-muted">Pilih posisi dimana Anda adalah pemain reguler:</p>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($positions as $position)
                                                <div class="col-md-4">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" 
                                                            id="pos_{{ Str::slug($position) }}" 
                                                            name="posisi_reguler[]" 
                                                            value="{{ $position }}" 
                                                            {{ in_array($position, $regularPositions ?? []) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="pos_{{ Str::slug($position) }}">
                                                            {{ $position }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan Ketersediaan</button>
                            <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary">Kembali</a>
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
        // Handle select all days
        $('#select-all-days').click(function() {
            $('input[name="ketersediaan_hari[]"]').prop('checked', true);
        });
        
        // Handle select weekend only
        $('#select-weekend').click(function() {
            $('input[name="ketersediaan_hari[]"]').prop('checked', false);
            $('#day_0, #day_6').prop('checked', true);
        });
        
        // Handle add time slot
        $('#add-time-slot').click(function() {
            const newTimeSlot = `
                <div class="row mb-3 time-slot">
                    <div class="col-md-5">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control time-start" name="ketersediaan_jam_start[]" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control time-end" name="ketersediaan_jam_end[]" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger form-control remove-time-slot">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#time-slots-container').append(newTimeSlot);
        });
        
        // Handle remove time slot
        $(document).on('click', '.remove-time-slot', function() {
            // Don't remove if it's the last time slot
            if ($('.time-slot').length > 1) {
                $(this).closest('.time-slot').remove();
            } else {
                alert('Minimal harus ada satu slot waktu.');
            }
        });
        
        // Handle add common time slots
        $('#add-common-slots').click(function() {
            // Clear existing slots
            $('#time-slots-container').empty();
            
            // Add common slots
            const commonSlots = [
                { start: "06:00", end: "09:00", label: "Pagi" },
                { start: "17:00", end: "21:00", label: "Malam" }
            ];
            
            commonSlots.forEach(function(slot) {
                const newTimeSlot = `
                    <div class="row mb-3 time-slot">
                        <div class="col-md-5">
                            <label class="form-label">Jam Mulai (${slot.label})</label>
                            <input type="time" class="form-control time-start" name="ketersediaan_jam_start[]" value="${slot.start}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Jam Selesai (${slot.label})</label>
                            <input type="time" class="form-control time-end" name="ketersediaan_jam_end[]" value="${slot.end}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger form-control remove-time-slot">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                $('#time-slots-container').append(newTimeSlot);
            });
        });
        
        // Form submission
        $('form').submit(function(e) {
            // Validate that at least one day is selected
            if ($('input[name="ketersediaan_hari[]"]:checked').length === 0) {
                e.preventDefault();
                alert('Harap pilih minimal satu hari ketersediaan.');
                return false;
            }
            
            // Format time slots
            const timeSlots = [];
            $('.time-slot').each(function() {
                const start = $(this).find('.time-start').val();
                const end = $(this).find('.time-end').val();
                
                if (start && end) {
                    // Validate end time is after start time
                    if (start >= end) {
                        e.preventDefault();
                        alert('Jam selesai harus lebih besar dari jam mulai.');
                        return false;
                    }
                    
                    timeSlots.push(`${start}-${end}`);
                }
            });
            
            // Add hidden field with formatted time slots
            $('input[name="ketersediaan_jam[]"]').remove();
            timeSlots.forEach(function(slot) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'ketersediaan_jam[]',
                    value: slot
                }).appendTo('form');
            });
        });
    });
</script>
@endsection