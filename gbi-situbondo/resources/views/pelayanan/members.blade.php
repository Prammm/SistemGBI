@extends('layouts.app')

@section('title', 'Anggota Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Anggota Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item active">Anggota Pelayanan</li>
    </ol>
    
    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter & Pencarian
        </div>
        <div class="card-body">
            <form method="GET" id="filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Cari Nama:</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama anggota...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filter Posisi:</label>
                        <select class="form-select" name="posisi">
                            <option value="">-- Semua Posisi --</option>
                            @foreach($availablePositions as $position)
                                <option value="{{ $position }}" {{ request('posisi') === $position ? 'selected' : '' }}>
                                    {{ $position }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status:</label>
                        <select class="form-select" name="status">
                            <option value="">-- Semua --</option>
                            <option value="reguler" {{ request('status') === 'reguler' ? 'selected' : '' }}>Pemain Reguler</option>
                            <option value="non_reguler" {{ request('status') === 'non_reguler' ? 'selected' : '' }}>Non Reguler</option>
                            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia Weekend</option>
                            <option value="no_specialization" {{ request('status') === 'no_specialization' ? 'selected' : '' }}>Belum Ada Spesialisasi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Urutkan:</label>
                        <select class="form-select" name="sort">
                            <option value="nama" {{ request('sort') === 'nama' ? 'selected' : '' }}>Nama A-Z</option>
                            <option value="total_services" {{ request('sort') === 'total_services' ? 'selected' : '' }}>Total Pelayanan</option>
                            <option value="recent_services" {{ request('sort') === 'recent_services' ? 'selected' : '' }}>Pelayanan Terakhir</option>
                            <option value="workload" {{ request('sort') === 'workload' ? 'selected' : '' }}>Workload</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('pelayanan.members') }}" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-75 small">Total Anggota Pelayanan</div>
                            <div class="text-lg fw-bold">{{ $totalMembers }}</div>
                        </div>
                        <div><i class="fas fa-users fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-75 small">Pemain Reguler</div>
                            <div class="text-lg fw-bold">{{ $regularMembers }}</div>
                        </div>
                        <div><i class="fas fa-star fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-75 small">Tersedia Weekend</div>
                            <div class="text-lg fw-bold">{{ $weekendAvailable }}</div>
                        </div>
                        <div><i class="fas fa-calendar-week fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-75 small">Belum Setup</div>
                            <div class="text-lg fw-bold">{{ $needsSetup }}</div>
                        </div>
                        <div><i class="fas fa-exclamation-triangle fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Members List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
            Daftar Anggota Pelayanan ({{ $members->total() }} total)
            
            <div class="float-end">
                <a href="{{ route('pelayanan.members.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($members->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Spesialisasi</th>
                                <th>Status Reguler</th>
                                <th>Ketersediaan</th>
                                <th>Statistik</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial rounded-circle bg-primary text-white me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                {{ strtoupper(substr($member->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $member->nama }}</strong>
                                                <br><small class="text-muted">{{ $member->email ?: 'No email' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($member->spesialisasi->isNotEmpty())
                                            @foreach($member->spesialisasi->take(3) as $spec)
                                                <span class="badge bg-secondary me-1 mb-1">{{ $spec->posisi }}</span>
                                            @endforeach
                                            @if($member->spesialisasi->count() > 3)
                                                <span class="badge bg-light text-dark">+{{ $member->spesialisasi->count() - 3 }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Belum ada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $regularSpecs = $member->spesialisasi->where('is_reguler', true);
                                        @endphp
                                        
                                        @if($regularSpecs->isNotEmpty())
                                            @foreach($regularSpecs->take(2) as $spec)
                                                <span class="badge bg-success me-1 mb-1">
                                                    <i class="fas fa-star"></i> {{ $spec->posisi }}
                                                </span>
                                            @endforeach
                                            @if($regularSpecs->count() > 2)
                                                <span class="badge bg-success">+{{ $regularSpecs->count() - 2 }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Non reguler</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($member->ketersediaan_hari))
                                            @php
                                                $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                                                $availableDays = collect($member->ketersediaan_hari)->map(fn($day) => $dayNames[$day]);
                                            @endphp
                                            <div class="small">
                                                <strong>Hari:</strong> {{ $availableDays->implode(', ') }}
                                            </div>
                                            @if(!empty($member->ketersediaan_jam))
                                                <div class="small text-muted">
                                                    <strong>Jam:</strong> {{ count($member->ketersediaan_jam) }} slot
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Belum diatur
                                            </span>
                                        @endif
                                        
                                        @if(!empty($member->blackout_dates))
                                            <div class="small text-danger">
                                                <i class="fas fa-ban"></i> {{ count($member->blackout_dates) }} blackout
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $totalServices = $member->jadwalPelayanan->count();
                                            $recentServices = $member->jadwalPelayanan()
                                                ->where('tanggal_pelayanan', '>=', now()->subMonths(3))
                                                ->count();
                                            $lastService = $member->jadwalPelayanan()
                                                ->orderBy('tanggal_pelayanan', 'desc')
                                                ->first();
                                        @endphp
                                        
                                        <div class="small">
                                            <strong>Total:</strong> {{ $totalServices }}
                                        </div>
                                        <div class="small">
                                            <strong>3 Bulan:</strong> {{ $recentServices }}
                                        </div>
                                        @if($lastService)
                                            <div class="small text-muted">
                                                <strong>Terakhir:</strong> {{ \Carbon\Carbon::parse($lastService->tanggal_pelayanan)->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('pelayanan.member-profile', $member->id_anggota) }}" class="btn btn-outline-primary" title="Lihat Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('pelayanan.availability', $member->id_anggota) }}" class="btn btn-outline-success" title="Edit Ketersediaan">
                                                <i class="fas fa-calendar-edit"></i>
                                            </a>
                                            @if(Auth::user()->id_role <= 2)
                                                <a href="{{ route('pelayanan.assign-regular', $member->id_anggota) }}" class="btn btn-outline-warning" title="Manage Regular">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Menampilkan {{ $members->firstItem() }} - {{ $members->lastItem() }} dari {{ $members->total() }} anggota
                    </div>
                    {{ $members->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>Tidak Ada Anggota Pelayanan</h5>
                    <p class="text-muted">Tidak ada anggota yang sesuai dengan filter yang dipilih.</p>
                    <a href="{{ route('pelayanan.members') }}" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Reset Filter
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Position Distribution Chart
    const positionCtx = document.getElementById('positionChart').getContext('2d');
    const positionChart = new Chart(positionCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($positionDistribution['labels']) !!},
            datasets: [{
                data: {!! json_encode($positionDistribution['data']) !!},
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Regular Status Chart
    const regularCtx = document.getElementById('regularChart').getContext('2d');
    const regularChart = new Chart(regularCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($regularDistribution['labels']) !!},
            datasets: [{
                data: {!! json_encode($regularDistribution['data']) !!},
                backgroundColor: ['#28a745', '#6c757d', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Auto-submit form when filters change
    document.querySelectorAll('#filter-form select').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
</script>
@endsection