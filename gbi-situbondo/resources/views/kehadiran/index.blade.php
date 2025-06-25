@extends('layouts.app')

@section('title', 'Presensi Kehadiran')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Presensi Kehadiran</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Presensi Kehadiran</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-clipboard-check me-1"></i>
            Jadwal Kegiatan & Presensi
        </div>
        <div class="card-body">
            @if(count($pelaksanaan) > 0)
                <div class="table-responsive">
                    <table id="datatablesSimple" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Kegiatan</th>
                                <th data-sort="date">Tanggal</th>
                                <th>Waktu</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>Kehadiran</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pelaksanaan as $p)
                                @php
                                    $user = Auth::user();
                                    $eventDate = \Carbon\Carbon::parse($p->tanggal_kegiatan);
                                    
                                    // Prevent double time specification error
                                    try {
                                        $eventStartTime = $eventDate->copy()->setTimeFromTimeString($p->jam_mulai);
                                        $eventEndTime = $eventDate->copy()->setTimeFromTimeString($p->jam_selesai);
                                    } catch (\Exception $e) {
                                        $eventStartTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                                            $eventDate->format('Y-m-d') . ' ' . substr($p->jam_mulai, 0, 5));
                                        $eventEndTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                                            $eventDate->format('Y-m-d') . ' ' . substr($p->jam_selesai, 0, 5));
                                    }
                                    
                                    $now = \Carbon\Carbon::now();
                                    $isToday = $eventDate->isToday();
                                    $isPast = $eventEndTime->isPast() && !$isToday;
                                    $isUpcoming = $eventStartTime->isFuture();
                                    
                                    // QR Code availability: 30 minutes before start until event ends
                                    $qrStartTime = $eventStartTime->copy()->subMinutes(30);
                                    $qrEndTime = $eventEndTime->copy();
                                    $isQrAvailable = $now->between($qrStartTime, $qrEndTime);
                                    
                                    // Attendance availability: from event start time until event ends
                                    $attendanceStartTime = $eventStartTime->copy();
                                    $canTakeAttendance = $now->gte($attendanceStartTime) && $now->lte($eventEndTime);
                                    
                                    // Check if user already attended
                                    $userAttended = false;
                                    if ($user->id_anggota) {
                                        $userAttended = $p->kehadiran->contains('id_anggota', $user->id_anggota);
                                    }
                                    
                                    $showManualPresensi = $user->id_role <= 3; // Admin/Pengurus/Petugas
                                    
                                    // Create proper sort value: YYYYMMDD format for proper sorting
                                    $sortValue = $eventDate->format('Ymd');
                                @endphp
                                
                                <tr class="{{ $isPast ? 'table-secondary' : ($isToday ? 'table-warning' : '') }}">
                                    <td>
                                        {{ $p->kegiatan->nama_kegiatan }}
                                        @if($p->kegiatan->tipe_kegiatan === 'komsel')
                                            <br><small class="text-primary">
                                                <i class="fas fa-users"></i> Kelompok Sel
                                            </small>
                                        @elseif($p->kegiatan->tipe_kegiatan === 'ibadah')
                                            <br><small class="text-success">
                                                <i class="fas fa-church"></i> Ibadah
                                            </small>
                                        @endif
                                        
                                        @if($isPast)
                                            <br><small class="text-muted"><i class="fas fa-history"></i> Selesai</small>
                                        @elseif($isToday)
                                            <br><small class="text-warning"><i class="fas fa-clock"></i> Hari ini</small>
                                        @endif
                                    </td>
                                    <td data-order="{{ $sortValue }}">
                                        {{ $eventDate->format('d/m/Y') }}
                                        @if($isToday)
                                            <span class="badge bg-warning text-dark ms-1">Hari ini</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($p->jam_selesai)->format('H:i') }}
                                        
                                        @if($user->id_role > 3 && !$isPast)
                                            <br><small class="text-muted">
                                                QR: {{ $qrStartTime->format('H:i') }} - {{ $qrEndTime->format('H:i') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $p->lokasi ?: '-' }}</td>
                                    <td>
                                        @if($isPast)
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-history"></i> Selesai
                                            </span>
                                        @elseif($isToday && $canTakeAttendance)
                                            <span class="badge bg-success">
                                                <i class="fas fa-play"></i> Sedang Berlangsung
                                            </span>
                                        @elseif($isToday && $isQrAvailable && !$canTakeAttendance)
                                            <span class="badge bg-info">
                                                <i class="fas fa-qrcode"></i> QR Tersedia
                                            </span>
                                        @elseif($isToday)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Belum Dimulai
                                            </span>
                                            @if($user->id_role > 3 && $now->lt($qrStartTime))
                                                <br><small class="text-muted">
                                                    QR dalam {{ round($now->diffInMinutes($qrStartTime)) }} menit
                                                </small>
                                            @endif
                                        @else
                                            <span class="badge bg-primary">
                                                <i class="fas fa-calendar"></i> Akan Datang
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($userAttended)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Sudah Hadir
                                            </span>
                                        @elseif($isPast)
                                            <span class="text-muted">-</span>
                                        @else
                                            <span class="badge bg-outline-secondary">
                                                {{ $p->kehadiran->count() }} orang hadir
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($showManualPresensi)
                                                <!-- Manual Presensi for Admin/Pengurus/Petugas -->
                                                @if($canTakeAttendance || $isPast)
                                                    <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $p->id_pelaksanaan]) }}" 
                                                       class="btn btn-primary btn-sm" title="Presensi Manual">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </a>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled 
                                                            title="Presensi belum dapat dilakukan (kegiatan belum dimulai)">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </button>
                                                @endif
                                            @endif
                                            
                                            <!-- QR Code Button -->
                                            @if($user->id_role > 3)
                                                <!-- QR Code for Regular Members (with time restriction) -->
                                                @if($isQrAvailable && !$userAttended)
                                                    <a href="{{ route('kehadiran.scan', $p->id_pelaksanaan) }}" 
                                                       class="btn btn-success btn-sm" title="Scan QR Code">
                                                        <i class="fas fa-qrcode"></i>
                                                    </a>
                                                @elseif($userAttended)
                                                    <button class="btn btn-outline-success btn-sm" disabled title="Sudah Presensi">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @elseif($isPast)
                                                    <button class="btn btn-outline-secondary btn-sm" disabled title="Sudah Berakhir">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline-secondary btn-sm" disabled 
                                                            title="QR Code belum tersedia atau sudah berakhir">
                                                        <i class="fas fa-qrcode"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <!-- QR Code for Admin/Pengurus/Petugas (with time restriction) -->
                                                @if($canTakeAttendance || $isPast)
                                                    <a href="{{ route('kehadiran.scan', $p->id_pelaksanaan) }}" 
                                                       class="btn btn-success btn-sm" title="Lihat QR Code">
                                                        <i class="fas fa-qrcode"></i>
                                                    </a>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled 
                                                            title="QR Code belum tersedia (kegiatan belum dimulai)">
                                                        <i class="fas fa-qrcode"></i>
                                                    </button>
                                                @endif
                                            @endif
                                            
                                            <!-- View Details Button -->
                                            @if($showManualPresensi || $p->kehadiran->count() > 0)
                                                <a href="{{ route('pelaksanaan.show', $p->id_pelaksanaan) }}" 
                                                   class="btn btn-info btn-sm" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                

            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Tidak Ada Jadwal Kegiatan</h4>
                    <p class="text-muted">Tidak ada jadwal kegiatan yang dapat diikuti saat ini.</p>
                    @if(auth()->user()->id_role <= 3)
                        <a href="{{ route('pelaksanaan.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Jadwal Kegiatan
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.table-secondary td {
    opacity: 0.7;
}

.table-warning td {
    background-color: #fff3cd !important;
}

.badge {
    font-size: 0.75em;
}

.btn[disabled] {
    cursor: not-allowed;
}

.alert ul {
    padding-left: 1.2rem;
}

/* Custom badge for outlined style */
.badge.bg-outline-secondary {
    background-color: transparent !important;
    color: #6c757d;
    border: 1px solid #6c757d;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable with proper sorting
    $('#datatablesSimple').DataTable({
        order: [[1, 'asc']], // Sort by date column (today first, then future, then past)
        columnDefs: [
            {
                targets: 1, // Date column
                type: 'num', // Use numeric type for YYYYMMDD format
                render: function(data, type, row) {
                    if (type === 'display') {
                        return data;
                    } else if (type === 'sort' || type === 'type') {
                        // Extract the data-order value for sorting
                        var $data = $(data);
                        if ($data.length) {
                            return $data.attr('data-order') || '0';
                        } else {
                            return data;
                        }
                    }
                    return data;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        pageLength: 25, // Show more rows by default
        responsive: true
    });
    
    // Auto-refresh page every 5 minutes to update QR availability (for regular members only)
    @if(Auth::user()->id_role > 3)
        setInterval(function() {
            location.reload();
        }, 300000); // 5 minutes
    @endif
    
    // Add countdown timer for buttons with time restrictions
    function updateCountdowns() {
        $('.btn[title*="menit"]').each(function() {
            var $btn = $(this);
            var match = $btn.text().match(/(\d+)m/);
            if (match) {
                var minutes = parseInt(match[1]) - 1;
                if (minutes > 0) {
                    $btn.html($btn.html().replace(/\d+m/, minutes + 'm'));
                } else {
                    location.reload(); // Refresh when countdown reaches 0
                }
            }
        });
    }
    
    // Update countdowns every minute
    setInterval(updateCountdowns, 60000);
});
</script>
@endsection