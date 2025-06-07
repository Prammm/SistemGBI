@extends('layouts.app')

@section('title', 'Laporan')

@section('styles')
<style>
    .report-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
    .report-icon {
        font-size: 3rem;
        color: white;
    }
    .report-header {
        padding: 20px;
        color: white;
        position: relative;
    }
    .report-body {
        padding: 20px;
        background-color: white;
    }
    .report-title {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .kehadiran {
        background-color: #4e73df;
    }
    .pelayanan {
        background-color: #1cc88a;
    }
    .komsel {
        background-color: #f6c23e;
    }
    .anggota {
        background-color: #e74a3b;
    }
    .dashboard {
        background-color: #36b9cc;
    }
    .no-reports-message {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    .no-reports-message i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }
    .role-info {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        color: #1976d2;
    }
    .user-select-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 500;
    }
    .report-sections {
        margin-bottom: 30px;
    }
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f8f9fa;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Laporan</li>
    </ol>

    @if(Auth::user()->id_role <= 2)
        <div class="role-info">
            <i class="fas fa-crown me-2"></i>
            <strong>Administrator/Pengurus Gereja:</strong> Anda memiliki akses ke semua laporan sistem dan dapat melihat laporan pribadi anggota lain.
        </div>
    @elseif(Auth::user()->id_role == 3)
        <div class="role-info">
            <i class="fas fa-user-cog me-2"></i>
            <strong>Petugas Pelayanan:</strong> Anda dapat melihat laporan kehadiran umum, pelayanan (termasuk anggota lain), kehadiran pribadi, dan komsel (jika Anda pemimpin).
        </div>
    @elseif(Auth::user()->id_role == 4)
        <div class="role-info">
            <i class="fas fa-user me-2"></i>
            <strong>Anggota Jemaat:</strong> Anda dapat melihat laporan kehadiran pribadi, riwayat pelayanan, dan komsel (jika Anda pemimpin komsel).
        </div>
    @endif

    @if(count($availableReports) > 0)
        @php
            // Group reports by type
            $systemReports = [];
            $personalReports = [];
            
            foreach($availableReports as $key => $report) {
                if(in_array($key, ['kehadiran', 'pelayanan', 'komsel', 'anggota', 'dashboard'])) {
                    $systemReports[$key] = $report;
                } else {
                    $personalReports[$key] = $report;
                }
            }
        @endphp
        
        @if(!empty($systemReports))
            <div class="report-sections">
                <h4 class="section-title">
                    <i class="fas fa-chart-bar me-2"></i>Laporan Sistem
                </h4>
                <div class="row">
                    @foreach($systemReports as $key => $report)
                        <div class="col-xl-4 col-md-6">
                            <div class="report-card">
                                <div class="report-header {{ $report['color'] }}">
                                    <div class="row">
                                        <div class="col-3">
                                            <i class="fas {{ $report['icon'] }} report-icon"></i>
                                        </div>
                                        <div class="col-9 text-right">
                                            <div class="report-title">{{ $report['title'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="report-body">
                                    <p>{{ $report['description'] }}</p>
                                    <a href="{{ route($report['route']) }}" class="btn btn-primary btn-sm">Lihat Laporan</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if(!empty($personalReports))
            <div class="report-sections">
                <h4 class="section-title">
                    <i class="fas fa-user-chart me-2"></i>Laporan Pribadi
                </h4>
                <div class="row">
                    @foreach($personalReports as $key => $report)
                        <div class="col-xl-4 col-md-6">
                            <div class="report-card">
                                <div class="report-header {{ $report['color'] }}">
                                    @if(isset($report['can_select_user']) && $report['can_select_user'])
                                        <div class="user-select-badge">
                                            <i class="fas fa-users me-1"></i>Multi User
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-3">
                                            <i class="fas {{ $report['icon'] }} report-icon"></i>
                                        </div>
                                        <div class="col-9 text-right">
                                            <div class="report-title">{{ $report['title'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="report-body">
                                    <p>{{ $report['description'] }}</p>
                                    <a href="{{ route($report['route']) }}" class="btn btn-primary btn-sm">Lihat Laporan</a>
                                    @if(isset($report['can_select_user']) && $report['can_select_user'])
                                        <small class="d-block mt-2 text-muted">
                                            <i class="fas fa-info-circle me-1"></i>Dapat memilih anggota lain
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="no-reports-message">
            <i class="fas fa-chart-bar"></i>
            <h5>Tidak Ada Laporan Tersedia</h5>
            <p>Saat ini tidak ada laporan yang dapat Anda akses berdasarkan role Anda.</p>
            <p class="small text-muted">Hubungi administrator jika Anda memerlukan akses ke laporan tertentu.</p>
        </div>
    @endif

    @if(Auth::user()->id_role == 4)
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-lightbulb me-2"></i>Tips untuk Anggota Jemaat
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-user-check me-2"></i>Laporan Kehadiran Pribadi</h6>
                        <p class="small">Pantau riwayat kehadiran Anda pada ibadah umum, komsel, dan kegiatan gereja lainnya dalam berbagai periode.</p>
                    </div>
                    @if(Auth::user()->id_anggota)
                        @php
                            $anggota = \App\Models\Anggota::find(Auth::user()->id_anggota);
                            $hasService = \App\Models\JadwalPelayanan::where('id_anggota', $anggota->id_anggota)->exists();
                            $isKomselLeader = \App\Models\Komsel::where('id_pemimpin', $anggota->id_anggota)->exists();
                        @endphp
                        
                        @if($hasService)
                            <div class="col-md-6">
                                <h6><i class="fas fa-hand-holding-heart me-2"></i>Riwayat Pelayanan</h6>
                                <p class="small">Lihat statistik dan riwayat pelayanan Anda di berbagai kegiatan gereja dengan chart interaktif.</p>
                            </div>
                        @endif
                        
                        @if($isKomselLeader)
                            <div class="col-md-6">
                                <h6><i class="fas fa-users me-2"></i>Laporan Komsel</h6>
                                <p class="small">Pantau kehadiran dan aktivitas anggota komsel yang Anda pimpin dengan analisis mendalam.</p>
                            </div>
                        @endif
                    @endif
                </div>
                
                @if(!Auth::user()->id_anggota)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Profil Tidak Lengkap:</strong> Untuk mengakses laporan pribadi, pastikan profil anggota Anda sudah terhubung dengan akun ini. Hubungi administrator untuk bantuan.
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(Auth::user()->id_role == 3)
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Informasi untuk Petugas Pelayanan
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6><i class="fas fa-clipboard-check me-2"></i>Laporan Kehadiran</h6>
                        <p class="small">Akses laporan kehadiran umum untuk semua kegiatan gereja dengan filter periode.</p>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="fas fa-hands-helping me-2"></i>Laporan Pelayanan</h6>
                        <p class="small">Kelola dan pantau jadwal pelayanan serta kinerja pelayan. Dapat memilih anggota lain.</p>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="fas fa-user-check me-2"></i>Kehadiran Pribadi</h6>
                        <p class="small">Pantau kehadiran pribadi Anda dengan analisis tren dan statistik.</p>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="fas fa-users me-2"></i>Laporan Komsel</h6>
                        <p class="small">Khusus untuk pemimpin komsel, lihat aktivitas dan kehadiran anggota komsel.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif


</div>
@endsection