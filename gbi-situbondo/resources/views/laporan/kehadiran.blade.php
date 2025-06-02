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

    <div class="row">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filter Laporan
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.kehadiran') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select id="bulan" name="bulan" class="form-select">
                                @foreach($bulanList as $key => $nama)
                                    <option value="{{ $key }}" {{ $bulan == $key ? 'selected' : '' }}>{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select id="tahun" name="tahun" class="form-select">
                                @foreach($tahunList as $t)
                                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Terapkan Filter</button>
                            <a href="{{ route('laporan.kehadiran') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card primary">
                <div class="stats-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-card-title">Total Anggota</div>
                <div class="stats-card-value">{{ $totalAnggota }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card success">
                <div class="stats-card-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stats-card-title">Total Kehadiran</div>
                <div class="stats-card-value">{{ $totalKehadiran }}</div>
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
                    {{ isset($kehadiranPerMinggu[count($kehadiranPerMinggu)-1]) ? $kehadiranPerMinggu[count($kehadiranPerMinggu)-1]['jumlah'] : 0 }}
                </div>
            </div>
        </div>
    </div>

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

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Detail Kehadiran
                </div>
                <div class="card-body table-container">
                    <div class="mb-3">
                        <a href="{{ route('laporan.export', ['jenis' => 'kehadiran', 'format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'kehadiran', 'format' => 'excel']) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                    <table id="kehadiranTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Anggota</th>
                                <th>Kegiatan</th>
                                <th>Waktu Absensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kehadiran as $k)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($k->waktu_absensi)->format('d-m-Y') }}</td>
                                <td>{{ $k->anggota->nama ?? 'Tidak Diketahui' }}</td>
                                <td>{{ $k->kegiatan->nama_kegiatan ?? 'Tidak Diketahui' }}</td>
                                <td>{{ \Carbon\Carbon::parse($k->waktu_absensi)->format('H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
        
        // Initialize DataTable
        $('#kehadiranTable').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });
    });
</script>
@endsection