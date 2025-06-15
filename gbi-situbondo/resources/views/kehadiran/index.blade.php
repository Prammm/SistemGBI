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
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Jadwal Kegiatan Terdekat
                </div>
                <div class="card-body">
                    @if(count($pelaksanaan) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kegiatan</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pelaksanaan as $p)
                                        @php
                                            $user = Auth::user();
                                            $eventDate = \Carbon\Carbon::parse($p->tanggal_kegiatan);
                                            
                                            // PERBAIKAN: Gunakan setTimeFromTimeString untuk menghindari double time specification
                                            try {
                                                $eventStartTime = $eventDate->copy()->setTimeFromTimeString($p->jam_mulai);
                                                $eventEndTime = $eventDate->copy()->setTimeFromTimeString($p->jam_selesai);
                                            } catch (\Exception $e) {
                                                // Fallback jika ada error
                                                $eventStartTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                                                    $eventDate->format('Y-m-d') . ' ' . substr($p->jam_mulai, 0, 5));
                                                $eventEndTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                                                    $eventDate->format('Y-m-d') . ' ' . substr($p->jam_selesai, 0, 5));
                                            }
                                            
                                            $now = \Carbon\Carbon::now();
                                            
                                            // QR Code availability window: 30 minutes before start until event ends
                                            $qrStartTime = $eventStartTime->copy()->subMinutes(30);
                                            $qrEndTime = $eventEndTime->copy();
                                            
                                            $isQrAvailable = $now->between($qrStartTime, $qrEndTime);
                                            $showManualPresensi = $user->id_role <= 3; // Only for Admin, Pengurus, Petugas
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $p->kegiatan->nama_kegiatan }}
                                                @if($p->kegiatan->tipe_kegiatan === 'komsel')
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-users"></i> Kelompok Sel
                                                    </small>
                                                @endif
                                            </td>
                                            <td>{{ $eventDate->format('d/m/Y') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($p->jam_selesai)->format('H:i') }}
                                                
                                                @if($user->id_role > 3)
                                                    <br><small class="text-muted">
                                                        QR Code tersedia: {{ $qrStartTime->format('H:i') }} - {{ $qrEndTime->format('H:i') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($now->lt($qrStartTime))
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-clock"></i> Belum Dimulai
                                                    </span>
                                                    @if($user->id_role > 3)
                                                        <br><small class="text-muted">
                                                            QR tersedia dalam {{ round($now->diffInMinutes($qrStartTime)) }} menit
                                                        </small>
                                                    @endif
                                                @elseif($isQrAvailable)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-qrcode"></i> QR Tersedia
                                                    </span>
                                                    @if($user->id_role > 3)
                                                        <br><small class="text-success">
                                                            Silahkan scan QR Code
                                                        </small>
                                                    @endif
                                                @elseif($now->gt($qrEndTime))
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times"></i> Sudah Berakhir
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($showManualPresensi)
                                                    <!-- Manual Presensi (Only for Admin/Pengurus/Petugas) -->
                                                    <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $p->id_pelaksanaan]) }}" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-clipboard-check"></i> Presensi Manual
                                                    </a>
                                                    <br>
                                                @endif
                                                
                                                @if($user->id_role > 3)
                                                    <!-- QR Code for Regular Members (with time restriction) -->
                                                    @if($isQrAvailable)
                                                        <a href="{{ route('kehadiran.scan', $p->id_pelaksanaan) }}" 
                                                           class="btn btn-success btn-sm">
                                                            <i class="fas fa-qrcode"></i> Scan QR Code
                                                        </a>
                                                    @else
                                                        <button class="btn btn-secondary btn-sm" disabled title="QR Code belum tersedia atau sudah berakhir">
                                                            <i class="fas fa-qrcode"></i> QR Code
                                                            @if($now->lt($qrStartTime))
                                                                ({{ round($now->diffInMinutes($qrStartTime)) }}m lagi)
                                                            @else
                                                                (Berakhir)
                                                            @endif
                                                        </button>
                                                    @endif
                                                @else
                                                    <!-- QR Code for Admin/Pengurus/Petugas (always available) -->
                                                    <a href="{{ route('kehadiran.scan', $p->id_pelaksanaan) }}" 
                                                       class="btn btn-success btn-sm">
                                                        <i class="fas fa-qrcode"></i> QR Code
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(Auth::user()->id_role > 3)
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Informasi Presensi:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>QR Code hanya tersedia 30 menit sebelum acara dimulai hingga acara berakhir</li>
                                    <li>Pastikan Anda berada di lokasi kegiatan saat melakukan presensi</li>
                                    <li>Jika mengalami kesulitan, hubungi petugas pelayanan</li>
                                </ul>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada jadwal kegiatan terdekat yang dapat diikuti.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.75em;
}

.btn[disabled] {
    cursor: not-allowed;
}

.alert ul {
    padding-left: 1.2rem;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh page every 5 minutes to update QR availability
    @if(Auth::user()->id_role > 3)
        setInterval(function() {
            location.reload();
        }, 300000); // 5 minutes
    @endif
});
</script>
@endsection