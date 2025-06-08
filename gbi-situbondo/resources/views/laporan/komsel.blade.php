@extends('layouts.app')

@section('title', 'Laporan Komsel')

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
    .komsel-card {
        margin-bottom: 20px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .komsel-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
    .komsel-header {
        padding: 15px;
        background-color: #f6c23e;
        color: white;
    }
    .komsel-title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .komsel-subtitle {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    .komsel-body {
        padding: 15px;
        background-color: white;
    }
    .komsel-stat {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .komsel-stat-label {
        font-weight: bold;
        color: #4e73df;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Komsel</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Komsel</li>
    </ol>


    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card primary">
                <div class="stats-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-card-title">Total Komsel</div>
                <div class="stats-card-value">{{ $totalKomsel }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card success">
                <div class="stats-card-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stats-card-title">Total Anggota Komsel</div>
                <div class="stats-card-value">{{ $totalAnggotaKomsel }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card warning">
                <div class="stats-card-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="stats-card-title">Rata-rata Anggota</div>
                <div class="stats-card-value">{{ round($rataRataAnggota, 1) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card info">
                <div class="stats-card-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stats-card-title">Kegiatan Bulan Ini</div>
                <div class="stats-card-value">{{ $kegiatanKomsel->count() }}</div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Komsel dengan Anggota Terbanyak
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="komselTerbanyakChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Kehadiran Komsel per Minggu
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="kehadiranKomselChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Daftar Komsel
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('laporan.export', ['jenis' => 'komsel', 'format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'komsel', 'format' => 'excel']) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                    <div class="row">
                        @foreach($komsel as $k)
                        <div class="col-xl-4 col-md-6">
                            <div class="komsel-card">
                                <div class="komsel-header">
                                    <div class="komsel-title">{{ $k->nama_komsel }}</div>
                                    <div class="komsel-subtitle">Pemimpin: {{ $k->pemimpin->nama ?? 'Belum ditentukan' }}</div>
                                </div>
                                <div class="komsel-body">
                                    <div class="komsel-stat">
                                        <div class="komsel-stat-label">Jumlah Anggota:</div>
                                        <div>{{ $k->anggota->count() }} orang</div>
                                    </div>
                                    <div class="komsel-stat">
                                        <div class="komsel-stat-label">Lokasi:</div>
                                        <div>{{ $k->lokasi ?? '-' }}</div>
                                    </div>
                                    <div class="komsel-stat">
                                        <div class="komsel-stat-label">Jadwal:</div>
                                        <div>{{ $k->hari ?? '-' }}, {{ $k->jam ?? '-' }}</div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('komsel.show', $k->id_komsel) }}" class="btn btn-sm btn-warning w-100">Detail Komsel</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
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
        // Komsel dengan Anggota Terbanyak Chart
        const komselTerbanyakCtx = document.getElementById('komselTerbanyakChart').getContext('2d');
        const komselTerbanyakData = @json($komselTerbanyak);
        const komselTerbanyakLabels = komselTerbanyakData.map(item => item.nama);
        const komselTerbanyakValues = komselTerbanyakData.map(item => item.jumlah);
        
        new Chart(komselTerbanyakCtx, {
            type: 'bar',
            data: {
                labels: komselTerbanyakLabels,
                datasets: [{
                    label: 'Jumlah Anggota',
                    data: komselTerbanyakValues,
                    backgroundColor: 'rgba(246, 194, 62, 0.8)',
                    borderColor: 'rgba(246, 194, 62, 1)',
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
        
        // Kehadiran Komsel per Minggu Chart
        const kehadiranKomselCtx = document.getElementById('kehadiranKomselChart').getContext('2d');
        const kehadiranKomselData = @json($kehadiranPerMinggu);
        const kehadiranKomselLabels = kehadiranKomselData.map(item => item.minggu);
        const kehadiranKomselValues = kehadiranKomselData.map(item => item.jumlah);
        
        new Chart(kehadiranKomselCtx, {
            type: 'line',
            data: {
                labels: kehadiranKomselLabels,
                datasets: [{
                    label: 'Jumlah Kehadiran',
                    data: kehadiranKomselValues,
                    backgroundColor: 'rgba(246, 194, 62, 0.2)',
                    borderColor: 'rgba(246, 194, 62, 1)',
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
    });
</script>
@endsection