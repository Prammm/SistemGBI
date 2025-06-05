@extends('layouts.app')

@section('title', 'Riwayat Pelayanan Pribadi')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<style>
    .report-header {
        background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(28, 200, 138, 0.3);
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        border-left: 5px solid #1cc88a;
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #1cc88a;
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
    
    .service-list {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .service-item {
        padding: 20px;
        border-bottom: 1px solid #f8f9fa;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .service-item:hover {
        background: #f8f9fa;
    }
    
    .service-item:last-child {
        border-bottom: none;
    }
    
    .service-info {
        flex: 1;
    }
    
    .service-event {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .service-details {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .service-badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-terima {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .badge-belum {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .badge-tolak {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f1c6c8;
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
        border-color: #1cc88a;
        background: #1cc88a;
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
        background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
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
    <h1 class="mt-4">Riwayat Pelayanan Pribadi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Riwayat Pelayanan</li>
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
                    <div><i class="fas fa-hands-helping me-2"></i>Riwayat Pelayanan Pribadi</div>
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
                <div class="stats-label">Total Pelayanan</div>
                <div class="stats-number">{{ $totalPelayanan }}</div>
                <div class="stats-description">
                    Dalam {{ $startDate->diffInMonths($endDate) }} bulan terakhir
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">Rata-rata per Bulan</div>
                <div class="stats-number">
                    {{ $pelayananPerBulan->count() > 0 ? round($pelayananPerBulan->avg(), 1) : 0 }}
                </div>
                <div class="stats-description">
                    Pelayanan bulanan Anda
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">Posisi Terfavorit</div>
                <div class="stats-number">{{ $pelayananPerPosisi->count() > 0 ? $pelayananPerPosisi->keys()->first() : '-' }}</div>
                <div class="stats-description">
                    {{ $pelayananPerPosisi->count() > 0 ? $pelayananPerPosisi->first() . ' kali melayani' : 'Belum ada data' }}
                </div>
            </div>
        </div>
    </div>
    
    @if($totalPelayanan > 0)
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-line me-2"></i>Tren Pelayanan per Bulan
                    </div>
                    <canvas id="monthlyChart" width="400" height="300"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie me-2"></i>Pelayanan per Posisi
                    </div>
                    <canvas id="positionChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="service-list">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Riwayat Pelayanan Terbaru
                </h5>
            </div>
            <div class="card-body p-0">
                @foreach($jadwalPelayanan->take(10) as $pelayanan)
                    <div class="service-item">
                        <div class="service-info">
                            <div class="service-event">
                                {{ $pelayanan->kegiatan->nama_kegiatan ?? 'Kegiatan Tidak Diketahui' }}
                            </div>
                            <div class="service-details">
                                <i class="fas fa-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($pelayanan->tanggal_pelayanan)->format('d F Y') }}
                                &nbsp;•&nbsp;
                                <i class="fas fa-user-tag me-1"></i>
                                {{ $pelayanan->posisi }}
                            </div>
                        </div>
                        <div class="service-badge 
                            @if($pelayanan->status_konfirmasi == 'terima') badge-terima
                            @elseif($pelayanan->status_konfirmasi == 'tolak') badge-tolak
                            @else badge-belum
                            @endif">
                            @if($pelayanan->status_konfirmasi == 'terima')
                                <i class="fas fa-check me-1"></i>Diterima
                            @elseif($pelayanan->status_konfirmasi == 'tolak')
                                <i class="fas fa-times me-1"></i>Ditolak
                            @else
                                <i class="fas fa-clock me-1"></i>Menunggu
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="service-list">
            <div class="no-data-message">
                <i class="fas fa-hand-holding-heart"></i>
                <h5>Belum Ada Riwayat Pelayanan</h5>
                <p>Anda belum memiliki riwayat pelayanan dalam {{ $startDate->diffInMonths($endDate) }} bulan terakhir.</p>
                <a href="{{ route('pelayanan.index') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Lihat Jadwal Pelayanan
                </a>
            </div>
        </div>
    @endif
    
    <div class="text-center mt-4">
        <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <button onclick="window.print()" class="btn btn-outline-success ms-2">
            <i class="fas fa-print me-2"></i>Cetak Laporan
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($totalPelayanan > 0)
        // Monthly service chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = @json($pelayananPerBulan);
        
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
                    label: 'Pelayanan',
                    data: monthlyValues,
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(28, 200, 138, 1)',
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
        
        // Position chart
        const positionCtx = document.getElementById('positionChart').getContext('2d');
        const positionData = @json($pelayananPerPosisi);
        
        const positionLabels = Object.keys(positionData);
        const positionValues = Object.values(positionData);
        
        // Generate colors for positions
        const colors = [
            'rgba(28, 200, 138, 0.8)',
            'rgba(78, 115, 223, 0.8)',
            'rgba(246, 194, 62, 0.8)',
            'rgba(231, 74, 59, 0.8)',
            'rgba(54, 185, 204, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(75, 192, 192, 0.8)'
        ];
        
        new Chart(positionCtx, {
            type: 'doughnut',
            data: {
                labels: positionLabels,
                datasets: [{
                    data: positionValues,
                    backgroundColor: colors.slice(0, positionLabels.length),
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