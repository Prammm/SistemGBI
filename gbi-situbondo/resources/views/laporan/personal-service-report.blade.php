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
        position: relative;
        height: 400px;
    }
    
    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f8f9fa;
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
    
    .service-list {
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
        border-color: #1cc88a;
        outline: none;
        box-shadow: 0 0 0 3px rgba(28, 200, 138, 0.1);
    }
    
    .user-selector-form .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        white-space: nowrap;
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
    
    .info-note {
        background: #e8f5e8;
        border: 1px solid #c3e6c3;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 20px;
        color: #2d5a2d;
        font-size: 0.9rem;
    }
    
    .info-note .fas {
        margin-right: 8px;
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
        background: #1cc88a;
        border-color: #1cc88a;
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
        background-color: rgba(28, 200, 138, 0.05);
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.5em 0.75em;
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
        
        .chart-container {
            height: 350px;
        }
        
        .chart-wrapper {
            height: 250px;
        }
        
        .chart-wrapper canvas {
            max-width: 100% !important;
            max-height: 100% !important;
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
    <h1 class="mt-4">Riwayat Pelayanan Pribadi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Riwayat Pelayanan</li>
    </ol>

    <!-- Info Note -->
    <div class="info-note">
        <i class="fas fa-info-circle"></i>
        <strong>Catatan:</strong> Statistik pelayanan hanya menghitung jadwal dengan status "Diterima". 
        Namun, semua riwayat (termasuk yang ditolak atau menunggu konfirmasi) tetap ditampilkan dalam tabel.
        Jadwal yang sudah lewat tanggal akan otomatis berubah status menjadi "Ditolak" jika belum dikonfirmasi.
    </div>
    
    <div class="report-header">
        {{-- User Selection for Admin/Pengurus/Petugas Pelayanan --}}
        @if($canSelectUser && $usersWithService->count() > 0)
            <div class="user-selector-card">
                <div class="user-selector-title">
                    <i class="fas fa-users"></i>
                    <span>Pilih Pelayan untuk Melihat Laporan</span>
                </div>
                <form method="GET" action="{{ route('laporan.personal-service-report') }}" class="user-selector-form">
                    <div class="form-group">
                        <label for="user_id">Pilih Pelayan:</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">-- Pilih Pelayan --</option>
                            @foreach($usersWithService as $user)
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
                    <div><i class="fas fa-hands-helping me-2"></i>Riwayat Pelayanan {{ $canSelectUser && $selectedUserId ? 'Supervisori' : 'Pribadi' }}</div>
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
            <form method="GET" action="{{ route('laporan.personal-service-report') }}" id="filterForm">
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
                        <label for="status_filter" class="form-label">Status Konfirmasi</label>
                        <select id="status_filter" name="status_filter" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="terima" {{ request('status_filter') == 'terima' ? 'selected' : '' }}>Diterima</option>
                            <option value="tolak" {{ request('status_filter') == 'tolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="belum" {{ request('status_filter') == 'belum' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-success">
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
                <div class="stats-label">Total Pelayanan</div>
                <div class="stats-number">{{ $totalPelayanan }}</div>
                <div class="stats-description">
                    Pelayanan yang diterima dalam {{ round($startDate->floatDiffInMonths($endDate)) }} bulan terakhir
                    @if($kegiatan_id)
                        <br><small class="text-muted">
                            Filter: {{ $kegiatanList->where('id_kegiatan', $kegiatan_id)->first()->nama_kegiatan ?? 'Kegiatan Tidak Ditemukan' }}
                        </small>
                    @endif
                    <br><small style="opacity: 0.8;">Hanya yang diterima</small>
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
                    Pelayanan bulanan {{ $canSelectUser && $selectedUserId ? $anggota->nama : 'Anda' }}
                    <br><small style="opacity: 0.8;">Yang diterima</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">Posisi Terfavorit</div>
                <div class="stats-number">{{ $pelayananPerPosisi->count() > 0 ? Str::limit($pelayananPerPosisi->keys()->first(), 15) : '-' }}</div>
                <div class="stats-description">
                    {{ $pelayananPerPosisi->count() > 0 ? $pelayananPerPosisi->first() . ' kali melayani' : 'Belum ada data' }}
                    <br><small style="opacity: 0.8;">Yang diterima</small>
                </div>
            </div>
        </div>
    </div>
    
    @if($totalPelayanan > 0)
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-line me-2"></i>Tren Pelayanan per Bulan (Yang Diterima)
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie me-2"></i>Pelayanan per Posisi (Yang Diterima)
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="positionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif
        
    <div class="service-list">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Pelayanan
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
        <div class="card-body">
            @if($jadwalPelayanan->count() > 0)
                <!-- Export Buttons -->
                @if(!$canSelectUser || !$selectedUserId)
                    <div class="export-buttons p-3">
                        <a href="{{ route('laporan.export', ['jenis' => 'personal-service-report', 'format' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                        class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'personal-service-report', 'format' => 'excel']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
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
                        <a href="{{ route('laporan.export', ['jenis' => 'personal-service-report', 'format' => 'pdf']) }}?{{ $exportQuery }}" 
                        class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'personal-service-report', 'format' => 'excel']) }}?{{ $exportQuery }}" 
                        class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                @endif
                
                <!-- DataTable -->
                <div class="table-responsive">
                    <table id="pelayananTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="120">Tanggal</th>
                                <th>Kegiatan</th>
                                <th width="150">Posisi</th>
                                <th width="120">Status</th>
                                <th width="100">Waktu</th>
                                <th width="200">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwalPelayanan as $pelayanan)
                                @php
                                    $isExpired = \Carbon\Carbon::parse($pelayanan->tanggal_pelayanan)->isPast() && $pelayanan->status_konfirmasi === 'belum';
                                    $wasAutoRejected = \Carbon\Carbon::parse($pelayanan->tanggal_pelayanan)->isPast() && $pelayanan->status_konfirmasi === 'tolak';
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pelayanan->tanggal_pelayanan)->format('d-m-Y') }}</td>
                                    <td>
                                        <strong>{{ $pelayanan->kegiatan->nama_kegiatan ?? 'Kegiatan Tidak Diketahui' }}</strong>
                                        @if($pelayanan->kegiatan)
                                            <br><small class="text-muted badge bg-light text-dark">
                                                {{ ucfirst($pelayanan->kegiatan->tipe_kegiatan) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $pelayanan->posisi ?? 'Tidak Diketahui' }}</span>
                                    </td>
                                    <td>
                                        @switch($pelayanan->status_konfirmasi)
                                            @case('terima')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Diterima
                                                </span>
                                                @break
                                            @case('tolak')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Ditolak
                                                </span>
                                                @if($wasAutoRejected)
                                                    <br><small class="text-muted">Auto-reject</small>
                                                @endif
                                                @break
                                            @case('belum')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>Menunggu
                                                </span>
                                                @if($isExpired)
                                                    <br><small class="text-danger">Akan auto-reject</small>
                                                @endif
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($pelayanan->status_konfirmasi) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($pelayanan->pelaksanaan)
                                            <small>
                                                {{ \Carbon\Carbon::parse($pelayanan->pelaksanaan->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($pelayanan->pelaksanaan->jam_selesai)->format('H:i') }}
                                            </small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $pelayanan->keterangan ?? '-' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data-message">
                    <i class="fas fa-hand-holding-heart"></i>
                    <h5>Belum Ada Riwayat Pelayanan</h5>
                    <p>{{ $canSelectUser && $selectedUserId ? $anggota->nama : 'Anda' }} belum memiliki riwayat pelayanan dalam {{ round($startDate->floatDiffInMonths($endDate)) }} bulan terakhir.</p>
                    @if(!$canSelectUser || !$selectedUserId)
                        @if(Route::has('pelayanan.index'))
                            <a href="{{ route('pelayanan.index') }}" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Lihat Jadwal Pelayanan
                            </a>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    @if($jadwalPelayanan->count() > 0)
        const pelayananTable = new simpleDatatables.DataTable("#pelayananTable", {
            searchable: true,
            sortable: true,
            paging: true,
            perPage: 25,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Cari kegiatan, posisi, atau status...",
                perPage: "data per halaman",
                noRows: "Tidak ada data pelayanan",
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
    
    // Variabel untuk menyimpan instance chart
    let monthlyChart = null;
    let positionChart = null;
    
    // Function untuk menghancurkan chart yang sudah ada
    function destroyExistingCharts() {
        if (monthlyChart) {
            monthlyChart.destroy();
            monthlyChart = null;
        }
        if (positionChart) {
            positionChart.destroy();
            positionChart = null;
        }
    }
    
    @if($totalPelayanan > 0)
        // Monthly service chart
        const monthlyCtx = document.getElementById('monthlyChart');
        if (monthlyCtx) {
            const ctx = monthlyCtx.getContext('2d');
            const monthlyData = @json($pelayananPerBulan);
            
            const monthlyLabels = [];
            const monthlyValues = [];
            
            // Generate labels for all months in the period
            const startDate = new Date('{{ $startDate->format('Y-m-d') }}');
            const endDate = new Date('{{ $endDate->format('Y-m-d') }}');
            
            let currentDate = new Date(startDate);
            currentDate.setDate(1); // Set to first day of month
            
            while (currentDate <= endDate) {
                const monthKey = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');
                const monthLabel = currentDate.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
                
                monthlyLabels.push(monthLabel);
                monthlyValues.push(monthlyData[monthKey] || 0);
                
                // Move to next month
                currentDate.setMonth(currentDate.getMonth() + 1);
            }
            
            monthlyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Pelayanan Diterima',
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
        }
        
        // Position chart
        const positionCtx = document.getElementById('positionChart');
        if (positionCtx) {
            const ctx = positionCtx.getContext('2d');
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
            
            positionChart = new Chart(ctx, {
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
        }
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
    
    // Cleanup saat halaman akan ditinggalkan
    window.addEventListener('beforeunload', function() {
        destroyExistingCharts();
    });
});
</script>
@endsection