@extends('layouts.app')

@section('title', 'Laporan Kehadiran')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<style>
    .filter-card {
        margin-bottom: 20px;
    }
    .stats-card {
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        color: white;
        text-align: center;
    }
    .stats-card.primary {
        background-color: #4e73df;
    }
    .stats-card.success {
        background-color: #1cc88a;
    }
    .stats-card.warning {
        background-color: #f6c23e;
    }
    .stats-card.info {
        background-color: #36b9cc;
    }
    .stats-card-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }
    .stats-card-title {
        font-size: 0.8rem;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .stats-card-value {
        font-size: 1.8rem;
        font-weight: bold;
    }
    .chart-container {
        position: relative;
        margin: auto;
        height: 300px;
        margin-bottom: 30px;
    }
    .table-container {
        margin-top: 20px;
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
        background: #007bff;
        border-color: #007bff;
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
        background-color: rgba(0,123,255,0.05);
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.5em 0.75em;
    }
    
    .filter-row {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .form-select, .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
    }
    
    .export-buttons {
        margin-bottom: 15px;
    }
    
    .export-buttons .btn {
        margin-right: 10px;
        margin-bottom: 5px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Kehadiran</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Kehadiran</li>
    </ol>

    <!-- Filter Section -->
    <div class="row">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filter Laporan
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.kehadiran') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="bulan" class="form-label">Bulan</label>
                                <select id="bulan" name="bulan" class="form-select">
                                    @foreach($bulanList as $key => $nama)
                                        <option value="{{ $key }}" {{ $bulan == $key ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="tahun" class="form-label">Tahun</label>
                                <select id="tahun" name="tahun" class="form-select">
                                    @foreach($tahunList as $t)
                                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Terapkan Filter
                                </button>
                                <a href="{{ route('laporan.kehadiran') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card primary">
                <div class="stats-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-card-title">Total Anggota</div>
                <div class="stats-card-value">{{ number_format($totalAnggota) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card success">
                <div class="stats-card-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stats-card-title">Total Kehadiran</div>
                <div class="stats-card-value">{{ number_format($totalKehadiran) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card warning">
                <div class="stats-card-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stats-card-title">Rata-rata Kehadiran</div>
                <div class="stats-card-value">
                    {{ $totalAnggota > 0 ? round(($totalKehadiran / ($totalAnggota * 4)) * 100) : 0 }}%
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card info">
                <div class="stats-card-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stats-card-title">Kehadiran Minggu Ini</div>
                <div class="stats-card-value">
                    {{ isset($kehadiranPerMinggu[count($kehadiranPerMinggu)-1]) ? number_format($kehadiranPerMinggu[count($kehadiranPerMinggu)-1]['jumlah']) : 0 }}
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Kehadiran per Kegiatan
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="kegiatanChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Kehadiran per Minggu
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="mingguChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Detail Kehadiran
                    @if($kegiatan_id)
                        <small class="text-muted ms-2">
                            - {{ $kegiatanList->where('id_kegiatan', $kegiatan_id)->first()->nama_kegiatan ?? 'Kegiatan Tidak Ditemukan' }}
                        </small>
                    @endif
                    @if($pelaksanaan_id)
                        <small class="text-muted ms-2">
                            - {{ $pelaksanaanList->where('id_pelaksanaan', $pelaksanaan_id)->first() ? 
                                \Carbon\Carbon::parse($pelaksanaanList->where('id_pelaksanaan', $pelaksanaan_id)->first()->tanggal_kegiatan)->format('d M Y') : 
                                'Pelaksanaan Tidak Ditemukan' }}
                        </small>
                    @endif
                </div>
                <div class="card-body table-container">
                    <!-- Export Buttons -->
                    <div class="export-buttons">
                        <a href="{{ route('laporan.export', ['jenis' => 'kehadiran', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                           class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'kehadiran', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                    
                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="kehadiranTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="120">Tanggal</th>
                                    <th>Nama Anggota</th>
                                    <th>Kegiatan</th>
                                    <th width="100">Waktu</th>
                                    <th width="80">Status</th>
                                    <th width="150">Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kehadiran as $k)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($k->waktu_absensi)->format('d-m-Y') }}</td>
                                    <td>
                                        <strong>{{ $k->anggota->nama ?? 'Tidak Diketahui' }}</strong>
                                        @if($k->anggota && $k->anggota->keluarga)
                                            <br><small class="text-muted">{{ $k->anggota->keluarga->nama_keluarga }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $k->pelaksanaan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui' }}</span>
                                        @if($k->pelaksanaan && $k->pelaksanaan->kegiatan)
                                            <br><small class="text-muted badge bg-light text-dark">
                                                {{ ucfirst($k->pelaksanaan->kegiatan->tipe_kegiatan) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($k->waktu_absensi)->format('H:i') }}</td>
                                    <td>
                                        @switch($k->status)
                                            @case('hadir')
                                                <span class="badge bg-success">Hadir</span>
                                                @break
                                            @case('izin')
                                                <span class="badge bg-warning text-dark">Izin</span>
                                                @break
                                            @case('sakit')
                                                <span class="badge bg-info">Sakit</span>
                                                @break
                                            @case('alfa')
                                                <span class="badge bg-danger">Alfa</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($k->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <small>{{ $k->pelaksanaan->lokasi ?? '-' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        const kehadiranTable = new simpleDatatables.DataTable("#kehadiranTable", {
            searchable: true,
            sortable: true,
            paging: true,
            perPage: 25,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Cari data kehadiran...",
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
        
        // Kehadiran per Kegiatan Chart
        const kegiatanCtx = document.getElementById('kegiatanChart').getContext('2d');
        const kegiatanData = @json($kehadiranPerKegiatan);
        const kegiatanLabels = kegiatanData.map(item => item.kegiatan);
        const kegiatanValues = kegiatanData.map(item => item.jumlah);
        
        new Chart(kegiatanCtx, {
            type: 'bar',
            data: {
                labels: kegiatanLabels,
                datasets: [{
                    label: 'Jumlah Kehadiran',
                    data: kegiatanValues,
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Kehadiran per Minggu Chart
        const mingguCtx = document.getElementById('mingguChart').getContext('2d');
        const mingguData = @json($kehadiranPerMinggu);
        const mingguLabels = mingguData.map(item => item.minggu);
        const mingguValues = mingguData.map(item => item.jumlah);
        
        new Chart(mingguCtx, {
            type: 'line',
            data: {
                labels: mingguLabels,
                datasets: [{
                    label: 'Jumlah Kehadiran',
                    data: mingguValues,
                    backgroundColor: 'rgba(28, 200, 138, 0.2)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Dependent dropdown: Pelaksanaan based on Kegiatan
        document.getElementById('kegiatan_id').addEventListener('change', function() {
            const kegiatanId = this.value;
            const pelaksanaanSelect = document.getElementById('pelaksanaan_id');
            
            // Clear pelaksanaan options
            pelaksanaanSelect.innerHTML = '<option value="">-- Semua Pelaksanaan --</option>';
            
            if (kegiatanId) {
                // Auto-submit form to get pelaksanaan data
                document.getElementById('filterForm').submit();
            }
        });
        
        // Auto-submit when month/year changes
        document.getElementById('bulan').addEventListener('change', function() {
            // Reset kegiatan and pelaksanaan when changing month/year
            document.getElementById('kegiatan_id').value = '';
            document.getElementById('pelaksanaan_id').innerHTML = '<option value="">-- Semua Pelaksanaan --</option>';
            document.getElementById('filterForm').submit();
        });
        
        document.getElementById('tahun').addEventListener('change', function() {
            // Reset kegiatan and pelaksanaan when changing month/year
            document.getElementById('kegiatan_id').value = '';
            document.getElementById('pelaksanaan_id').innerHTML = '<option value="">-- Semua Pelaksanaan --</option>';
            document.getElementById('filterForm').submit();
        });
    });
</script>
@endsection