@extends('layouts.app')

@section('title', 'Riwayat Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Riwayat Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelayanan.index') }}">Pelayanan</a></li>
        <li class="breadcrumb-item active">Riwayat</li>
    </ol>
    
    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Riwayat Pelayanan
        </div>
        <div class="card-body">
            <form method="GET" id="filter-form" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai:</label>
                    <input type="date" class="form-control" name="start_date" 
                           value="{{ request('start_date', now()->subMonths(3)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai:</label>
                    <input type="date" class="form-control" name="end_date" 
                           value="{{ request('end_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Posisi:</label>
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
                        <option value="">-- Semua Status --</option>
                        <option value="terima" {{ request('status') === 'terima' ? 'selected' : '' }}>Diterima</option>
                        <option value="tolak" {{ request('status') === 'tolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Konfirmasi</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('pelayanan.history') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Reset
                    </a>
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
                            <div class="text-white-75 small">Total Riwayat</div>
                            <div class="text-lg fw-bold">{{ $historyData->total() }}</div>
                        </div>
                        <div><i class="fas fa-history fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-75 small">Diterima</div>
                            <div class="text-lg fw-bold">{{ $statistics['accepted'] }}</div>
                        </div>
                        <div><i class="fas fa-check-circle fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-75 small">Ditolak</div>
                            <div class="text-lg fw-bold">{{ $statistics['rejected'] }}</div>
                        </div>
                        <div><i class="fas fa-times-circle fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-75 small">Belum Konfirmasi</div>
                            <div class="text-lg fw-bold">{{ $statistics['pending'] }}</div>
                        </div>
                        <div><i class="fas fa-clock fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Options -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-download me-1"></i>
                Export Riwayat
            </div>
            <div class="btn-group">
                <a href="{{ route('pelayanan.history.export', array_merge(['format' => 'excel'], request()->all())) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('pelayanan.history.export', array_merge(['format' => 'pdf'], request()->all())) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>
    
    <!-- History Data -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
            Riwayat Pelayanan ({{ $historyData->total() }} total)
        </div>
        <div class="card-body">
            @if($historyData->isNotEmpty())
                <!-- Group by Month View Toggle -->
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="listView" value="list" checked>
                        <label class="btn btn-outline-primary btn-sm" for="listView">
                            <i class="fas fa-list"></i> List View
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="groupView" value="group">
                        <label class="btn btn-outline-primary btn-sm" for="groupView">
                            <i class="fas fa-calendar"></i> Group by Month
                        </label>
                    </div>
                </div>
                
                <!-- List View -->
                <div id="listViewContent">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kegiatan</th>
                                    <th>Posisi</th>
                                    <th>Petugas</th>
                                    <th>Status</th>
                                    <th>Reguler</th>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historyData as $jadwal)
                                    @php
                                        $isExpired = \Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->isPast() && $jadwal->status_konfirmasi === 'belum';
                                        $wasAutoRejected = \Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->isPast() && $jadwal->status_konfirmasi === 'tolak';
                                    @endphp
                                    <tr class="{{ $jadwal->status_konfirmasi === 'terima' ? 'table-light' : ($jadwal->status_konfirmasi === 'tolak' ? 'table-danger' : 'table-warning') }}">
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->format('d/m/Y') }}</strong><br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->format('l') }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $jadwal->pelaksanaan->kegiatan->nama_kegiatan ?? $jadwal->kegiatan->nama_kegiatan ?? 'N/A' }}</strong>
                                            @if($jadwal->pelaksanaan)
                                                <br><small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($jadwal->pelaksanaan->jam_mulai)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($jadwal->pelaksanaan->jam_selesai)->format('H:i') }}
                                                    @if($jadwal->pelaksanaan->lokasi)
                                                        | {{ $jadwal->pelaksanaan->lokasi }}
                                                    @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $jadwal->posisi }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('pelayanan.member-profile', $jadwal->anggota->id_anggota) }}" class="text-decoration-none">
                                                {{ $jadwal->anggota->nama }}
                                            </a>
                                            @if($jadwal->anggota->no_telepon)
                                                <br><small class="text-muted">
                                                    <i class="fas fa-phone"></i> {{ $jadwal->anggota->no_telepon }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($jadwal->status_konfirmasi)
                                                @case('terima')
                                                    <span class="badge bg-success">Diterima</span>
                                                    @break
                                                @case('tolak')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                    @if($wasAutoRejected)
                                                        <br><small class="text-muted">Auto-reject</small>
                                                    @endif
                                                    @php
                                                        $replacement = \App\Models\JadwalPelayananReplacement::where('id_jadwal_pelayanan', $jadwal->id_pelayanan)->first();
                                                    @endphp
                                                    @if($replacement)
                                                        <br><small class="text-info">
                                                            @if($replacement->replacement)
                                                                Diganti: {{ $replacement->replacement->nama }}
                                                            @else
                                                                {{ ucfirst($replacement->replacement_status) }}
                                                            @endif
                                                        </small>
                                                    @endif
                                                    @break
                                                @default
                                                    <span class="badge bg-warning">Belum Konfirmasi</span>
                                                    @if($isExpired)
                                                        <br><small class="text-danger">Akan auto-reject</small>
                                                    @endif
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($jadwal->is_reguler || $jadwal->anggota->isRegularIn($jadwal->posisi))
                                                <span class="badge bg-success">
                                                    <i class="fas fa-star"></i> Reguler
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark">Non-Reguler</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                Dibuat: {{ $jadwal->created_at->format('d/m/Y H:i') }}<br>
                                                Update: {{ $jadwal->updated_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info" onclick="viewDetails({{ $jadwal->id_pelayanan }})" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if(Auth::user()->id_role <= 2)
                                                    <!-- Edit button for Admin only -->
                                                    <button type="button" class="btn btn-outline-warning" onclick="editSchedule({{ $jadwal->id_pelayanan }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
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
                            Menampilkan {{ $historyData->firstItem() }} - {{ $historyData->lastItem() }} dari {{ $historyData->total() }} riwayat
                        </div>
                        {{ $historyData->appends(request()->query())->links() }}
                    </div>
                </div>
                
                <!-- Group View -->
                <div id="groupViewContent" style="display: none;">
                    @php
                        $groupedData = $historyData->groupBy(function($item) {
                            return \Carbon\Carbon::parse($item->tanggal_pelayanan)->format('Y-m');
                        });
                    @endphp
                    
                    @foreach($groupedData as $monthYear => $schedules)
                        @php
                            $monthName = \Carbon\Carbon::createFromFormat('Y-m', $monthYear)->format('F Y');
                            $monthStats = [
                                'total' => $schedules->count(),
                                'accepted' => $schedules->where('status_konfirmasi', 'terima')->count(),
                                'rejected' => $schedules->where('status_konfirmasi', 'tolak')->count(),
                                'pending' => $schedules->where('status_konfirmasi', 'belum')->count(),
                            ];
                        @endphp
                        
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-0">{{ $monthName }}</h6>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <span class="badge bg-primary">{{ $monthStats['total'] }} total</span>
                                        <span class="badge bg-success">{{ $monthStats['accepted'] }} diterima</span>
                                        <span class="badge bg-danger">{{ $monthStats['rejected'] }} ditolak</span>
                                        <span class="badge bg-warning">{{ $monthStats['pending'] }} pending</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($schedules->groupBy('tanggal_pelayanan') as $date => $dailySchedules)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-2">
                                                <h6 class="text-primary">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h6>
                                                @foreach($dailySchedules as $schedule)
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <div>
                                                            <strong>{{ $schedule->posisi }}</strong><br>
                                                            <small class="text-muted">{{ $schedule->anggota->nama }}</small>
                                                        </div>
                                                        <div>
                                                            @switch($schedule->status_konfirmasi)
                                                                @case('terima')
                                                                    <i class="fas fa-check-circle text-success"></i>
                                                                    @break
                                                                @case('tolak')
                                                                    <i class="fas fa-times-circle text-danger"></i>
                                                                    @break
                                                                @default
                                                                    <i class="fas fa-clock text-warning"></i>
                                                            @endswitch
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5>Tidak Ada Riwayat</h5>
                    <p class="text-muted">Tidak ada riwayat pelayanan yang sesuai dengan filter yang dipilih.</p>
                    <a href="{{ route('pelayanan.history') }}" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Reset Filter
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Jadwal Pelayanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
@if(Auth::user()->id_role <= 2)
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jadwal Pelayanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // View mode toggle
    document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'list') {
                document.getElementById('listViewContent').style.display = 'block';
                document.getElementById('groupViewContent').style.display = 'none';
            } else {
                document.getElementById('listViewContent').style.display = 'none';
                document.getElementById('groupViewContent').style.display = 'block';
            }
        });
    });
    
    function viewDetails(jadwalId) {
        const modalBody = document.getElementById('detailModalBody');
        modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        fetch(`/pelayanan/api/schedule-details/${jadwalId}`)
            .then(response => response.json())
            .then(data => {
                const schedule = data.schedule;
                const replacement = data.replacement;
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informasi Kegiatan</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Kegiatan:</strong></td><td>${schedule.kegiatan}</td></tr>
                                <tr><td><strong>Tanggal:</strong></td><td>${schedule.tanggal}</td></tr>
                                <tr><td><strong>Waktu:</strong></td><td>${schedule.waktu}</td></tr>
                                <tr><td><strong>Lokasi:</strong></td><td>${schedule.lokasi || '-'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Informasi Petugas</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Nama:</strong></td><td>${schedule.anggota}</td></tr>
                                <tr><td><strong>Posisi:</strong></td><td>${schedule.posisi}</td></tr>
                                <tr><td><strong>Status:</strong></td><td>${schedule.status_badge}</td></tr>
                                <tr><td><strong>Regular:</strong></td><td>${schedule.reguler_badge}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                if (replacement) {
                    html += `
                        <hr>
                        <h6>Informasi Penggantian</h6>
                        <div class="alert alert-info">
                            <strong>Alasan:</strong> ${replacement.reason}<br>
                            <strong>Status:</strong> ${replacement.status}<br>
                            ${replacement.replacement_name ? `<strong>Pengganti:</strong> ${replacement.replacement_name}<br>` : ''}
                            <strong>Waktu Request:</strong> ${replacement.requested_at}<br>
                            ${replacement.notes ? `<strong>Catatan:</strong> ${replacement.notes}` : ''}
                        </div>
                    `;
                }
                
                html += `
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Dibuat: ${schedule.created_at}</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">Diupdate: ${schedule.updated_at}</small>
                        </div>
                    </div>
                `;
                
                modalBody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        Terjadi kesalahan saat memuat detail.
                    </div>
                `;
            });
        
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        modal.show();
    }
    
    @if(Auth::user()->id_role <= 2)
    function editSchedule(jadwalId) {
        const modalBody = document.getElementById('editModalBody');
        const editForm = document.getElementById('editForm');
        
        modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        // Set form action
        editForm.action = `/pelayanan/api/update-schedule/${jadwalId}`;
        
        fetch(`/pelayanan/api/schedule-details/${jadwalId}`)
            .then(response => response.json())
            .then(data => {
                const schedule = data.schedule;
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status Konfirmasi</label>
                                <select class="form-select" id="edit_status" name="status_konfirmasi" required>
                                    <option value="belum">Belum Konfirmasi</option>
                                    <option value="terima">Diterima</option>
                                    <option value="tolak">Ditolak</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_notes" class="form-label">Catatan</label>
                                <textarea class="form-control" id="edit_notes" name="notes" rows="3" placeholder="Catatan perubahan..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <h6>Informasi Jadwal:</h6>
                        <strong>Kegiatan:</strong> ${schedule.kegiatan}<br>
                        <strong>Tanggal:</strong> ${schedule.tanggal}<br>
                        <strong>Petugas:</strong> ${schedule.anggota}<br>
                        <strong>Posisi:</strong> ${schedule.posisi}
                    </div>
                `;
                
                modalBody.innerHTML = html;
                
                // Set current status as selected
                const currentStatus = schedule.status_badge.includes('Diterima') ? 'terima' : 
                                    schedule.status_badge.includes('Ditolak') ? 'tolak' : 'belum';
                document.getElementById('edit_status').value = currentStatus;
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        Terjadi kesalahan saat memuat data.
                    </div>
                `;
            });
        
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }
    
    // Handle edit form submission
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const jadwalId = this.action.split('/').pop();
        
        fetch(`/pelayanan/api/update-schedule/${jadwalId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh page to show changes
            } else {
                alert('Terjadi kesalahan: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan perubahan.');
        });
    });
    @endif
    
    function duplicateSchedule(jadwalId) {
        if (confirm('Duplikasi jadwal ini untuk pelaksanaan kegiatan lain?')) {
            window.location.href = `/pelayanan/create?duplicate=${jadwalId}`;
        }
    }

    
    // Auto-submit form when filters change
    document.querySelectorAll('#filter-form select, #filter-form input[type="date"]').forEach(element => {
        element.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
    
    // Charts
    @if($historyData->isNotEmpty())
        // Monthly Trend Chart
        const monthlyData = @json($chartData['monthly']);
        const ctx1 = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Total Pelayanan',
                    data: monthlyData.total,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Diterima',
                    data: monthlyData.accepted,
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Ditolak',
                    data: monthlyData.rejected,
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
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
        
        // Status Distribution Chart
        const statusData = @json($chartData['status']);
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Diterima', 'Ditolak', 'Belum Konfirmasi'],
                datasets: [{
                    data: [statusData.accepted, statusData.rejected, statusData.pending],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107']
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
    @endif
</script>
@endsection