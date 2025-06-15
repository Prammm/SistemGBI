@extends('layouts.app')

@section('title', 'Laporan Anggota')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<style>
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
    .stats-card.danger {
        background-color: #e74a3b;
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
    .progress {
        height: 10px;
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
    
    .export-buttons {
        margin-bottom: 20px;
    }
    
    .export-buttons .btn {
        margin-right: 10px;
        margin-bottom: 5px;
    }
    
    .anggota-profile {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .anggota-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    
    .anggota-info h6 {
        margin: 0;
        font-weight: 600;
        color: #333;
    }
    
    .anggota-meta {
        font-size: 0.8rem;
        color: #6c757d;
        margin: 0;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Anggota</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Anggota</li>
    </ol>

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
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stats-card-title">Anggota Aktif</div>
                <div class="stats-card-value">{{ number_format($anggotaAktif) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card danger">
                <div class="stats-card-icon">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stats-card-title">Anggota Tidak Aktif</div>
                <div class="stats-card-value">{{ number_format($anggotaTidakAktif) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card warning">
                <div class="stats-card-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stats-card-title">Tingkat Keaktifan</div>
                <div class="stats-card-value">
                    {{ $totalAnggota > 0 ? round(($anggotaAktif / $totalAnggota) * 100) : 0 }}%
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Anggota per Gender
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Anggota per Kelompok Umur
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="umurChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Pertumbuhan Anggota Baru (12 Bulan Terakhir)
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="pertumbuhanChart"></canvas>
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
                    Data Anggota
                </div>
                <div class="card-body table-container">
                    <!-- Export Buttons -->
                    <div class="export-buttons">
                        <a href="{{ route('laporan.export', ['jenis' => 'anggota', 'format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'anggota', 'format' => 'excel']) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                    
                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="anggotaTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="250">Nama</th>
                                    <th width="80">Gender</th>
                                    <th width="100">Umur</th>
                                    <th width="120">No. Telepon</th>
                                    <th width="150">Email</th>
                                    <th width="120">Keluarga</th>
                                    <th width="100">Status</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($anggota as $a)
                                <tr>
                                    <td>
                                        <div class="anggota-profile">
                                            <div class="anggota-avatar">
                                                {{ strtoupper(substr($a->nama, 0, 1)) }}
                                            </div>
                                            <div class="anggota-info">
                                                <h6>{{ $a->nama }}</h6>
                                                @if($a->tanggal_lahir)
                                                    <p class="anggota-meta">
                                                        {{ \Carbon\Carbon::parse($a->tanggal_lahir)->format('d M Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @switch($a->jenis_kelamin)
                                            @case('L')
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-mars me-1"></i>Laki-laki
                                                </span>
                                                @break
                                            @case('P')
                                                <span class="badge bg-pink" style="background-color: #e91e63 !important;">
                                                    <i class="fas fa-venus me-1"></i>Perempuan
                                                </span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">-</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($a->tanggal_lahir)
                                            <span class="fw-medium">{{ \Carbon\Carbon::parse($a->tanggal_lahir)->age }}</span> tahun
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($a->no_telepon)
                                            <a href="tel:{{ $a->no_telepon }}" class="text-decoration-none">
                                                <i class="fas fa-phone me-1"></i>{{ $a->no_telepon }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($a->email)
                                            <a href="mailto:{{ $a->email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope me-1"></i>
                                                <small>{{ $a->email }}</small>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($a->keluarga)
                                            <span class="badge bg-info">
                                                <i class="fas fa-home me-1"></i>{{ $a->keluarga->nama_keluarga }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $aktif = \App\Models\Kehadiran::where('id_anggota', $a->id_anggota)
                                                ->where('waktu_absensi', '>=', \Carbon\Carbon::now()->subMonths(3))
                                                ->exists();
                                        @endphp
                                        @if($aktif)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Tidak Aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if(Route::has('anggota.show'))
                                                <a href="{{ route('anggota.show', $a->id_anggota) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                            @if(Route::has('anggota.edit') && Auth::user()->id_role <= 2)
                                                <a href="{{ route('anggota.edit', $a->id_anggota) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
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
        const anggotaTable = new simpleDatatables.DataTable("#anggotaTable", {
            searchable: true,
            sortable: true,
            paging: true,
            perPage: 25,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Cari nama, email, telepon, atau keluarga...",
                perPage: "data per halaman",
                noRows: "Tidak ada data anggota",
                info: "Menampilkan {start} sampai {end} dari {rows} data",
                previous: "Sebelumnya",
                next: "Selanjutnya"
            },
            layout: {
                top: "{select}{search}",
                bottom: "{info}{pager}"
            },
            columns: [
                { select: 0, sortable: true },  // Nama
                { select: 1, sortable: true },  // Gender
                { select: 2, sortable: true, type: "number" },  // Umur
                { select: 3, sortable: false }, // No. Telepon
                { select: 4, sortable: false }, // Email
                { select: 5, sortable: true },  // Keluarga
                { select: 6, sortable: true },  // Status
                { select: 7, sortable: false }  // Aksi
            ]
        });
        
        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderData = @json($anggotaPerGender);
        const genderLabels = genderData.map(item => item.gender);
        const genderValues = genderData.map(item => item.jumlah);
        const genderColors = [
            'rgba(78, 115, 223, 0.8)',
            'rgba(233, 30, 99, 0.8)',
            'rgba(246, 194, 62, 0.8)'
        ];
        
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: genderLabels,
                datasets: [{
                    data: genderValues,
                    backgroundColor: genderColors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        
        // Umur Chart
        const umurCtx = document.getElementById('umurChart').getContext('2d');
        const umurData = @json($anggotaPerUmur);
        const umurLabels = umurData.map(item => item.kelompok);
        const umurValues = umurData.map(item => item.jumlah);
        
        new Chart(umurCtx, {
            type: 'bar',
            data: {
                labels: umurLabels,
                datasets: [{
                    label: 'Jumlah Anggota',
                    data: umurValues,
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
        
        // Pertumbuhan Chart
        const pertumbuhanCtx = document.getElementById('pertumbuhanChart').getContext('2d');
        const pertumbuhanData = @json($anggotaBaruPerBulan);
        const pertumbuhanLabels = pertumbuhanData.map(item => item.bulan);
        const pertumbuhanValues = pertumbuhanData.map(item => item.jumlah);
        
        new Chart(pertumbuhanCtx, {
            type: 'line',
            data: {
                labels: pertumbuhanLabels,
                datasets: [{
                    label: 'Anggota Baru',
                    data: pertumbuhanValues,
                    backgroundColor: 'rgba(28, 200, 138, 0.2)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
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
    });
</script>
@endsection