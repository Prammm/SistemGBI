<!-- resources/views/laporan/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard Analitik')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<style>
    .stats-card {
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        color: white;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
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
    .stats-card-trend {
        font-size: 0.8rem;
        margin-top: 5px;
    }
    .stats-card-trend.up {
        color: #1cc88a;
    }
    .stats-card-trend.down {
        color: #e74a3b;
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
    .kegiatan-card {
        border-left: 4px solid #4e73df;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .kegiatan-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .kegiatan-date {
        font-size: 0.9rem;
        color: #666;
    }
    .kegiatan-title {
        font-weight: bold;
    }
    .kegiatan-location {
        font-size: 0.9rem;
    }
    .ultah-card {
        border-left: 4px solid #f6c23e;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .ultah-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .ultah-name {
        font-weight: bold;
    }
    .ultah-date {
        font-size: 0.9rem;
        color: #666;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard Analitik</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Dashboard Analitik</li>
    </ol>

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
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stats-card-title">Kegiatan Bulan Ini</div>
                <div class="stats-card-value">{{ $totalKegiatan }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card warning">
                <div class="stats-card-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stats-card-title">Total Komsel</div>
                <div class="stats-card-value">{{ $totalKomsel }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card info">
                <div class="stats-card-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stats-card-title">Kehadiran Minggu Ini</div>
                <div class="stats-card-value">{{ $kehadiranMingguIni }}</div>
                @if($kehadiranMingguLalu > 0)
                    @php $perbedaan = (($kehadiranMingguIni - $kehadiranMingguLalu) / $kehadiranMingguLalu) * 100; @endphp
                    <div class="stats-card-trend {{ $perbedaan >= 0 ? 'up' : 'down' }}">
                        <i class="fas fa-{{ $perbedaan >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs(round($perbedaan)) }}% dari minggu lalu
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Tren Kehadiran (4 Minggu Terakhir)
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="kehadiranChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar me-1"></i>
                    Kegiatan Mendatang
                </div>
                <div class="card-body">
                    @if(count($kegiatanMendatang) > 0)
                        @foreach($kegiatanMendatang as $kegiatan)
                            <div class="card kegiatan-card">
                                <div class="card-body">
                                    <div class="kegiatan-date">
                                        <i class="fas fa-calendar-day"></i>
                                        {{ \Carbon\Carbon::parse($kegiatan->tanggal_kegiatan)->format('d M Y') }}
                                    </div>
                                    <div class="kegiatan-title">{{ $kegiatan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui' }}</div>
                                    <div class="kegiatan-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $kegiatan->lokasi ?? 'Lokasi belum ditentukan' }}
                                    </div>
                                    <div class="kegiatan-time">
                                        <i class="fas fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($kegiatan->jam_mulai)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($kegiatan->jam_selesai)->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-center">Tidak ada kegiatan mendatang.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-birthday-cake me-1"></i>
                    Ulang Tahun Bulan Ini
                </div>
                <div class="card-body">
                    @if(count($ultahBulanIni) > 0)
                        @foreach($ultahBulanIni as $anggota)
                            <div class="card ultah-card">
                                <div class="card-body">
                                    <div class="ultah-name">{{ $anggota->nama }}</div>
                                    <div class="ultah-date">
                                        <i class="fas fa-calendar-day"></i>
                                        {{ \Carbon\Carbon::parse($anggota->tanggal_lahir)->format('d M') }}
                                    </div>
                                    <div class="ultah-age">
                                        <i class="fas fa-birthday-cake"></i>
                                        {{ \Carbon\Carbon::parse($anggota->tanggal_lahir)->age }} tahun
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-center">Tidak ada ulang tahun bulan ini.</p>
                    @endif
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
        // Kehadiran Chart
        const kehadiranCtx = document.getElementById('kehadiranChart').getContext('2d');
        const kehadiranData = @json($kehadiranPerMinggu);
        const kehadiranLabels = kehadiranData.map(item => item.minggu);
        const kehadiranValues = kehadiranData.map(item => item.jumlah);
        
        new Chart(kehadiranCtx, {
            type: 'line',
            data: {
                labels: kehadiranLabels,
                datasets: [{
                    label: 'Jumlah Kehadiran',
                    data: kehadiranValues,
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    borderColor: 'rgba(78, 115, 223, 1)',
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
        
        // Org Chart (Simple visualization)
        const orgCtx = document.getElementById('orgChart').getContext('2d');
        
        // Sample data - in a real app, you would fetch this from the database
        const orgData = {
            labels: ['Gembala', 'Wakil Gembala', 'Sekretaris', 'Bendahara', 'Bidang Ibadah', 'Bidang Misi', 'Bidang Pemuda', 'Bidang Anak'],
            datasets: [{
                label: 'Struktur Organisasi',
                data: [1, 1, 1, 1, 3, 2, 4, 5],
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(133, 135, 150, 0.8)',
                    'rgba(105, 0, 132, 0.8)',
                    'rgba(0, 181, 204, 0.8)'
                ],
                borderWidth: 1
            }]
        };
        
        new Chart(orgCtx, {
            type: 'bar',
            data: orgData,
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