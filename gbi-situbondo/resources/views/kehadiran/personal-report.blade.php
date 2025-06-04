@extends('layouts.app')

@section('title', 'Laporan Kehadiran Pribadi')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
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
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f8f9fa;
    }
    
    .attendance-list {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .attendance-item {
        padding: 20px;
        border-bottom: 1px solid #f8f9fa;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .attendance-item:hover {
        background: #f8f9fa;
    }
    
    .attendance-item:last-child {
        border-bottom: none;
    }
    
    .attendance-info {
        flex: 1;
    }
    
    .attendance-event {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .attendance-details {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .attendance-badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-hadir {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .filter-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .period-selector {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .period-btn {
        padding: 8px 16px;
        border: 2px solid #e9ecef;
        background: white;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .period-btn.active,
    .period-btn:hover {
        border-color: #667eea;
        background: #667eea;
        color: white;
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
        
        .period-selector {
            justify-content: center;
        }
        
        .period-btn {
            flex: 1;
            min-width: 80px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Kehadiran Pribadi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kehadiran.index') }}">Presensi Kehadiran</a></li>
        <li class="breadcrumb-item active">Laporan Pribadi</li>
    </ol>
    
    <div class="report-header">
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
                </div>
            </div>
        </div>
    </div>
    
    <div class="filter-card">
        <h5><i class="fas fa-filter me-2"></i>Filter Periode</h5>
        <div class="period-selector">
            <button class="period-btn" data-period="1">1 Bulan</button>
            <button class="period-btn" data-period="3">3 Bulan</button>
            <button class="period-btn active" data-period="6">6 Bulan</button>
            <button class="period-btn" data-period="12">1 Tahun</button>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">Total Kehadiran</div>
                <div class="stats-number">{{ $totalKehadiran }}</div>
                <div class="stats-description">
                    Dalam {{ $startDate->diffInMonths($endDate) }} bulan terakhir
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
                    Kehadiran bulanan Anda
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">Kegiatan Terfavorit</div>
                <div class="stats-number">{{ $kehadiranPerKegiatan->count() > 0 ? $kehadiranPerKegiatan->keys()->first() : '-' }}</div>
                <div class="stats-description">
                    {{ $kehadiranPerKegiatan->count() > 0 ? $kehadiranPerKegiatan->first() . ' kali hadir' : 'Belum ada data' }}
                </div>
            </div>
        </div>
    </div>
    
    @if($totalKehadiran > 0)
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-line me-2"></i>Tren Kehadiran per Bulan
                    </div>
                    <canvas id="monthlyChart" width="400" height="300"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie me-2"></i>Kehadiran per Jenis Kegiatan
                    </div>
                    <canvas id="activityChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="attendance-list">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Riwayat Kehadiran Terbaru
                </h5>
            </div>
            <div class="card-body p-0">
                @foreach($kehadiran->take(10) as $attendance)
                    <div class="attendance-item">
                        <div class="attendance-info">
                            <div class="attendance-event">
                                {{ $attendance->pelaksanaan->kegiatan->nama_kegiatan ?? 'Kegiatan Tidak Diketahui' }}
                            </div>
                            <div class="attendance-details">
                                <i class="fas fa-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($attendance->waktu_absensi)->format('d F Y') }}
                                &nbsp;•&nbsp;
                                <i class="fas fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($attendance->waktu_absensi)->format('H:i') }}
                                @if($attendance->pelaksanaan->lokasi)
                                    &nbsp;•&nbsp;
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $attendance->pelaksanaan->lokasi }}
                                @endif
                            </div>
                        </div>
                        <div class="attendance-badge badge-hadir">
                            <i class="fas fa-check me-1"></i>{{ ucfirst($attendance->status) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="attendance-list">
            <div class="no-data-message">
                <i class="fas fa-calendar-times"></i>
                <h5>Belum Ada Data Kehadiran</h5>
                <p>Anda belum memiliki catatan kehadiran dalam 6 bulan terakhir.</p>
                <a href="{{ route('kehadiran.index') }}" class="btn btn-primary">
                    <i class="fas fa-qrcode me-2"></i>Mulai Presensi
                </a>
            </div>
        </div>
    @endif
    
    <div class="text-center mt-4">
        <a href="{{ route('kehadiran.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary ms-2">
            <i class="fas fa-print me-2"></i>Cetak Laporan
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($totalKehadiran > 0)
        // Monthly attendance chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = @json($kehadiranPerBulan);
        
        const monthlyLabels = [];
        const monthlyValues = [];
        
        // Generate labels for all months in the period
        const startDate = new Date('{{ $startDate->format('Y-m-d') }}');
        const endDate = new Date('{{ $endDate->format('Y-m-d') }}');
        
        for (let d = new Date(startDate); d <= endDate; d.setMonth(d.getMonth() + 1)) {
            const monthKey = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            const monthLabel = d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
            
            monthlyLabels.push(monthLabel);
            monthlyValues.push(monthlyData[monthKey] || 0);
        }
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Kehadiran',
                    data: monthlyValues,
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(102, 126, 234, 1)',
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
        
        // Activity type chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityData = @json($kehadiranPerKegiatan);
        
        const activityLabels = Object.keys(activityData);
        const activityValues = Object.values(activityData);
        
        // Generate colors for activities
        const colors = [
            'rgba(102, 126, 234, 0.8)',
            'rgba(118, 75, 162, 0.8)',
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 205, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)'
        ];
        
        new Chart(activityCtx, {
            type: 'doughnut',
            data: {
                labels: activityLabels,
                datasets: [{
                    data: activityValues,
                    backgroundColor: colors.slice(0, activityLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    @endif
    
    // Period selector
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            const url = new URL(window.location);
            url.searchParams.set('period', period);
            window.location.href = url.toString();
        });
    });
    
    // Set active period based on URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const currentPeriod = urlParams.get('period') || '6';
    
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.period === currentPeriod) {
            btn.classList.add('active');
        }
    });
});
</script>
@endsection