@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">Dashboard</h1>
            <p class="text-muted mb-0">{{ $data['role_name'] ?? 'Dashboard' }} - {{ now()->format('l, d F Y') }}</p>
        </div>
        @if($user->id_role <= 3)
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-plus"></i> Quick Actions
            </button>
            <ul class="dropdown-menu">
                @if($user->id_role <= 2)
                <li><a class="dropdown-item" href="{{ route('anggota.create') }}"><i class="fas fa-user-plus me-2"></i>Tambah Anggota</a></li>
                <li><a class="dropdown-item" href="{{ route('kegiatan.create') }}"><i class="fas fa-calendar-plus me-2"></i>Buat Kegiatan</a></li>
                @endif
                @if($user->id_role <= 3)
                <li><a class="dropdown-item" href="{{ route('kehadiran.create') }}"><i class="fas fa-clipboard-check me-2"></i>Input Presensi</a></li>
                <li><a class="dropdown-item" href="{{ route('pelayanan.create') }}"><i class="fas fa-hand-holding-heart me-2"></i>Jadwal Pelayanan</a></li>
                @endif
            </ul>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ADMIN DASHBOARD --}}
    @if($user->id_role == 1)
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total Users</div>
                            <div class="fs-4 fw-bold">{{ $data['total_users'] }}</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('users.index') }}">Kelola Users</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total Anggota</div>
                            <div class="fs-4 fw-bold">{{ $data['total_anggota'] }}</div>
                            <div class="small">+{{ $data['new_anggota_this_month'] }} bulan ini</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-user-friends"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('anggota.index') }}">Kelola Anggota</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Keluarga</div>
                            <div class="fs-4 fw-bold">{{ $data['total_keluarga'] }}</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-home"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('keluarga.index') }}">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- UPDATED: Perhatian Sistem Section - Horizontal Layout --}}
    @if($data['anggota_without_users']->isNotEmpty() || $data['anggota_without_komsel']->isNotEmpty() || $data['anggota_absent_consecutive']->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Perhatian Sistem</h6>
                
                <div class="row">
                    {{-- UPDATED: Anggota without users with list --}}
                    @if($data['anggota_without_users']->isNotEmpty())
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-warning border-3 ps-3">
                            <strong class="text-danger">{{ $data['anggota_without_users']->count() }} anggota belum memiliki akun user:</strong>
                            <div class="mt-2">
                                @foreach($data['anggota_without_users']->take(5) as $anggota)
                                <div class="mb-1">
                                    <a href="{{ route('anggota.show', $anggota->id_anggota) }}" class="text-decoration-none text-danger fw-bold">
                                        {{ $anggota->nama }}
                                    </a>
                                    <br><small class="text-muted">{{ $anggota->email ?: 'Tidak ada email' }}</small>
                                </div>
                                @endforeach
                                @if($data['anggota_without_users']->count() > 5)
                                <div class="small text-muted mt-1">
                                    <a href="{{ route('anggota.index') }}?filter=no_user" class="text-decoration-none">
                                        +{{ $data['anggota_without_users']->count() - 5 }} lainnya...
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    {{-- UPDATED: Anggota without komsel with list --}}
                    @if($data['anggota_without_komsel']->isNotEmpty())
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-info border-3 ps-3">
                            <strong class="text-danger">{{ $data['anggota_without_komsel']->count() }} anggota belum bergabung komsel:</strong>
                            <div class="mt-2">
                                @foreach($data['anggota_without_komsel']->take(5) as $anggota)
                                <div class="mb-1">
                                    <a href="{{ route('anggota.show', $anggota->id_anggota) }}" class="text-decoration-none text-danger fw-bold">
                                        {{ $anggota->nama }}
                                    </a>
                                    <br><small class="text-muted">{{ $anggota->keluarga->nama_keluarga ?? 'Tidak ada keluarga' }}</small>
                                </div>
                                @endforeach
                                @if($data['anggota_without_komsel']->count() > 5)
                                <div class="small text-muted mt-1">
                                    <a href="{{ route('anggota.index') }}?filter=no_komsel" class="text-decoration-none">
                                        +{{ $data['anggota_without_komsel']->count() - 5 }} lainnya...
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    {{-- Consecutive Absence Alert --}}
                    @if($data['anggota_absent_consecutive']->isNotEmpty())
                    <div class="col-md-4 mb-3">
                        <div class="border-start border-danger border-3 ps-3">
                            <strong class="text-danger">{{ $data['anggota_absent_consecutive']->count() }} anggota absen 3x berturut-turut:</strong>
                            <div class="mt-2">
                                @foreach($data['anggota_absent_consecutive']->take(5) as $absentMember)
                                <div class="mb-1">
                                    <a href="{{ route('anggota.show', $absentMember['id_anggota']) }}" class="text-decoration-none text-danger fw-bold">
                                        {{ $absentMember['nama'] }}
                                    </a>
                                    <br><small class="text-muted">{{ $absentMember['last_attendance'] }}</small>
                                </div>
                                @endforeach
                                @if($data['anggota_absent_consecutive']->count() > 5)
                                <div class="small text-muted mt-1">
                                    <a href="{{ route('laporan.kehadiran') }}" class="text-decoration-none">
                                        +{{ $data['anggota_absent_consecutive']->count() - 5 }} lainnya...
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>User Terbaru
                </div>
                <div class="card-body">
                    @if($data['recent_users']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Role</th>
                                    <th>Anggota</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['recent_users'] as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $user->role->nama_role ?? 'N/A' }}</span></td>
                                    <td>{{ $user->anggota->nama ?? 'Belum terhubung' }}</td>
                                    <td class="text-muted small">{{ $user->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-center text-muted">Belum ada user baru</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar me-1"></i>Kegiatan Mendatang
                </div>
                <div class="card-body">
                    @if($data['upcoming_events']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($data['upcoming_events'] as $event)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $event->kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">{{ Carbon\Carbon::parse($event->tanggal_kegiatan)->format('d M Y') }} • {{ Carbon\Carbon::parse($event->jam_mulai)->format('H:i') }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $event->kegiatan->tipe_kegiatan }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-center text-muted">Tidak ada kegiatan mendatang</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PENGURUS GEREJA DASHBOARD --}}
    @if($user->id_role == 2)
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total Anggota</div>
                            <div class="fs-4 fw-bold">{{ $data['total_anggota'] }}</div>
                            <div class="small">+{{ $data['new_anggota_this_month'] }} bulan ini</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('anggota.index') }}">Kelola Anggota</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Keluarga</div>
                            <div class="fs-4 fw-bold">{{ $data['total_keluarga'] }}</div>
                            <div class="small">+{{ $data['new_families_this_month'] }} bulan ini</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-home"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('keluarga.index') }}">Kelola Keluarga</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Komsel</div>
                            <div class="fs-4 fw-bold">{{ $data['total_komsel'] }}</div>
                            <div class="small">Rata-rata {{ number_format($data['average_komsel_size'], 1) }} anggota</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('komsel.index') }}">Kelola Komsel</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Kehadiran Bulan Ini</div>
                            <div class="fs-4 fw-bold">{{ $data['active_members_this_month'] }}</div>
                            <div class="small">{{ $data['attendance_this_week'] }} minggu ini</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('laporan.index') }}">Lihat Laporan</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-plus me-1"></i>Anggota Baru
                </div>
                <div class="card-body">
                    @if($data['recent_anggota']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($data['recent_anggota'] as $anggota)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $anggota->nama }}</div>
                                <small class="text-muted">{{ $anggota->keluarga->nama_keluarga ?? 'Belum ada keluarga' }}</small>
                            </div>
                            <small class="text-muted">{{ $anggota->created_at->format('d/m/Y') }}</small>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-center text-muted">Belum ada anggota baru bulan ini</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-calendar me-1"></i>Kegiatan Mendatang</span>
                    <a href="{{ route('kegiatan.calendar') }}" class="btn btn-sm btn-outline-primary">Lihat Kalender</a>
                </div>
                <div class="card-body">
                    @if($data['upcoming_events']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($data['upcoming_events'] as $event)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $event->kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">{{ Carbon\Carbon::parse($event->tanggal_kegiatan)->format('d M Y') }} • {{ Carbon\Carbon::parse($event->jam_mulai)->format('H:i') }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $event->kegiatan->tipe_kegiatan }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-center text-muted">Tidak ada kegiatan mendatang</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PETUGAS PELAYANAN DASHBOARD --}}
    @if($user->id_role == 3)
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Konfirmasi Pending</div>
                            <div class="fs-4 fw-bold">{{ $data['pending_confirmations'] }}</div>
                            <div class="small">Butuh perhatian</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('pelayanan.index') }}">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Presensi Hari Ini</div>
                            <div class="fs-4 fw-bold">{{ $data['attendance_today'] }}</div>
                            <div class="small">orang hadir</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-clipboard-check"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('kehadiran.index') }}">Input Presensi</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Pelayanan Bulan Ini</div>
                            <div class="fs-4 fw-bold">{{ $data['total_services_this_month'] }}</div>
                            <div class="small">{{ $data['active_servants'] }} pelayan aktif</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-hand-holding-heart"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('pelayanan.generator') }}">Generate Jadwal</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Perlu Dijadwalkan</div>
                            <div class="fs-4 fw-bold">{{ $data['members_need_scheduling'] }}</div>
                            <div class="small">anggota</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-calendar-plus"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('pelayanan.members') }}">Lihat Anggota</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    @if($data['pending_services_detail']->count() > 0)
    <div class="alert alert-warning">
        <h6><i class="fas fa-clock me-2"></i>Konfirmasi Pending (7 Hari Ke Depan)</h6>
        <div class="row">
            @foreach($data['pending_services_detail'] as $service)
            <div class="col-md-6">
                <small>• <strong>{{ $service->anggota->nama }}</strong> - {{ $service->posisi }} ({{ Carbon\Carbon::parse($service->tanggal_pelayanan)->format('d/m') }})</small>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-day me-1"></i>Kegiatan Hari Ini
                </div>
                <div class="card-body">
                    @if($data['todays_events']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($data['todays_events'] as $event)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $event->kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">{{ Carbon\Carbon::parse($event->jam_mulai)->format('H:i') }} - {{ Carbon\Carbon::parse($event->jam_selesai)->format('H:i') }}</small>
                            </div>
                            <div>
                                <a href="{{ route('kehadiran.scan', $event->id_pelaksanaan) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-qrcode"></i> QR
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-center text-muted">Tidak ada kegiatan hari ini</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-week me-1"></i>Minggu Ini
                </div>
                <div class="card-body">
                    @if($data['events_this_week']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($data['events_this_week'] as $event)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $event->kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">{{ Carbon\Carbon::parse($event->tanggal_kegiatan)->format('D, d/m') }} • {{ Carbon\Carbon::parse($event->jam_mulai)->format('H:i') }}</small>
                            </div>
                            <span class="badge bg-{{ $event->kehadiran->count() > 0 ? 'success' : 'secondary' }} rounded-pill">
                                {{ $event->kehadiran->count() }} hadir
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-center text-muted">Tidak ada kegiatan minggu ini</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ANGGOTA JEMAAT DASHBOARD --}}
    @if($user->id_role == 4)
    @if(isset($data['profile_incomplete']) && $data['profile_incomplete'])
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Profil Belum Lengkap</h5>
        <p>{{ $data['message'] }}</p>
        <a href="{{ route('profile.show') }}" class="btn btn-warning">Lihat Profil</a>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Selamat datang, {{ $data['anggota']->nama }}!</h5>
                            <p class="card-text mb-0">{{ $data['my_pending_confirmations']->count() > 0 ? 'Anda memiliki jadwal pelayanan yang perlu dikonfirmasi' : 'Tidak ada yang perlu dikonfirmasi saat ini' }}</p>
                        </div>
                        <div class="text-end">
                            @if($data['my_pending_confirmations']->count() > 0)
                            <div class="fs-4">{{ $data['my_pending_confirmations']->count() }}</div>
                            <small>Perlu konfirmasi</small>
                            @else
                            <i class="fas fa-check-circle fs-1"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($data['my_pending_confirmations']->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <h6><i class="fas fa-clock me-2"></i>Konfirmasi Jadwal Pelayanan</h6>
                <div class="row">
                    @foreach($data['my_pending_confirmations'] as $service)
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $service->pelaksanaan->kegiatan->nama_kegiatan }}</strong><br>
                                <small>{{ $service->posisi }} - {{ Carbon\Carbon::parse($service->tanggal_pelayanan)->format('d M Y') }}</small>
                            </div>
                            <div>
                                <a href="{{ route('pelayanan.konfirmasi', [$service->id_pelayanan, 'terima']) }}" class="btn btn-sm btn-success me-1">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="{{ route('pelayanan.konfirmasi', [$service->id_pelayanan, 'tolak']) }}" class="btn btn-sm btn-danger">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Pelayanan Saya</div>
                            <div class="fs-4 fw-bold">{{ $data['my_upcoming_services']->count() }}</div>
                            <div class="small">mendatang</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-hand-holding-heart"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('pelayanan.index') }}">Lihat Jadwal</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Komsel Saya</div>
                            <div class="fs-4 fw-bold">{{ $data['my_komsel']->count() }}</div>
                            <div class="small">kelompok sel</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('komsel.index') }}">Lihat Komsel</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Kehadiran Bulan Ini</div>
                            <div class="fs-4 fw-bold">{{ $data['my_attendance_this_month'] }}</div>
                            <div class="small">kali hadir</div>
                        </div>
                        <div class="fs-1 opacity-50"><i class="fas fa-calendar-check"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('laporan.personal-report') }}">Lihat Riwayat</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="text-center">
                        <div class="fs-1 mb-2"><i class="fas fa-qrcode"></i></div>
                        <div class="small">Scan QR</div>
                        <div class="small">Presensi Cepat</div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    {{-- FIXED: Changed from kehadiran.scan to kehadiran.index for general attendance page --}}
                    <a class="small text-white stretched-link" href="{{ route('kehadiran.index') }}">Buka Presensi</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if($data['todays_events']->count() > 0)
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-day me-1"></i>Kegiatan Hari Ini
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($data['todays_events'] as $event)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $event->kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">{{ Carbon\Carbon::parse($event->jam_mulai)->format('H:i') }} - {{ Carbon\Carbon::parse($event->jam_selesai)->format('H:i') }}</small>
                                @if($event->lokasi)
                                <br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $event->lokasi }}</small>
                                @endif
                            </div>
                            <a href="{{ route('kehadiran.scan', $event->id_pelaksanaan) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-qrcode"></i> Presensi
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-hand-holding-heart me-1"></i>Jadwal Pelayanan Mendatang
                </div>
                <div class="card-body">
                    @if($data['my_upcoming_services']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($data['my_upcoming_services'] as $service)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $service->pelaksanaan->kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">{{ $service->posisi }} • {{ Carbon\Carbon::parse($service->tanggal_pelayanan)->format('d M Y') }}</small>
                            </div>
                            <span class="badge bg-{{ $service->status_konfirmasi == 'terima' ? 'success' : ($service->status_konfirmasi == 'tolak' ? 'danger' : 'warning') }}">
                                {{ ucfirst($service->status_konfirmasi) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-center text-muted">Tidak ada jadwal pelayanan mendatang</p>
                    @endif
                </div>
            </div>
        </div>

        @if($data['upcoming_komsel_meetings']->count() > 0)
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>Pertemuan Komsel Mendatang
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($data['upcoming_komsel_meetings'] as $meeting)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $meeting->kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">{{ Carbon\Carbon::parse($meeting->tanggal_kegiatan)->format('d M Y') }} • {{ Carbon\Carbon::parse($meeting->jam_mulai)->format('H:i') }}</small>
                            </div>
                            <a href="{{ route('kehadiran.scan', $meeting->id_pelaksanaan) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-qrcode"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($data['my_family']->count() > 0)
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-home me-1"></i>Keluarga Saya
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($data['my_family'] as $family)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-bold">{{ $family->nama }}</div>
                                <small class="text-muted">{{ $data['anggota']->getHubunganDengan($family->id_anggota) }}</small>
                            </div>
                            <small class="text-muted">{{ Carbon\Carbon::parse($family->tanggal_lahir)->age }} tahun</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
    @endif

</div>

<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-body {
    padding: 1.5rem;
}

.badge {
    font-size: 0.75em;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid rgba(0,0,0,0.125);
    padding: 0.75rem 0;
}

.list-group-item:last-child {
    border-bottom: none;
}

.alert {
    border: none;
    border-radius: 10px;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .fs-4 {
        font-size: 1.25rem !important;
    }
    
    .fs-1 {
        font-size: 1.5rem !important;
    }
}
</style>
@endsection