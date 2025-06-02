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
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stats-card-title">Anggota Aktif</div>
                <div class="stats-card-value">{{ $anggotaAktif }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card danger">
                <div class="stats-card-icon">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stats-card-title">Anggota Tidak Aktif</div>
                <div class="stats-card-value">{{ $anggotaTidakAktif }}</div>
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

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Data Anggota
                </div>
                <div class="card-body table-container">
                    <div class="mb-3">
                        <a href="{{ route('laporan.export', ['jenis' => 'anggota', 'format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'anggota', 'format' => 'excel']) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                    <table id="anggotaTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Gender</th>
                                <th>Tanggal Lahir</th>
                                <th>No. Telepon</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($anggota as $a)
                            <tr>
                                <td>{{ $a->nama }}</td>
                                <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : ($a->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                                <td>{{ $a->tanggal_lahir ? \Carbon\Carbon::parse($a->tanggal_lahir)->format('d-m-Y') : '-' }}</td>
                                <td>{{ $a->no_telepon ?? '-' }}</td>
                                <td>{{ $a->email ?? '-' }}</td>
                                <td>
                                    @php
                                        $aktif = \App\Models\Kehadiran::where('id_anggota', $a->id_anggota)
                                            ->where('waktu_absensi', '>=', \Carbon\Carbon::now()->subMonths(3))
                                            ->exists();
                                    @endphp
                                    @if($aktif)
                                    <span class="badge bg-success">Aktif</span>
                                    @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('anggota.show', $a->id_anggota) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderData = @json($anggotaPerGender);
        const genderLabels = genderData.map(item => item.gender);
        const genderValues = genderData.map(item => item.jumlah);
        const genderColors = [
            'rgba(78, 115, 223, 0.8)',
            'rgba(231, 74, 59, 0.8)',
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
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
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
        $('#anggotaTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });
    });
</script>
@endsection