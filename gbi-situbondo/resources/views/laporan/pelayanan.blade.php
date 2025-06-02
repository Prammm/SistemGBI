@extends('layouts.app')

@section('title', 'Laporan Pelayanan')

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
    <h1 class="mt-4">Laporan Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Pelayanan</li>
    </ol>

    <div class="row">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filter Laporan
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.pelayanan') }}" class="row g-3">
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
                            <a href="{{ route('laporan.pelayanan') }}" class="btn btn-secondary">Reset</a>
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
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stats-card-title">Total Pelayanan</div>
                <div class="stats-card-value">{{ $totalPelayanan }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card success">
                <div class="stats-card-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <div class="stats-card-title">Total Pelayan</div>
                <div class="stats-card-value">{{ $totalPelayan }}</div>
            </div>
        </div>
        
    </div>

    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Pelayanan per Posisi
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="posisiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Pelayan Paling Aktif
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="pelayanChart"></canvas>
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
                    Detail Jadwal Pelayanan
                </div>
                <div class="card-body table-container">
                    <div class="mb-3">
                        <a href="{{ route('laporan.export', ['jenis' => 'pelayanan', 'format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'pelayanan', 'format' => 'excel']) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                    <table id="pelayananTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pelayan</th>
                                <th>Kegiatan</th>
                                <th>Posisi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwalPelayanan as $jp)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($jp->tanggal_pelayanan)->format('d-m-Y') }}</td>
                                <td>{{ $jp->anggota->nama ?? 'Tidak Diketahui' }}</td>
                                <td>{{ $jp->kegiatan->nama_kegiatan ?? 'Tidak Diketahui' }}</td>
                                <td>{{ $jp->posisi->nama_posisi ?? '-' }}</td>
                                <td>
                                    @if($jp->status == 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                    @elseif($jp->status == 'tidak_hadir')
                                    <span class="badge bg-danger">Tidak Hadir</span>
                                    @elseif($jp->status == 'pending')
                                    <span class="badge bg-warning">Menunggu Konfirmasi</span>
                                    @else
                                    <span class="badge bg-secondary">Tidak Hadir</span>
                                    @endif
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
        // Pelayanan per Posisi Chart
        const posisiCtx = document.getElementById('posisiChart').getContext('2d');
        const posisiData = @json($pelayananPerPosisi);
        const posisiLabels = posisiData.map(item => item.posisi);
        const posisiValues = posisiData.map(item => item.jumlah);
        const posisiColors = [
            'rgba(78, 115, 223, 0.8)',
            'rgba(28, 200, 138, 0.8)',
            'rgba(246, 194, 62, 0.8)',
            'rgba(231, 74, 59, 0.8)',
            'rgba(54, 185, 204, 0.8)',
            'rgba(133, 135, 150, 0.8)',
            'rgba(105, 0, 132, 0.8)',
            'rgba(0, 181, 204, 0.8)',
            'rgba(220, 53, 69, 0.8)',
            'rgba(40, 167, 69, 0.8)'
        ];
        
        new Chart(posisiCtx, {
            type: 'pie',
            data: {
                labels: posisiLabels,
                datasets: [{
                    data: posisiValues,
                    backgroundColor: posisiColors,
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
        
        // Pelayan Paling Aktif Chart
        const pelayanCtx = document.getElementById('pelayanChart').getContext('2d');
        const pelayanData = @json($pelayanAktif);
        const pelayanLabels = pelayanData.map(item => item.anggota);
        const pelayanValues = pelayanData.map(item => item.jumlah);
        
        new Chart(pelayanCtx, {
            type: 'bar',
            data: {
                labels: pelayanLabels,
                datasets: [{
                    label: 'Jumlah Pelayanan',
                    data: pelayanValues,
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
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
        
        // Initialize DataTable
        $('#pelayananTable').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });
    });
</script>
@endsection