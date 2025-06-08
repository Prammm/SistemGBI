@extends('layouts.app')

@section('title', 'Jadwal Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Jadwal Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Jadwal Pelayanan</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <!-- Pending Replacements Alert -->
    @php
        $pendingReplacements = \App\Models\JadwalPelayananReplacement::getPendingReplacements();
    @endphp
    @if($pendingReplacements->isNotEmpty() && Auth::user()->id_role <= 3)
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Perhatian!</strong> Ada {{ $pendingReplacements->count() }} jadwal yang membutuhkan pengganti.
            <a href="#pending-replacements" class="alert-link">Lihat detail</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <!-- Quick Actions Card -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-bolt me-1"></i>
                    Quick Actions
                    
                    @if(Auth::user()->id_role <= 3)
                        <div class="float-end">
                            <a href="{{ route('pelayanan.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> Tambah Jadwal
                            </a>
                            
                            @if(Auth::user()->id_role <= 2)
                                <a href="{{ route('pelayanan.generator') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-magic"></i> Generate Jadwal
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Personal Actions -->
                        <div class="col-md-3">
                            <h6 class="text-primary">Pengaturan Personal</h6>
                            <div class="d-grid gap-2">
                                @if(Auth::user()->id_anggota)
                                    <a href="{{ route('pelayanan.availability', Auth::user()->id_anggota) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-calendar-edit"></i> Ketersediaan Saya
                                    </a>
                                    <a href="{{ route('pelayanan.member-profile', Auth::user()->id_anggota) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-user"></i> Profile Pelayanan
                                    </a>
                                @else
                                    <span class="text-muted small">Tidak terhubung dengan anggota</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Management Actions -->
                        @if(Auth::user()->id_role <= 3)
                        <div class="col-md-3">
                            <h6 class="text-success">Manajemen</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('pelayanan.members') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-users"></i> Anggota Pelayanan
                                </a>
                                @if(Auth::user()->id_role <= 2)
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sendNotifications()">
                                        <i class="fas fa-bell"></i> Kirim Notifikasi
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        
                        <!-- Current Status -->
                        <div class="col-md-3">
                            <h6 class="text-info">Status Saat Ini</h6>
                            <div class="small">
                                @php
                                    $user = Auth::user();
                                    $todaySchedule = collect();
                                    if ($user->id_anggota) {
                                        $todaySchedule = \App\Models\JadwalPelayanan::where('id_anggota', $user->id_anggota)
                                            ->where('tanggal_pelayanan', now()->format('Y-m-d'))
                                            ->with('pelaksanaan.kegiatan')
                                            ->get();
                                    }
                                @endphp
                                
                                @if($todaySchedule->isNotEmpty())
                                    <div class="alert alert-success py-2 mb-2">
                                        <strong>Jadwal Hari Ini:</strong><br>
                                        @foreach($todaySchedule as $schedule)
                                            • {{ $schedule->posisi }} - {{ $schedule->pelaksanaan->kegiatan->nama_kegiatan ?? 'N/A' }}<br>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-check"></i> Tidak ada jadwal hari ini
                                    </div>
                                @endif
                                
                                @if($user->id_anggota)
                                    @php
                                        $upcomingSchedule = \App\Models\JadwalPelayanan::where('id_anggota', $user->id_anggota)
                                            ->where('tanggal_pelayanan', '>', now()->format('Y-m-d'))
                                            ->where('tanggal_pelayanan', '<=', now()->addDays(7)->format('Y-m-d'))
                                            ->count();
                                    @endphp
                                    <div class="mt-2">
                                        <i class="fas fa-calendar-alt"></i> {{ $upcomingSchedule }} jadwal minggu depan
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Replacements Section -->
    @if($pendingReplacements->isNotEmpty() && Auth::user()->id_role <= 3)
        <div class="card mb-4" id="pending-replacements">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-user-times me-1"></i>
                Jadwal Membutuhkan Pengganti ({{ $pendingReplacements->count() }})
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Kegiatan</th>
                                <th>Posisi</th>
                                <th>Anggota Asli</th>
                                <th>Alasan</th>
                                <th>Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingReplacements as $replacement)
                                <tr class="table-warning">
                                    <td>
                                        <strong>{{ $replacement->jadwalPelayanan->pelaksanaan->kegiatan->nama_kegiatan ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($replacement->jadwalPelayanan->tanggal_pelayanan)->format('d/m/Y') }}
                                            {{ \Carbon\Carbon::parse($replacement->jadwalPelayanan->pelaksanaan->jam_mulai)->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $replacement->jadwalPelayanan->posisi }}</span>
                                    </td>
                                    <td>{{ $replacement->originalAssignee->nama }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($replacement->replacement_reason) }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $replacement->requested_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="findReplacement({{ $replacement->id }})"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#replacementModal">
                                            <i class="fas fa-search"></i> Cari Pengganti
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Jadwal Pelayanan Mendatang - GROUPED BY PELAKSANAAN -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-check me-1"></i>
            Jadwal Pelayanan Mendatang
        </div>
        <div class="card-body">
            @if($jadwalPelayanan->isNotEmpty())
                @foreach($jadwalPelayanan as $tanggal => $jadwalPerTanggal)
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 text-primary">
                            {{ \Carbon\Carbon::parse($tanggal)->format('l, d F Y') }}
                        </h5>
                        
                        @php
                            // Group by pelaksanaan within each date
                            $jadwalGrouped = $jadwalPerTanggal->groupBy('id_pelaksanaan');
                        @endphp
                        
                        @foreach($jadwalGrouped as $idPelaksanaan => $jadwalPerPelaksanaan)
                            @php
                                $pelaksanaan = $jadwalPerPelaksanaan->first()->pelaksanaan;
                                $kegiatan = $pelaksanaan->kegiatan ?? $jadwalPerPelaksanaan->first()->kegiatan;
                            @endphp
                            
                            <div class="card mb-3 border-start border-4 border-primary">
                                <div class="card-header bg-light">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-1">
                                                <i class="fas fa-church text-primary me-2"></i>
                                                {{ $kegiatan->nama_kegiatan ?? 'N/A' }}
                                            </h6>
                                            <div class="text-muted small">
                                                <i class="fas fa-clock"></i> 
                                                {{ \Carbon\Carbon::parse($pelaksanaan->jam_mulai ?? '00:00')->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($pelaksanaan->jam_selesai ?? '00:00')->format('H:i') }}
                                                @if($pelaksanaan->lokasi)
                                                    | <i class="fas fa-map-marker-alt"></i> {{ $pelaksanaan->lokasi }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-primary fs-6">{{ $jadwalPerPelaksanaan->count() }} posisi</span>
                                            @if(Auth::user()->id_role <= 3)
                                                <div class="btn-group btn-group-sm mt-1">
                                                    <a href="{{ route('pelayanan.create') }}?id_pelaksanaan={{ $idPelaksanaan }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-edit"></i> Edit Tim
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="20%">Posisi</th>
                                                    <th width="30%">Petugas</th>
                                                    <th width="15%">Status</th>
                                                    <th width="15%">Regular</th>
                                                    <th width="20%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($jadwalPerPelaksanaan as $jadwal)
                                                    @php
                                                        $hasReplacement = \App\Models\JadwalPelayananReplacement::where('id_jadwal_pelayanan', $jadwal->id_pelayanan)
                                                            ->where('replacement_status', 'pending')
                                                            ->exists();
                                                        $rowClass = '';
                                                        if ($jadwal->status_konfirmasi === 'tolak') {
                                                            $rowClass = Auth::user()->id_role <= 3 ? 'table-danger' : 'table-warning';
                                                        } elseif ($hasReplacement) {
                                                            $rowClass = 'table-warning';
                                                        } elseif ($jadwal->status_konfirmasi === 'belum') {
                                                            $rowClass = 'table-light';
                                                        } elseif ($jadwal->status_konfirmasi === 'terima') {
                                                            $rowClass = 'table-success';
                                                        }
                                                    @endphp
                                                    
                                                    <tr class="{{ $rowClass }}">
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $jadwal->posisi }}</span>
                                                            @if($hasReplacement)
                                                                <br><small class="text-warning">
                                                                    <i class="fas fa-exclamation-triangle"></i> Perlu pengganti
                                                                </small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-initial rounded-circle bg-primary text-white me-2" style="width: 32px; height: 32px; font-size: 12px; display: flex; align-items: center; justify-content: center;">
                                                                    {{ strtoupper(substr($jadwal->anggota->nama, 0, 2)) }}
                                                                </div>
                                                                <div>
                                                                    <a href="{{ route('pelayanan.member-profile', $jadwal->anggota->id_anggota) }}" class="text-decoration-none fw-bold">
                                                                        {{ $jadwal->anggota->nama }}
                                                                    </a>
                                                                    @if($jadwal->anggota->no_telepon)
                                                                        <br><small class="text-muted">
                                                                            <i class="fas fa-phone"></i> {{ $jadwal->anggota->no_telepon }}
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @switch($jadwal->status_konfirmasi)
                                                                @case('belum')
                                                                    <span class="badge bg-warning">Belum Konfirmasi</span>
                                                                    @break
                                                                @case('terima')
                                                                    <span class="badge bg-success">Diterima</span>
                                                                    @break
                                                                @case('tolak')
                                                                    <span class="badge bg-danger">Ditolak</span>
                                                                    @if($hasReplacement)
                                                                        <br><small class="text-warning">Mencari pengganti...</small>
                                                                    @endif
                                                                    @break
                                                                @default
                                                                    <span class="badge bg-secondary">Unknown</span>
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
                                                            <div class="btn-group btn-group-sm">
                                                                @if(Auth::user()->id_role <= 3 || (Auth::user()->id_anggota && Auth::user()->id_anggota == $jadwal->id_anggota))
                                                                    @if($jadwal->status_konfirmasi == 'belum')
                                                                        <button type="button" class="btn btn-success" onclick="confirmSchedule({{ $jadwal->id_pelayanan }}, 'terima')" title="Terima">
                                                                            <i class="fas fa-check"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-danger" onclick="confirmSchedule({{ $jadwal->id_pelayanan }}, 'tolak')" title="Tolak">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    @elseif($jadwal->status_konfirmasi == 'tolak' && Auth::user()->id_role <= 3)
                                                                        <button type="button" class="btn btn-primary btn-sm" onclick="findReplacement(null, {{ $jadwal->id_pelayanan }})" data-bs-toggle="modal" data-bs-target="#replacementModal">
                                                                            <i class="fas fa-user-plus"></i> Cari Pengganti
                                                                        </button>
                                                                    @endif
                                                                    
                                                                    @if(Auth::user()->id_role <= 3)
                                                                        <button type="button" class="btn btn-outline-secondary" onclick="changeAssignee({{ $jadwal->id_pelayanan }})" title="Ganti Petugas">
                                                                            <i class="fas fa-exchange-alt"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-danger" onclick="deleteSchedule({{ $jadwal->id_pelayanan }})" title="Hapus">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5>Tidak ada jadwal pelayanan mendatang</h5>
                    <p class="text-muted">Belum ada jadwal pelayanan yang terjadwal untuk periode mendatang.</p>
                    @if(Auth::user()->id_role <= 3)
                        <a href="{{ route('pelayanan.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Jadwal Baru
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    <!-- Riwayat Pelayanan -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-history me-1"></i>
                Riwayat Pelayanan (3 Terakhir)
            </div>
            <a href="{{ route('pelayanan.history') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-list"></i> Lihat Semua Riwayat
            </a>
        </div>
        <div class="card-body">
            @if($riwayatPelayanan->isNotEmpty())
                @foreach($riwayatPelayanan->take(3) as $tanggal => $jadwalList)
                    <div class="mb-3">
                        <h6 class="text-muted border-bottom pb-1">
                            {{ \Carbon\Carbon::parse($tanggal)->format('l, d F Y') }}
                            <span class="badge bg-secondary ms-2">{{ $jadwalList->count() }}</span>
                        </h6>
                        
                        <div class="row">
                            @foreach($jadwalList->take(6) as $jadwal)
                                <div class="col-md-4 mb-2">
                                    <div class="card card-sm border-0 bg-light">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="small">{{ $jadwal->posisi }}</strong><br>
                                                    <span class="small text-muted">{{ $jadwal->anggota->nama }}</span>
                                                </div>
                                                <div>
                                                    @switch($jadwal->status_konfirmasi)
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
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($jadwalList->count() > 6)
                                <div class="col-md-4 mb-2">
                                    <div class="card card-sm border-0 bg-secondary text-white">
                                        <div class="card-body p-2 text-center">
                                            <span class="small">+{{ $jadwalList->count() - 6 }} lainnya</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-3">
                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Belum ada riwayat pelayanan</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Replacement Modal -->
<div class="modal fade" id="replacementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cari Pengganti Pelayan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="replacementModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Mencari kandidat pengganti...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmSchedule(jadwalId, status) {
        const action = status === 'terima' ? 'menerima' : 'menolak';
        const message = `Apakah Anda yakin ${action} jadwal pelayanan ini?`;
        
        if (confirm(message)) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = `/pelayanan/konfirmasi/${jadwalId}/${status}`;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function deleteSchedule(id) {
        if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/pelayanan/${id}`;
            
            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfField);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function findReplacement(replacementId = null, jadwalId = null) {
        const modalBody = document.getElementById('replacementModalBody');
        modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Mencari kandidat pengganti...</p>
            </div>
        `;
        
        const url = replacementId ? 
            `/pelayanan/api/replacement-candidates/${replacementId}` : 
            `/pelayanan/api/schedule-replacement-candidates/${jadwalId}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.candidates && data.candidates.length > 0) {
                    let html = `
                        <form id="replacementForm">
                            <input type="hidden" name="replacement_id" value="${replacementId}">
                            <input type="hidden" name="jadwal_id" value="${jadwalId}">
                            
                            <div class="mb-3">
                                <h6>Kandidat Pengganti:</h6>
                                <div class="list-group">
                    `;
                    
                    data.candidates.forEach((candidate, index) => {
                        const categoryBadge = candidate.category === 'same_position' ? 
                            '<span class="badge bg-success">Posisi Sama</span>' : 
                            '<span class="badge bg-warning">Posisi Berbeda</span>';
                        
                        const regularBadge = candidate.is_reguler ? 
                            '<span class="badge bg-primary ms-1">Reguler</span>' : '';
                        
                        html += `
                            <div class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="replacement_candidate" 
                                           value="${candidate.anggota.id_anggota}" id="candidate${index}"
                                           ${index === 0 ? 'checked' : ''}>
                                    <label class="form-check-label w-100" for="candidate${index}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${candidate.anggota.nama}</strong>
                                                ${categoryBadge}${regularBadge}
                                                <br><small class="text-muted">Score: ${Math.round(candidate.score)}</small>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">Prioritas: ${candidate.prioritas || 0}/10</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += `
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan (Opsional):</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Alasan penggantian atau catatan lainnya..."></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary" onclick="assignReplacement()">Assign Pengganti</button>
                            </div>
                        </form>
                    `;
                } else {
                    html = `
                        <div class="text-center py-4">
                            <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
                            <h5>Tidak Ada Kandidat Tersedia</h5>
                            <p class="text-muted">Tidak ditemukan anggota yang tersedia untuk menggantikan posisi ini.</p>
                            <form id="noReplacementForm">
                                <input type="hidden" name="replacement_id" value="${replacementId}">
                                <input type="hidden" name="jadwal_id" value="${jadwalId}">
                                <div class="mb-3">
                                    <label class="form-label">Catatan:</label>
                                    <textarea class="form-control" name="notes" rows="2" placeholder="Alasan mengapa tidak ada pengganti..." required></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-warning" onclick="markNoReplacement()">Tandai Tidak Ada Pengganti</button>
                                </div>
                            </form>
                        </div>
                    `;
                }
                
                modalBody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Terjadi kesalahan saat mencari kandidat pengganti.
                    </div>
                `;
            });
    }
    
    function assignReplacement() {
        const form = document.getElementById('replacementForm');
        const formData = new FormData(form);
        
        fetch('/pelayanan/api/assign-replacement', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                replacement_id: formData.get('replacement_id'),
                jadwal_id: formData.get('jadwal_id'),
                candidate_id: formData.get('replacement_candidate'),
                notes: formData.get('notes')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pengganti berhasil di-assign!');
                location.reload();
            } else {
                alert('Gagal assign pengganti: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat assign pengganti');
        });
    }
    
    function markNoReplacement() {
        const form = document.getElementById('noReplacementForm');
        const formData = new FormData(form);
        
        fetch('/pelayanan/api/mark-no-replacement', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                replacement_id: formData.get('replacement_id'),
                jadwal_id: formData.get('jadwal_id'),
                notes: formData.get('notes')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status berhasil diupdate!');
                location.reload();
            } else {
                alert('Gagal update status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat update status');
        });
    }
    
    function copySchedule(pelaksanaanId) {
        if (confirm('Copy jadwal pelayanan ini untuk pelaksanaan lain?')) {
            // Implementation for copying schedule
            alert('Fitur copy jadwal akan segera tersedia!');
        }
    }
    
    function changeAssignee(jadwalId) {
        // Implementation for changing assignee
        alert('Fitur ganti petugas akan segera tersedia!');
    }
    
    function sendNotifications() {
        if (confirm('Kirim notifikasi reminder ke semua anggota yang belum konfirmasi?')) {
            fetch('{{ route("pelayanan.send-notifications") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    date: '{{ now()->addDays(7)->format("Y-m-d") }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Notifikasi berhasil dikirim!');
                } else {
                    alert('Gagal mengirim notifikasi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim notifikasi');
            });
        }
    }
</script>
@endsection