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
                    
                    <form action="{{ route('pelayanan.generate') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5>Informasi Penggunaan</h5>
                                    <p>Generator ini akan secara otomatis membuat jadwal pelayanan dengan mempertimbangkan:</p>
                                    <ul>
                                        <li>Ketersediaan waktu anggota pelayanan</li>
                                        <li>Rotasi yang adil untuk memberikan kesempatan bagi semua anggota</li>
                                        <li>Mempertahankan pemain reguler pada posisi yang sudah ditetapkan</li>
                                        <li>Riwayat pelayanan terakhir untuk distribusi yang lebih merata</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_pelaksanaan" class="form-label">Pilih Kegiatan</label>
                                    <select class="form-select" id="id_pelaksanaan" name="id_pelaksanaan" required>
                                        <option value="">-- Pilih Kegiatan --</option>
                                        @foreach($pelaksanaan as $p)
                                            <option value="{{ $p->id_pelaksanaan }}">
                                                {{ $p->kegiatan->nama_kegiatan }} - {{ \Carbon\Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bobot_reguler" class="form-label">Bobot Pemain Reguler (1-10)</label>
                                    <input type="number" class="form-control" id="bobot_reguler" name="bobot_reguler" min="1" max="10" value="5" required>
                                    <div class="form-text">Semakin tinggi nilai, semakin diprioritaskan pemain reguler.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Posisi yang Akan Dijadwalkan</label>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_worship_leader" name="positions[]" value="Worship Leader" checked>
                                                        <label class="form-check-label" for="pos_worship_leader">Worship Leader</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_singer" name="positions[]" value="Singer" checked>
                                                        <label class="form-check-label" for="pos_singer">Singer</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_keyboard" name="positions[]" value="Keyboard" checked>
                                                        <label class="form-check-label" for="pos_keyboard">Keyboard</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_guitar" name="positions[]" value="Guitar" checked>
                                                        <label class="form-check-label" for="pos_guitar">Guitar</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_bass" name="positions[]" value="Bass" checked>
                                                        <label class="form-check-label" for="pos_bass">Bass</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_drum" name="positions[]" value="Drum" checked>
                                                        <label class="form-check-label" for="pos_drum">Drum</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_sound" name="positions[]" value="Sound System" checked>
                                                        <label class="form-check-label" for="pos_sound">Sound System</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_multimedia" name="positions[]" value="Multimedia" checked>
                                                        <label class="form-check-label" for="pos_multimedia">Multimedia</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_usher" name="positions[]" value="Usher" checked>
                                                        <label class="form-check-label" for="pos_usher">Usher</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_liturgos" name="positions[]" value="Liturgos" checked>
                                                        <label class="form-check-label" for="pos_liturgos">Liturgos</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_alkitab" name="positions[]" value="Pembaca Alkitab" checked>
                                                        <label class="form-check-label" for="pos_alkitab">Pembaca Alkitab</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input position-checkbox" type="checkbox" id="pos_custom" name="positions[]" value="Lainnya">
                                                        <label class="form-check-label" for="pos_custom">
                                                            <input type="text" class="form-control form-control-sm custom-position" placeholder="Posisi Lain">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-secondary" id="select-all-positions">Pilih Semua</button>
                                                <button type="button" class="btn btn-sm btn-secondary" id="deselect-all-positions">Batalkan Semua</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Pilih Anggota Pelayanan</label>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($anggota->chunk(ceil($anggota->count() / 3)) as $chunk)
                                                    <div class="col-md-4">
                                                        @foreach($chunk as $a)
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input anggota-checkbox" type="checkbox" id="anggota_{{ $a->id_anggota }}" name="anggota[]" value="{{ $a->id_anggota }}" checked>
                                                                <label class="form-check-label" for="anggota_{{ $a->id_anggota }}">
                                                                    {{ $a->nama }}
                                                                    @php
                                                                        // Check if this person is a regular player
                                                                        $isReguler = $a->jadwalPelayanan
                                                                            ->where('is_reguler', true)
                                                                            ->count() > 0;
                                                                            
                                                                        // Get regular positions
                                                                        $regularPositions = $a->jadwalPelayanan
                                                                            ->where('is_reguler', true)
                                                                            ->pluck('posisi')
                                                                            ->unique()
                                                                            ->implode(', ');
                                                                    @endphp
                                                                    
                                                                    @if($isReguler)
                                                                        <span class="badge bg-success">Regular: {{ $regularPositions }}</span>
                                                                    @endif
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-secondary" id="select-all-anggota">Pilih Semua</button>
                                                <button type="button" class="btn btn-sm btn-secondary" id="deselect-all-anggota">Batalkan Semua</button>
                                                <button type="button" class="btn btn-sm btn-info" id="select-regular-anggota">Pilih Pemain Reguler</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-magic"></i> Generate Jadwal
                            </button>
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
        // Select2 for dropdown
        $('#id_pelaksanaan').select2({
            placeholder: "Pilih Kegiatan",
            allowClear: true
        });
        
        // Handle select all positions
        $('#select-all-positions').click(function() {
            $('.position-checkbox').prop('checked', true);
        });
        
        // Handle deselect all positions
        $('#deselect-all-positions').click(function() {
            $('.position-checkbox').prop('checked', false);
        });
        
        // Handle select all anggota
        $('#select-all-anggota').click(function() {
            $('.anggota-checkbox').prop('checked', true);
        });
        
        // Handle deselect all anggota
        $('#deselect-all-anggota').click(function() {
            $('.anggota-checkbox').prop('checked', false);
        });
        
        // Handle select only regular anggota
        $('#select-regular-anggota').click(function() {
            $('.anggota-checkbox').prop('checked', false);
            $('.anggota-checkbox').each(function() {
                const label = $(this).next('label');
                if (label.find('.badge').length > 0) {
                    $(this).prop('checked', true);
                }
            });
        });
        
        // Handle custom position
        $('.custom-position').on('input', function() {
            const checkbox = $('#pos_custom');
            checkbox.val($(this).val() || 'Lainnya');
            
            if ($(this).val()) {
                checkbox.prop('checked', true);
            }
        });
    });
</script>
@endsection