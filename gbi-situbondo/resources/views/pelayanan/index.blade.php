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
                                    <a href="{{ route('pelayanan.analytics') }}" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-chart-bar"></i> Analytics
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Reports & Export -->
                        @if(Auth::user()->id_role <= 2)
                        <div class="col-md-3">
                            <h6 class="text-warning">Reports & Export</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('pelayanan.export') }}?format=excel" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sendNotifications()">
                                    <i class="fas fa-bell"></i> Kirim Notifikasi
                                </button>
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
    
    <!-- Jadwal Pelayanan Mendatang -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-check me-1"></i>
            Jadwal Pelayanan Mendatang
        </div>
        <div class="card-body">
            @if($jadwalPelayanan->isNotEmpty())
                @foreach($jadwalPelayanan as $tanggal => $jadwalList)
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 d-flex justify-content-between align-items-center">
                            <span>{{ \Carbon\Carbon::parse($tanggal)->format('l, d F Y') }}</span>
                            <span class="badge bg-primary">{{ $jadwalList->count() }} posisi</span>
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kegiatan</th>
                                        <th>Posisi</th>
                                        <th>Petugas</th>
                                        <th>Status</th>
                                        <th>Regular</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jadwalList as $jadwal)
                                        <tr class="{{ $jadwal->status_konfirmasi === 'belum' ? 'table-warning' : ($jadwal->status_konfirmasi === 'terima' ? 'table-success' : 'table-danger') }}">
                                            <td>
                                                <strong>{{ $jadwal->pelaksanaan->kegiatan->nama_kegiatan ?? $jadwal->kegiatan->nama_kegiatan ?? 'N/A' }}</strong>
                                                @if($jadwal->pelaksanaan)
                                                    <br><small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($jadwal->pelaksanaan->jam_mulai)->format('H:i') }} - 
                                                        {{ \Carbon\Carbon::parse($jadwal->pelaksanaan->jam_selesai)->format('H:i') }}
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
                                                    @case('belum')
                                                        <span class="badge bg-warning">Belum Konfirmasi</span>
                                                        @break
                                                    @case('terima')
                                                        <span class="badge bg-success">Diterima</span>
                                                        @break
                                                    @case('tolak')
                                                        <span class="badge bg-danger">Ditolak</span>
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
                                                            <a href="{{ route('pelayanan.konfirmasi', [$jadwal->id_pelayanan, 'terima']) }}" 
                                                               class="btn btn-success" title="Terima">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                            <a href="{{ route('pelayanan.konfirmasi', [$jadwal->id_pelayanan, 'tolak']) }}" 
                                                               class="btn btn-danger" title="Tolak">
                                                                <i class="fas fa-times"></i>
                                                            </a>
                                                        @endif
                                                        
                                                        @if(Auth::user()->id_role <= 3)
                                                            <button type="button" class="btn btn-warning" 
                                                                    onclick="editSchedule({{ $jadwal->id_pelayanan }})" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger" 
                                                                    onclick="deleteSchedule({{ $jadwal->id_pelayanan }})" title="Hapus">
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
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Riwayat Pelayanan (10 Terakhir)
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
                
                @if($riwayatPelayanan->count() > 3)
                    <div class="text-center">
                        <a href="{{ route('pelayanan.member-history', Auth::user()->id_anggota ?? 0) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list"></i> Lihat Semua Riwayat
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-3">
                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Belum ada riwayat pelayanan</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal for Edit Schedule -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jadwal Pelayanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Form edit akan dimuat di sini -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function editSchedule(id) {
        // Implementation untuk edit schedule
        alert('Edit schedule feature akan segera tersedia!');
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
    
    // Auto refresh notification for new schedules
    setInterval(function() {
        // Check for new notifications
        fetch('{{ route("pelayanan.index") }}?check_updates=1')
            .then(response => response.json())
            .then(data => {
                if (data.has_updates) {
                    // Show notification badge or update UI
                }
            })
            .catch(error => console.log('Update check failed'));
    }, 30000); // Check every 30 seconds
</script>
@endsection