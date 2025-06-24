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
        display: grid;
        grid-template-columns: 1fr 1fr auto auto;
        gap: 15px;
        align-items: end;
    }
    
    .user-selector-form .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .user-selector-form label {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.9rem;
        margin-bottom: 8px;
    }
    
    .user-selector-form select {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        padding: 10px 15px;
        color: #333;
    }
    
    .user-selector-form select:focus {
        background: white;
        border-color: #f6c23e;
        outline: none;
        box-shadow: 0 0 0 3px rgba(246, 194, 62, 0.1);
    }
    
    .user-selector-form .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        white-space: nowrap;
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
        background: #f6c23e;
        border-color: #f6c23e;
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
        background-color: rgba(246, 194, 62, 0.05);
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.5em 0.75em;
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
        
        .user-selector-form {
            grid-template-columns: 1fr;
            gap: 15px;
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
        {{-- User Selection for Admin/Pengurus ONLY --}}
        @if($canSelectUser && $komselLeaders->count() > 0)
            <div class="user-selector-card">
                <div class="user-selector-title">
                    <i class="fas fa-users"></i>
                    <span>Pilih Pemimpin Komsel untuk Melihat Laporan</span>
                </div>
                <form method="GET" action="{{ route('laporan.komsel-report') }}" class="user-selector-form" id="komselForm">
                    <div class="form-group">
                        <label for="user_id">Pilih Pemimpin Komsel:</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">-- Pilih Pemimpin Komsel --</option>
                            @php
                                $komselOptions = [];
                                foreach($komselLeaders as $user) {
                                    $userKomsels = \App\Models\Komsel::where('id_pemimpin', $user->anggota->id_anggota)->get();
                                    foreach($userKomsels as $komsel) {
                                        $komselOptions[] = [
                                            'user_id' => $user->id,
                                            'komsel_id' => $komsel->id_komsel,
                                            'user_name' => $user->anggota->nama ?? $user->name,
                                            'komsel_name' => $komsel->nama_komsel
                                        ];
                                    }
                                }
                            @endphp
                            @foreach($komselOptions as $option)
                                @php
                                    $isSelected = false;
                                    // Jika admin memilih user dan komsel spesifik
                                    if ($selectedUserId == $option['user_id'] && request('komsel_id') == $option['komsel_id']) {
                                        $isSelected = true;
                                    } 
                                    // Jika user dipilih tapi tidak ada komsel spesifik, pilih komsel pertama user tersebut
                                    elseif ($selectedUserId == $option['user_id'] && !request('komsel_id')) {
                                        $userFirstKomsel = \App\Models\Komsel::where('id_pemimpin', \App\Models\User::find($option['user_id'])->anggota->id_anggota)->first();
                                        if ($userFirstKomsel && $userFirstKomsel->id_komsel == $option['komsel_id']) {
                                            $isSelected = true;
                                        }
                                    }
                                @endphp
                                <option value="{{ $option['user_id'] }}" 
                                        data-komsel-id="{{ $option['komsel_id'] }}"
                                        {{ $isSelected ? 'selected' : '' }}>
                                    {{ $option['komsel_name'] }} - {{ $option['user_name'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="komsel_id" name="komsel_id" value="">
                    </div>
                    <div class="form-group">
                        <label for="period_admin">Periode:</label>
                        <select id="period_admin" name="period" class="form-select">
                            <option value="1" {{ $period == 1 ? 'selected' : '' }}>1 Bulan</option>
                            <option value="3" {{ $period == 3 ? 'selected' : '' }}>3 Bulan</option>
                            <option value="6" {{ $period == 6 ? 'selected' : '' }}>6 Bulan</option>
                            <option value="12" {{ $period == 12 ? 'selected' : '' }}>1 Tahun</option>
                            <option value="custom" {{ !in_array($period, [1, 3, 6, 12]) ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                    <div class="form-group" id="customAdminDate" style="{{ !in_array($period, [1, 3, 6, 12]) ? '' : 'display: none;' }}">
                        <label for="start_date_admin">Tanggal Mulai:</label>
                        <input type="date" id="start_date_admin" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-search me-2"></i>Lihat Laporan
                        </button>
                    </div>
                    <div>
                        <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        @endif
        
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-users me-3"></i>{{ $selectedKomsel->nama_komsel }}</h2>
                <p class="mb-0">
                    <i class="fas fa-user-tie me-2"></i>Pemimpin: {{ $selectedKomsel->pemimpin->nama ?? 'Belum ditentukan' }}
                    &nbsp;•&nbsp;
                    <i class="fas fa-users me-2"></i>{{ $selectedKomsel->anggota->count() }} Anggota
                    &nbsp;•&nbsp;
                    <i class="fas fa-calendar me-2"></i>{{ $selectedKomsel->hari ?? 'Belum dijadwalkan' }}
                    @if($canSelectUser && $selectedUserId)
                        &nbsp;•&nbsp;
                        <i class="fas fa-eye me-2"></i>Laporan Supervisori
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="h4 mb-0">Periode Laporan</div>
                <div>{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</div>
            </div>
        </div>
    </div>
    
    {{-- Filter untuk user yang tidak dapat memilih user lain --}}
    @if(!$canSelectUser || !$selectedUserId)
        <div class="komsel-selector">
            <h5><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
            <form method="GET" action="{{ route('laporan.komsel-report') }}" id="komselFilterForm">
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
                        <label for="period" class="form-label">Periode</label>
                        <select id="period" name="period" class="form-select">
                            <option value="1" {{ $period == 1 ? 'selected' : '' }}>1 Bulan</option>
                            <option value="3" {{ $period == 3 ? 'selected' : '' }}>3 Bulan</option>
                            <option value="6" {{ $period == 6 ? 'selected' : '' }}>6 Bulan</option>
                            <option value="12" {{ $period == 12 ? 'selected' : '' }}>1 Tahun</option>
                            <option value="custom" {{ !in_array($period, [1, 3, 6, 12]) ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                    <div id="customStartDate" style="{{ !in_array($period, [1, 3, 6, 12]) ? '' : 'display: none;' }}">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div id="customEndDate" style="{{ !in_array($period, [1, 3, 6, 12]) ? '' : 'display: none;' }}">
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
    @endif
    
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
                        } elseif ($percentage >= 50) {
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
        </div>
        
        <div class="activities-list">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Riwayat Pertemuan Komsel
                </h5>
            </div>
            <div class="card-body">
                <!-- Export Buttons -->
                @if(!$canSelectUser || !$selectedUserId)
                    <div class="export-buttons p-3">
                        <a href="{{ route('laporan.export', ['jenis' => 'komsel-report', 'format' => 'pdf']) }}?user_id={{ $selectedUserId }}&komsel_id={{ $selectedKomsel->id_komsel }}&start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" 
                           class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'komsel-report', 'format' => 'excel']) }}?user_id={{ $selectedUserId }}&komsel_id={{ $selectedKomsel->id_komsel }}&start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                @endif
                
                <!-- DataTable -->
                <div class="table-responsive">
                    <table id="pertemuanTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="120">Tanggal</th>
                                <th width="100">Waktu</th>
                                <th>Lokasi</th>
                                <th width="120">Jumlah Hadir</th>
                                <th width="100">Persentase</th>
                                <th width="200">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pelaksanaanKomsel as $pertemuan)
                                @php
                                    $pertemuanKehadiran = $kehadiran->where('id_pelaksanaan', $pertemuan->id_pelaksanaan);
                                    $attendanceCount = $pertemuanKehadiran->count();
                                    $totalMembers = $selectedKomsel->anggota->count();
                                    $attendancePercentage = $totalMembers > 0 ? round(($attendanceCount / $totalMembers) * 100) : 0;
                                @endphp
                                
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pertemuan->tanggal_kegiatan)->format('d-m-Y') }}</td>
                                    <td>
                                        <small>
                                            {{ \Carbon\Carbon::parse($pertemuan->jam_mulai)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($pertemuan->jam_selesai)->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <small>{{ $pertemuan->lokasi ?? $selectedKomsel->lokasi ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $attendanceCount }}/{{ $totalMembers }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @if($attendancePercentage >= 80) bg-success
                                            @elseif($attendancePercentage >= 60) bg-warning text-dark
                                            @elseif($attendancePercentage >= 40) bg-info
                                            @else bg-danger
                                            @endif">
                                            {{ $attendancePercentage }}%
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $pertemuan->keterangan ?? '-' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="activities-list">
            <div class="no-data-message">
                <i class="fas fa-calendar-times"></i>
                <h5>Tidak Ada Data Pertemuan</h5>
                <p>Belum ada pertemuan komsel yang terjadwal dalam periode yang dipilih.</p>
                @if(!$canSelectUser || !$selectedUserId)
                    @if(Route::has('komsel.show'))
                        <a href="{{ route('komsel.show', $selectedKomsel->id_komsel) }}" class="btn btn-warning">
                            <i class="fas fa-calendar-plus me-2"></i>Jadwalkan Pertemuan
                        </a>
                    @endif
                @endif
            </div>
        </div>
    @endif
    
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('period');
    const customStartDate = document.getElementById('customStartDate');
    const customEndDate = document.getElementById('customEndDate');
    
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            const selectedPeriod = this.value;
            if (selectedPeriod === 'custom') {
                if (customStartDate) customStartDate.style.display = '';
                if (customEndDate) customEndDate.style.display = '';
            } else {
                if (customStartDate) customStartDate.style.display = 'none';
                if (customEndDate) customEndDate.style.display = 'none';
                // Auto submit for preset periods
                document.getElementById('komselFilterForm').submit();
            }
        });
    }
    // Initialize DataTable
    @if($pelaksanaanKomsel->count() > 0)
        const pertemuanTable = new simpleDatatables.DataTable("#pertemuanTable", {
            searchable: true,
            sortable: true,
            paging: true,
            perPage: 25,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Cari tanggal, lokasi, atau keterangan...",
                perPage: "data per halaman",
                noRows: "Tidak ada data pertemuan",
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
    let attendanceTrendChart = null;
    
    // Function untuk menghancurkan chart yang sudah ada
    function destroyExistingCharts() {
        if (attendanceTrendChart) {
            attendanceTrendChart.destroy();
            attendanceTrendChart = null;
        }
    }
    
    // Handle komsel selection untuk admin
    const userSelect = document.getElementById('user_id');
    const komselIdInput = document.getElementById('komsel_id');
    
    if (userSelect && komselIdInput) {
        userSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const komselId = selectedOption.getAttribute('data-komsel-id');
            komselIdInput.value = komselId || '';
        });
        
        // Set initial value jika ada yang terpilih
        const selectedOption = userSelect.options[userSelect.selectedIndex];
        if (selectedOption) {
            const komselId = selectedOption.getAttribute('data-komsel-id');
            komselIdInput.value = komselId || '';
        }
        
        // Set nilai dari URL parameter jika ada
        const urlParams = new URLSearchParams(window.location.search);
        const urlKomselId = urlParams.get('komsel_id');
        if (urlKomselId) {
            komselIdInput.value = urlKomselId;
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
    @endif
    
    // Cleanup saat halaman akan ditinggalkan
    window.addEventListener('beforeunload', function() {
        destroyExistingCharts();
    });
});
</script>
@endsection