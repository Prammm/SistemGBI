@extends('layouts.app')

@section('title', 'Profile Pelayanan - ' . $anggota->nama)

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Profile Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.members') }}">Anggota Pelayanan</a></li>
        <li class="breadcrumb-item active">{{ $anggota->nama }}</li>
    </ol>
    
    <div class="row">
        <!-- Profile Summary Card -->
        <div class="col-xl-4">
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user-circle me-1"></i>
                    Informasi Dasar
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h4>{{ $anggota->nama }}</h4>
                    <p class="text-muted">{{ $anggota->email ?: 'Email tidak tersedia' }}</p>
                    <p class="text-muted">{{ $anggota->no_telepon ?: 'No. Telepon tidak tersedia' }}</p>
                    
                    <div class="row text-center mt-4">
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <h5 class="text-primary mb-0">{{ $totalServices }}</h5>
                                <small class="text-muted">Total Pelayanan</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <h5 class="text-success mb-0">{{ $regularPositions }}</h5>
                                <small class="text-muted">Posisi Reguler</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <h5 class="text-info mb-0">{{ $totalPositions }}</h5>
                                <small class="text-muted">Total Posisi</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('pelayanan.availability', $anggota->id_anggota) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Ketersediaan
                        </a>
                        @if(Auth::user()->id_role <= 2)
                            <a href="{{ route('pelayanan.assign-regular', $anggota->id_anggota) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-star"></i> Manage Regular
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Statistik Cepat
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Pelayanan 3 Bulan Terakhir</span>
                            <strong>{{ $recentServices }}</strong>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-primary" style="width: {{ min(($recentServices / 12) * 100, 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Workload Score</span>
                            <strong>{{ $workloadScore }}</strong>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-warning" style="width: {{ min(($workloadScore / 50) * 100, 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Hari Istirahat Terakhir</span>
                            <strong>{{ $restDays }} hari</strong>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: {{ min(($restDays / 30) * 100, 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <div class="d-flex justify-content-between">
                            <span>Tingkat Ketersediaan</span>
                            <strong>{{ round($availabilityPercentage) }}%</strong>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-info" style="width: {{ $availabilityPercentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-xl-8">
            <!-- Spesialisasi & Status Reguler -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-star me-1"></i>
                    Spesialisasi & Status Reguler
                </div>
                <div class="card-body">
                    @if($anggota->spesialisasi->isNotEmpty())
                        <div class="row">
                            @foreach($positionCategories as $category => $positions)
                                @php
                                    $categorySpecs = $anggota->spesialisasi->whereIn('posisi', $positions);
                                @endphp
                                
                                @if($categorySpecs->isNotEmpty())
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-primary border-bottom pb-2">{{ $category }}</h6>
                                        @foreach($categorySpecs as $spec)
                                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded {{ $spec->is_reguler ? 'bg-success bg-opacity-10' : 'bg-light' }}">
                                                <div>
                                                    <strong>{{ $spec->posisi }}</strong>
                                                    @if($spec->is_reguler)
                                                        <span class="badge bg-success ms-2">
                                                            <i class="fas fa-star"></i> Reguler
                                                        </span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">
                                                        Prioritas: {{ $spec->prioritas }}/10
                                                        @if($spec->catatan)
                                                            | {{ $spec->catatan }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    @php
                                                        $positionServices = $anggota->jadwalPelayanan()
                                                            ->where('posisi', $spec->posisi)
                                                            ->count();
                                                        $lastService = $anggota->getLastServiceDate($spec->posisi);
                                                    @endphp
                                                    <div class="badge bg-secondary">{{ $positionServices }}x</div>
                                                    @if($lastService)
                                                        <br><small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($lastService)->diffForHumans() }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <h5>Belum Ada Spesialisasi</h5>
                            <p class="text-muted">Anggota ini belum memiliki spesialisasi pelayanan.</p>
                            <a href="{{ route('pelayanan.availability', $anggota->id_anggota) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Spesialisasi
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Ketersediaan -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-calendar-check me-1"></i>
                    Ketersediaan Waktu
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Hari Tersedia -->
                        <div class="col-md-6">
                            <h6>Hari Tersedia:</h6>
                            @if(!empty($anggota->ketersediaan_hari))
                                @php
                                    $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                    $dayColors = ['danger', 'primary', 'primary', 'primary', 'primary', 'primary', 'warning'];
                                @endphp
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($anggota->ketersediaan_hari as $day)
                                        <span class="badge bg-{{ $dayColors[$day] }}">
                                            {{ $dayNames[$day] }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">Belum diatur</span>
                            @endif
                        </div>
                        
                        <!-- Jam Tersedia -->
                        <div class="col-md-6">
                            <h6>Jam Tersedia:</h6>
                            @if(!empty($anggota->ketersediaan_jam))
                                @foreach($anggota->ketersediaan_jam as $timeSlot)
                                    @php
                                        list($start, $end) = explode('-', $timeSlot);
                                        $duration = \Carbon\Carbon::parse($start)->diffInHours(\Carbon\Carbon::parse($end));
                                    @endphp
                                    <div class="badge bg-secondary me-1 mb-1">
                                        {{ $start }} - {{ $end }} ({{ $duration }}j)
                                    </div>
                                @endforeach
                            @else
                                <span class="text-muted">Belum diatur</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Blackout Dates -->
                    @if(!empty($anggota->blackout_dates))
                        <div class="mt-3">
                            <h6>Tanggal Tidak Tersedia:</h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($anggota->blackout_dates as $date)
                                    <span class="badge bg-danger">
                                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Catatan Khusus -->
                    @if($anggota->catatan_khusus)
                        <div class="mt-3">
                            <h6>Catatan Khusus:</h6>
                            <div class="alert alert-warning">
                                <i class="fas fa-sticky-note"></i> {{ $anggota->catatan_khusus }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Riwayat Pelayanan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-history me-1"></i>
                    Riwayat Pelayanan (10 Terakhir)
                </div>
                <div class="card-body">
                    @if($recentSchedules->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kegiatan</th>
                                        <th>Posisi</th>
                                        <th>Status</th>
                                        <th>Reguler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSchedules as $schedule)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($schedule->tanggal_pelayanan)->format('d/m/Y') }}</td>
                                            <td>{{ $schedule->kegiatan->nama_kegiatan ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $schedule->posisi }}</span>
                                            </td>
                                            <td>
                                                @switch($schedule->status_konfirmasi)
                                                    @case('terima')
                                                        <span class="badge bg-success">Diterima</span>
                                                        @break
                                                    @case('tolak')
                                                        <span class="badge bg-danger">Ditolak</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-warning">Belum Konfirmasi</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($schedule->is_reguler)
                                                    <i class="fas fa-star text-success"></i>
                                                @else
                                                    <i class="fas fa-minus text-muted"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('pelayanan.member-history', $anggota->id_anggota) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list"></i> Lihat Semua Riwayat
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Belum ada riwayat pelayanan</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Performance Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Grafik Pelayanan (12 Bulan Terakhir)
                </div>
                <div class="card-body">
                    <canvas id="serviceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Service Chart
    const ctx = document.getElementById('serviceChart').getContext('2d');
    const serviceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                label: 'Jumlah Pelayanan',
                data: {!! json_encode($chartData['data']) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endsection