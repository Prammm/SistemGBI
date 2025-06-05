@extends('layouts.app')

@section('title', 'Laporan Kehadiran Komsel')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<style>
    .komsel-header {
        background: linear-gradient(135deg, #f6c23e 0%, #f4b942 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(246, 194, 62, 0.3);
    }
    
    .komsel-selector {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f6c23e, #f4b942);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #f6c23e;
        margin-bottom: 10px;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .member-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .member-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 4px solid #f6c23e;
    }
    
    .member-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .member-info {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .member-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f6c23e 0%, #f4b942 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        font-weight: bold;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .member-details h6 {
        margin: 0;
        color: #333;
        font-weight: 600;
    }
    
    .member-contact {
        color: #6c757d;
        font-size: 0.85rem;
    }
    
    .attendance-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .attendance-percentage {
        font-size: 1.8rem;
        font-weight: bold;
        color: #f6c23e;
    }
    
    .attendance-count {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .progress-container {
        margin-bottom: 10px;
    }
    
    .progress {
        height: 8px;
        border-radius: 10px;
        background: #f8f9fa;
    }
    
    .progress-bar {
        border-radius: 10px;
        background: linear-gradient(90deg, #f6c23e, #f4b942);
    }
    
    .attendance-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        text-align: center;
    }
    
    .status-excellent {
        background: #d4edda;
        color: #155724;
    }
    
    .status-good {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-fair {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-poor {
        background: #f8d7da;
        color: #721c24;
    }
    
    .chart-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        position: relative;
        height: 400px;
    }
    
    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
        overflow: hidden;
    }
    
    .chart-wrapper canvas {
        max-width: 100% !important;
        max-height: 100% !important;
        width: auto !important;
        height: auto !important;
    }
    
    .activities-list {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .activity-item {
        padding: 20px;
        border-bottom: 1px solid #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-info h6 {
        margin: 0 0 5px 0;
        color: #333;
    }
    
    .activity-meta {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .activity-attendance {
        text-align: right;
    }
    
    .attendance-number {
        font-size: 1.2rem;
        font-weight: bold;
        color: #f6c23e;
    }
    
    .filter-section {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: end;
    }
    
    .export-buttons {
        text-align: center;
        margin-top: 30px;
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
    
    @media (max-width: 768px) {
        .komsel-header {
            padding: 20px;
        }
        
        .member-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-section {
            flex-direction: column;
        }
        
        .filter-section > div {
            width: 100%;
        }
        
        .chart-section {
            height: 350px;
        }
        
        .chart-wrapper {
            height: 250px;
        }
        
        .chart-wrapper canvas {
            max-width: 100% !important;
            max-height: 100% !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Kehadiran Komsel</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Laporan Komsel</li>
    </ol>
    
    <div class="komsel-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-users me-3"></i>{{ $selectedKomsel->nama_komsel }}</h2>
                <p class="mb-0">
                    <i class="fas fa-user-tie me-2"></i>Pemimpin: {{ $selectedKomsel->pemimpin->nama ?? 'Belum ditentukan' }}
                    &nbsp;•&nbsp;
                    <i class="fas fa-users me-2"></i>{{ $selectedKomsel->anggota->count() }} Anggota
                    &nbsp;•&nbsp;
                    <i class="fas fa-calendar me-2"></i>{{ $selectedKomsel->hari ?? 'Belum dijadwalkan' }}
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="h4 mb-0">Periode Laporan</div>
                <div>{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</div>
            </div>
        </div>
    </div>
    
    <div class="komsel-selector">
        <h5><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
        <form method="GET" action="{{ route('laporan.komsel-report') }}">
            <div class="filter-section">
                @if($komselLead->count() > 1)
                    <div class="flex-fill">
                        <label for="komsel_id" class="form-label">Pilih Komsel</label>
                        <select id="komsel_id" name="komsel_id" class="form-select">
                            @foreach($komselLead as $komsel)
                                <option value="{{ $komsel->id_komsel }}" {{ $selectedKomsel->id_komsel == $komsel->id_komsel ? 'selected' : '' }}>
                                    {{ $komsel->nama_komsel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div>
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-search me-2"></i>Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    @if($pelaksanaanKomsel->count() > 0)
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-number">{{ $pelaksanaanKomsel->count() }}</div>
                <div class="stat-label">Total Pertemuan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $kehadiran->count() }}</div>
                <div class="stat-label">Total Kehadiran</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    {{ $selectedKomsel->anggota->count() > 0 ? round((array_sum(array_column($attendanceStats, 'persentase')) / count($attendanceStats)), 1) : 0 }}%
                </div>
                <div class="stat-label">Rata-rata Kehadiran</div>
            </div>
        </div>
        
        @if(count($attendanceStats) > 0)
            <div class="member-grid">
                @foreach($attendanceStats as $stats)
                    @php
                        $percentage = $stats['persentase'];
                        if ($percentage >= 90) {
                            $statusClass = 'status-excellent';
                            $statusText = 'Sangat Baik';
                        } elseif ($percentage >= 75) {
                            $statusClass = 'status-good';
                            $statusText = 'Baik';
                        } elseif ($percentage >= 75) {
                            $statusClass = 'status-fair';
                            $statusText = 'Cukup';
                        } else {
                            $statusClass = 'status-poor';
                            $statusText = 'Kurang';
                        }
                    @endphp
                    
                    <div class="member-card">
                        <div class="member-info">
                            <div class="member-avatar">
                                {{ strtoupper(substr($stats['anggota']->nama, 0, 1)) }}
                            </div>
                            <div class="member-details">
                                <h6>{{ $stats['anggota']->nama }}</h6>
                                <div class="member-contact">
                                    @if($stats['anggota']->no_telepon)
                                        <i class="fas fa-phone me-1"></i>{{ $stats['anggota']->no_telepon }}
                                    @else
                                        <i class="fas fa-user me-1"></i>Anggota Komsel
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="attendance-stats">
                            <div class="attendance-percentage">{{ $percentage }}%</div>
                            <div class="attendance-count">
                                {{ $stats['total_kehadiran'] }}/{{ $stats['total_kegiatan'] }} hadir
                            </div>
                        </div>
                        
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        
                        <div class="attendance-status {{ $statusClass }}">
                            {{ $statusText }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-section">
                    <h5><i class="fas fa-chart-line me-2"></i>Tren Kehadiran per Pertemuan</h5>
                    <div class="chart-wrapper">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-section">
                    <h5><i class="fas fa-chart-bar me-2"></i>Distribusi Kehadiran Anggota</h5>
                    <div class="chart-wrapper">
                        <canvas id="memberDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="activities-list">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Riwayat Pertemuan Komsel
                </h5>
            </div>
            <div class="card-body p-0">
                @foreach($pelaksanaanKomsel as $pertemuan)
                    @php
                        $pertemuanKehadiran = $kehadiran->where('id_pelaksanaan', $pertemuan->id_pelaksanaan);
                        $attendanceCount = $pertemuanKehadiran->count();
                        $totalMembers = $selectedKomsel->anggota->count();
                    @endphp
                    
                    <div class="activity-item">
                        <div class="activity-info">
                            <h6>Pertemuan {{ \Carbon\Carbon::parse($pertemuan->tanggal_kegiatan)->format('d F Y') }}</h6>
                            <div class="activity-meta">
                                <i class="fas fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($pertemuan->jam_mulai)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($pertemuan->jam_selesai)->format('H:i') }}
                                @if($pertemuan->lokasi)
                                    &nbsp;•&nbsp;
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $pertemuan->lokasi }}
                                @endif
                            </div>
                        </div>
                        <div class="activity-attendance">
                            <div class="attendance-number">{{ $attendanceCount }}/{{ $totalMembers }}</div>
                            <div class="text-muted small">
                                {{ $totalMembers > 0 ? round(($attendanceCount / $totalMembers) * 100) : 0 }}% hadir
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="activities-list">
            <div class="no-data-message">
                <i class="fas fa-calendar-times"></i>
                <h5>Tidak Ada Data Pertemuan</h5>
                <p>Belum ada pertemuan komsel yang terjadwal dalam periode yang dipilih.</p>
                <a href="{{ route('komsel.show', $selectedKomsel->id_komsel) }}" class="btn btn-warning">
                    <i class="fas fa-calendar-plus me-2"></i>Jadwalkan Pertemuan
                </a>
            </div>
        </div>
    @endif
    
    <div class="export-buttons">
        <button onclick="window.print()" class="btn btn-outline-warning">
            <i class="fas fa-print me-2"></i>Cetak Laporan
        </button>
        <a href="{{ route('laporan.index') }}" class="btn btn-secondary ms-2">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variabel untuk menyimpan instance chart
    let attendanceTrendChart = null;
    let memberDistributionChart = null;
    
    // Function untuk menghancurkan chart yang sudah ada
    function destroyExistingCharts() {
        if (attendanceTrendChart) {
            attendanceTrendChart.destroy();
            attendanceTrendChart = null;
        }
        if (memberDistributionChart) {
            memberDistributionChart.destroy();
            memberDistributionChart = null;
        }
    }
    
    @if($pelaksanaanKomsel->count() > 0)
        // Attendance trend chart
        const trendCtx = document.getElementById('attendanceTrendChart');
        if (trendCtx) {
            const ctx = trendCtx.getContext('2d');
            const meetings = @json($pelaksanaanKomsel->map(function($p) use ($kehadiran) {
                return [
                    'date' => $p->tanggal_kegiatan,
                    'attendance' => $kehadiran->where('id_pelaksanaan', $p->id_pelaksanaan)->count()
                ];
            }));
            
            const trendLabels = meetings.map(m => {
                const date = new Date(m.date);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            });
            const trendData = meetings.map(m => m.attendance);
            
            attendanceTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Jumlah Kehadiran',
                        data: trendData,
                        backgroundColor: 'rgba(246, 194, 62, 0.1)',
                        borderColor: 'rgba(246, 194, 62, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(246, 194, 62, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: {{ $selectedKomsel->anggota->count() }},
                            ticks: {
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        
        // Member distribution chart
        const distributionCtx = document.getElementById('memberDistributionChart');
        if (distributionCtx) {
            const ctx = distributionCtx.getContext('2d');
            const attendanceStats = @json($attendanceStats);
            
            const memberNames = Object.values(attendanceStats).map(stat => stat.anggota.nama.split(' ')[0]);
            const memberPercentages = Object.values(attendanceStats).map(stat => stat.persentase);
            
            // Generate colors based on performance
            const memberColors = memberPercentages.map(percentage => {
                if (percentage >= 90) return 'rgba(40, 167, 69, 0.8)';
                if (percentage >= 75) return 'rgba(54, 185, 204, 0.8)';
                if (percentage >= 50) return 'rgba(246, 194, 62, 0.8)';
                return 'rgba(220, 53, 69, 0.8)';
            });
            
            memberDistributionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: memberNames,
                    datasets: [{
                        label: 'Persentase Kehadiran',
                        data: memberPercentages,
                        backgroundColor: memberColors,
                        borderColor: memberColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    @endif
    
    // Cleanup saat halaman akan ditinggalkan
    window.addEventListener('beforeunload', function() {
        destroyExistingCharts();
    });
});
</script>
@endsection