@extends('layouts.app')

@section('title', 'Laporan Kehadiran Pribadi')

@section('styles')
<style>
    .report-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        border-left: 5px solid #667eea;
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 10px;
    }
    
    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }
    
    .stats-description {
        color: #495057;
        font-size: 0.95rem;
    }
    
    .attendance-list {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .filter-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .no-data-message {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .no-data-message i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }
    
    .profile-section {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: bold;
        flex-shrink: 0;
    }
    
    .profile-info h2 {
        margin: 0 0 5px 0;
        color: white;
    }
    
    .profile-meta {
        opacity: 0.9;
        font-size: 0.95rem;
    }
    
    .user-selector-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .user-selector-title {
        color: white;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .user-selector-form {
        display: flex;
        gap: 15px;
        align-items: end;
        flex-wrap: wrap;
    }
    
    .user-selector-form .form-group {
        flex: 1;
        min-width: 250px;
    }
    
    .user-selector-form label {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.9rem;
        margin-bottom: 8px;
        display: block;
    }
    
    .user-selector-form select {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        padding: 10px 15px;
        color: #333;
        width: 100%;
    }
    
    .user-selector-form select:focus {
        background: white;
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .user-selector-form .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        white-space: nowrap;
    }
    
    /* Custom DataTable Styling */
    .dataTables_wrapper {
        padding: 0;
    }
    
    .dataTables_filter {
        margin-bottom: 15px;
    }
    
    .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px 12px;
        margin-left: 8px;
    }
    
    .dataTables_length select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 4px 8px;
        margin: 0 8px;
    }
    
    .dataTables_info {
        padding-top: 8px;
        color: #6c757d;
    }
    
    .dataTables_paginate {
        padding-top: 8px;
    }
    
    .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin-left: 2px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        color: #495057;
        text-decoration: none;
    }
    
    .dataTables_paginate .paginate_button:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #667eea;
        border-color: #667eea;
        color: white !important;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
    
    .table td {
        vertical-align: middle;
        border-bottom: 1px solid #dee2e6;
    }
    
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.5em 0.75em;
    }
    
    .filter-section {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: end;
    }
    
    .export-buttons {
        margin-bottom: 15px;
    }
    
    .export-buttons .btn {
        margin-right: 10px;
        margin-bottom: 5px;
    }
    
    @media (max-width: 768px) {
        .report-header {
            padding: 20px;
        }
        
        .profile-section {
            flex-direction: column;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
        }
        
        .user-selector-form {
            flex-direction: column;
        }
        
        .user-selector-form .form-group {
            width: 100%;
            min-width: unset;
        }
        
        .filter-section {
            flex-direction: column;
        }
        
        .filter-section > div {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Kehadiran Pribadi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Kehadiran Pribadi</li>
    </ol>
    
    <div class="report-header">
        {{-- User Selection for Admin/Pengurus --}}
        @if($canSelectUser && $allUsers->count() > 0)
            <div class="user-selector-card">
                <div class="user-selector-title">
                    <i class="fas fa-users"></i>
                    <span>Pilih Anggota untuk Melihat Laporan</span>
                </div>
                <form method="GET" action="{{ route('laporan.personal-report') }}" class="user-selector-form">
                    <div class="form-group">
                        <label for="user_id">Pilih Anggota:</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">-- Pilih Anggota --</option>
                            @foreach($allUsers as $user)
                                <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                    {{ $user->anggota->nama ?? $user->name }} 
                                    @if($user->anggota && $user->anggota->keluarga)
                                        ({{ $user->anggota->keluarga->nama_keluarga }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="period">Periode:</label>
                        <select id="period" name="period" class="form-select">
                            <option value="1" {{ $period == 1 ? 'selected' : '' }}>1 Bulan</option>
                            <option value="3" {{ $period == 3 ? 'selected' : '' }}>3 Bulan</option>
                            <option value="6" {{ $period == 6 ? 'selected' : '' }}>6 Bulan</option>
                            <option value="12" {{ $period == 12 ? 'selected' : '' }}>1 Tahun</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-search me-2"></i>Lihat Laporan
                        </button>
                    </div>
                </form>
            </div>
        @endif
        
        <div class="profile-section">
            <div class="profile-avatar">
                {{ strtoupper(substr($anggota->nama, 0, 1)) }}
            </div>
            <div class="profile-info">
                <h2>{{ $anggota->nama }}</h2>
                <div class="profile-meta">
                    <div><i class="fas fa-calendar me-2"></i>Periode: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</div>
                    @if($anggota->keluarga)
                        <div><i class="fas fa-home me-2"></i>Keluarga: {{ $anggota->keluarga->nama_keluarga }}</div>
                    @endif
                    @if($canSelectUser && $selectedUserId)
                        <div><i class="fas fa-eye me-2"></i>Laporan Supervisori</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Filter untuk user yang tidak dapat memilih user lain --}}
    @if(!$canSelectUser || !$selectedUserId)
        <div class="filter-card">
            <h5><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
            <form method="GET" action="{{ route('laporan.personal-report') }}" id="filterForm">
                <div class="filter-section">
                    <div>
                        <label for="period" class="form-label">Periode</label>
                        <select id="period" name="period" class="form-select">
                            <option value="1" {{ $period == 1 ? 'selected' : '' }}>1 Bulan</option>
                            <option value="3" {{ $period == 3 ? 'selected' : '' }}>3 Bulan</option>
                            <option value="6" {{ $period == 6 ? 'selected' : '' }}>6 Bulan</option>
                            <option value="12" {{ $period == 12 ? 'selected' : '' }}>1 Tahun</option>
                        </select>
                    </div>
                    <div>
                        <label for="kegiatan_id" class="form-label">Kegiatan</label>
                        <select id="kegiatan_id" name="kegiatan_id" class="form-select">
                            <option value="">-- Semua Kegiatan --</option>
                            @foreach($kegiatanList as $kegiatan)
                                <option value="{{ $kegiatan->id_kegiatan }}" 
                                        {{ $kegiatan_id == $kegiatan->id_kegiatan ? 'selected' : '' }}>
                                    {{ $kegiatan->nama_kegiatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="pelaksanaan_id" class="form-label">Pelaksanaan Spesifik</label>
                        <select id="pelaksanaan_id" name="pelaksanaan_id" class="form-select">
                            <option value="">-- Semua Pelaksanaan --</option>
                            @foreach($pelaksanaanList as $pelaksanaan)
                                <option value="{{ $pelaksanaan->id_pelaksanaan }}" 
                                        {{ $pelaksanaan_id == $pelaksanaan->id_pelaksanaan ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('d M Y') }} 
                                    ({{ \Carbon\Carbon::parse($pelaksanaan->jam_mulai)->format('H:i') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status_filter" class="form-label">Status Kehadiran</label>
                        <select id="status_filter" name="status_filter" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="hadir" {{ request('status_filter') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="tidak_hadir" {{ request('status_filter') == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
    
    <div class="row">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">Total Kehadiran</div>
                <div class="stats-number">{{ number_format($totalKehadiran) }}</div>
                <div class="stats-description">
                    Dalam {{ round($startDate->floatDiffInMonths($endDate)) }} bulan terakhir
                    @if($kegiatan_id)
                        <br><small class="text-muted">
                            Filter: {{ $kegiatanList->where('id_kegiatan', $kegiatan_id)->first()->nama_kegiatan ?? 'Kegiatan Tidak Ditemukan' }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">Rata-rata per Bulan</div>
                <div class="stats-number">
                    {{ $kehadiranPerBulan->count() > 0 ? round($kehadiranPerBulan->avg(), 1) : 0 }}
                </div>
                <div class="stats-description">
                    Kegiatan bulanan {{ $canSelectUser && $selectedUserId ? $anggota->nama : 'Anda' }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">Kegiatan Terfavorit</div>
                <div class="stats-number">{{ $kehadiranPerKegiatan->count() > 0 ? Str::limit($kehadiranPerKegiatan->keys()->first(), 15) : '-' }}</div>
                <div class="stats-description">
                    {{ $kehadiranPerKegiatan->count() > 0 ? $kehadiranPerKegiatan->first() . ' kali hadir' : 'Belum ada data' }}
                </div>
            </div>
        </div>
    </div>
    
    @if($totalKehadiran > 0 || $kehadiran->count() > 0)
        <div class="attendance-list">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Riwayat Kehadiran
                    @if($kegiatan_id || $pelaksanaan_id)
                        <small class="text-muted">
                            @if($kegiatan_id)
                                - {{ $kegiatanList->where('id_kegiatan', $kegiatan_id)->first()->nama_kegiatan ?? 'Kegiatan Tidak Ditemukan' }}
                            @endif
                            @if($pelaksanaan_id)
                                - {{ $pelaksanaanList->where('id_pelaksanaan', $pelaksanaan_id)->first() ? 
                                    \Carbon\Carbon::parse($pelaksanaanList->where('id_pelaksanaan', $pelaksanaan_id)->first()->tanggal_kegiatan)->format('d M Y') : 
                                    'Pelaksanaan Tidak Ditemukan' }}
                            @endif
                        </small>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                <!-- Export Buttons -->
                @if(!$canSelectUser || !$selectedUserId)
                    <div class="export-buttons p-3">
                        <a href="{{ route('laporan.export', ['jenis' => 'personal-report', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                        class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'personal-report', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                        class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                @else
                    <div class="export-buttons p-3">
                        @php
                            $exportParams = [
                                'user_id' => $selectedUserId,
                                'period' => $period
                            ];
                            if($kegiatan_id) $exportParams['kegiatan_id'] = $kegiatan_id;
                            if($pelaksanaan_id) $exportParams['pelaksanaan_id'] = $pelaksanaan_id;
                            if(request('status_filter')) $exportParams['status_filter'] = request('status_filter');
                            $exportQuery = http_build_query($exportParams);
                        @endphp
                        <a href="{{ route('laporan.export', ['jenis' => 'personal-report', 'format' => 'pdf']) }}?{{ $exportQuery }}" 
                        class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'personal-report', 'format' => 'excel']) }}?{{ $exportQuery }}" 
                        class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                @endif
                
                <!-- DataTable -->
                <div class="table-responsive">
                    <table id="kehadiranTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="120">Tanggal</th>
                                <th>Kegiatan</th>
                                <th width="100">Waktu</th>
                                <th width="100">Status</th>
                                <th width="150">Lokasi</th>
                                <th width="200">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kehadiran as $attendance)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($attendance['tanggal'])->format('d-m-Y') }}</td>
                                    <td>
                                        <strong>{{ $attendance['nama_kegiatan'] }}</strong>
                                        @if($attendance['tipe_kegiatan'])
                                            <br><small class="text-muted badge bg-light text-dark">
                                                {{ ucfirst($attendance['tipe_kegiatan']) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance['waktu_absensi'])
                                            {{ \Carbon\Carbon::parse($attendance['waktu_absensi'])->format('H:i') }}
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($attendance['status'])
                                            @case('hadir')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>{{ ucfirst($attendance['status']) }}
                                                </span>
                                                @break
                                            @case('izin')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-user-clock me-1"></i>{{ ucfirst($attendance['status']) }}
                                                </span>
                                                @break
                                            @case('sakit')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-thermometer me-1"></i>{{ ucfirst($attendance['status']) }}
                                                </span>
                                                @break
                                            @case('alfa')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>{{ ucfirst($attendance['status']) }}
                                                </span>
                                                @break
                                            @case('tidak_hadir')
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-user-times me-1"></i>Tidak Hadir
                                                </span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($attendance['status']) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <small>{{ $attendance['lokasi'] ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $attendance['keterangan'] ?? '-' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="attendance-list">
            <div class="no-data-message">
                <i class="fas fa-calendar-times"></i>
                <h5>Belum Ada Data Kehadiran</h5>
                <p>{{ $canSelectUser && $selectedUserId ? $anggota->nama : 'Anda' }} belum memiliki catatan kehadiran dalam {{ round($startDate->floatDiffInMonths($endDate)) }} bulan terakhir.</p>
                @if(!$canSelectUser || !$selectedUserId)
                    @if(Route::has('kehadiran.index'))
                        <a href="{{ route('kehadiran.index') }}" class="btn btn-primary">
                            <i class="fas fa-qrcode me-2"></i>Mulai Presensi
                        </a>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    @if($totalKehadiran > 0 || $kehadiran->count() > 0)
        const kehadiranTable = new simpleDatatables.DataTable("#kehadiranTable", {
            searchable: true,
            sortable: true,
            paging: true,
            perPage: 25,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Cari kegiatan, status, atau lokasi...",
                perPage: "data per halaman",
                noRows: "Tidak ada data kehadiran",
                info: "Menampilkan {start} sampai {end} dari {rows} data",
                previous: "Sebelumnya",
                next: "Selanjutnya"
            },
            layout: {
                top: "{select}{search}",
                bottom: "{info}{pager}"
            }
        });
    @endif
    
    // Dependent dropdown: Pelaksanaan based on Kegiatan
    const kegiatanSelect = document.getElementById('kegiatan_id');
    const pelaksanaanSelect = document.getElementById('pelaksanaan_id');
    
    if (kegiatanSelect && pelaksanaanSelect) {
        kegiatanSelect.addEventListener('change', function() {
            const kegiatanId = this.value;
            
            // Clear pelaksanaan options
            pelaksanaanSelect.innerHTML = '<option value="">-- Semua Pelaksanaan --</option>';
            
            if (kegiatanId) {
                // Auto-submit form to get pelaksanaan data
                document.getElementById('filterForm').submit();
            }
        });
    }
    
    // Period selector - only for non-admin users or when no user selected
    @if(!$canSelectUser || !$selectedUserId)
        const periodSelect = document.getElementById('period');
        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                // Reset kegiatan and pelaksanaan when changing period
                if (kegiatanSelect) kegiatanSelect.value = '';
                if (pelaksanaanSelect) pelaksanaanSelect.innerHTML = '<option value="">-- Semua Pelaksanaan --</option>';
                document.getElementById('filterForm').submit();
            });
        }
    @endif
});
</script>
@endsection