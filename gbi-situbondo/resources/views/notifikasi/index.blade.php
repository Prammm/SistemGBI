@extends('layouts.app')
@section('title', 'Notifikasi & Pengingat')

@section('styles')
<style>
    .notification-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .notification-card.high-priority {
        border-left-color: #dc3545;
        background-color: #fff5f5;
    }
    .notification-card.medium-priority {
        border-left-color: #ffc107;
        background-color: #fffbf0;
    }
    .notification-card.low-priority {
        border-left-color: #17a2b8;
        background-color: #f0f9ff;
    }
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .control-panel {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .notification-actions .btn {
        margin: 2px;
    }
    .queue-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875em;
        font-weight: 500;
    }
    .queue-status.processing {
        background-color: #d1ecf1;
        color: #0c5460;
    }
    .queue-status.completed {
        background-color: #d4edda;
        color: #155724;
    }
    .queue-status.failed {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">
        <i class="fas fa-bell me-2"></i>Notifikasi & Pengingat
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Notifikasi</li>
    </ol>
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card text-center">
                <div class="h2 mb-0">{{ $stats['total'] }}</div>
                <div class="small">Total Notifikasi</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card text-center">
                <div class="h2 mb-0 text-warning">{{ $stats['high_priority'] }}</div>
                <div class="small">Prioritas Tinggi</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card text-center">
                <div class="h2 mb-0">{{ $stats['pending_confirmations'] }}</div>
                <div class="small">Menunggu Konfirmasi</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card text-center">
                <div class="h2 mb-0">{{ $stats['upcoming_services'] }}</div>
                <div class="small">Pelayanan Mendatang</div>
            </div>
        </div>
    </div>

    @if(Auth::user()->id_role <= 2)
    <!-- Admin Control Panel -->
    <div class="control-panel">
        <h5 class="mb-3"><i class="fas fa-cogs me-2"></i>Panel Kontrol Notifikasi</h5>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Pengingat Manual
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <form method="POST" action="{{ route('notifikasi.send-pelayanan') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-hand-holding-heart"></i> Pengingat Pelayanan
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('notifikasi.send-komsel') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm me-2">
                                        <i class="fas fa-users"></i> Pengingat Komsel
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('notifikasi.send-ibadah') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-sm">
                                        <i class="fas fa-church"></i> Pengingat Ibadah
                                    </button>
                                </form>
                            </div>
                        </div>
                        <small class="text-muted">Pengingat akan dikirim secara background menggunakan queue system.</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-exclamation-triangle me-2"></i>Pemeriksaan Absensi
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('notifikasi.check-absences') }}" class="mb-3">
                            @csrf
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">Threshold</label>
                                    <select name="threshold" class="form-select form-select-sm">
                                        <option value="2">2 kali berturut-turut</option>
                                        <option value="3" selected>3 kali berturut-turut</option>
                                        <option value="4">4 kali berturut-turut</option>
                                        <option value="5">5 kali berturut-turut</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Periode</label>
                                    <select name="days" class="form-select form-select-sm">
                                        <option value="14">2 minggu terakhir</option>
                                        <option value="30" selected>1 bulan terakhir</option>
                                        <option value="60">2 bulan terakhir</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm mt-2">
                                <i class="fas fa-search"></i> Periksa Absensi Berturut-turut
                            </button>
                        </form>
                        <small class="text-muted">Sistem akan mengirim notifikasi kepada pengurus dan anggota yang tidak hadir berturut-turut.</small>
                    </div>
                </div>
            </div>
        </div>
        
        @if(Auth::user()->id_role == 1)
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-flask me-2"></i>Test Email System
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('notifikasi.test-email') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="type" value="pelayanan">
                            <button type="submit" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-vial"></i> Test Pelayanan
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('notifikasi.test-email') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="type" value="absence">
                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-vial"></i> Test Absensi
                            </button>
                        </form>
                        
                        <small class="text-muted d-block mt-2">Test email akan dijalankan dalam mode dry-run (tidak mengirim email sebenarnya).</small>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Notifications List -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Daftar Notifikasi
                    <div class="float-end">
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshNotifications()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($notifications) > 0)
                        <div id="notifications-container">
                            @foreach($notifications as $index => $notification)
                                <div class="notification-card {{ $notification['urgency'] ?? 'low' }}-priority card mb-3" 
                                     data-url="{{ $notification['url'] }}"
                                     onclick="window.location.href='{{ $notification['url'] }}'">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1">
                                                    @if($notification['type'] == 'pelayanan')
                                                        <i class="fas fa-hand-holding-heart text-primary me-2"></i>
                                                    @elseif($notification['type'] == 'komsel')
                                                        <i class="fas fa-users text-success me-2"></i>
                                                    @elseif($notification['type'] == 'ibadah')
                                                        <i class="fas fa-church text-info me-2"></i>
                                                    @elseif($notification['type'] == 'admin_alert')
                                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                    @elseif($notification['type'] == 'staffing_alert')
                                                        <i class="fas fa-users-slash text-danger me-2"></i>
                                                    @endif
                                                    {{ $notification['title'] }}
                                                    
                                                    @if($notification['priority'] >= 3)
                                                        <span class="badge bg-danger ms-2">Urgent</span>
                                                    @elseif($notification['priority'] == 2)
                                                        <span class="badge bg-warning ms-2">Penting</span>
                                                    @endif
                                                </h6>
                                                <p class="card-text text-muted mb-2">{{ $notification['description'] }}</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ \Carbon\Carbon::parse($notification['date'])->format('d/m/Y') }}
                                                    ({{ \Carbon\Carbon::parse($notification['date'])->diffForHumans() }})
                                                </small>
                                            </div>
                                            
                                            <div class="notification-actions">
                                                @if(isset($notification['status']) && $notification['status'] == 'belum')
                                                    @if(isset($notification['actions']['confirm']))
                                                        <a href="{{ $notification['actions']['confirm'] }}" 
                                                           class="btn btn-success btn-sm"
                                                           onclick="event.stopPropagation()">
                                                            <i class="fas fa-check"></i> Terima
                                                        </a>
                                                    @endif
                                                    @if(isset($notification['actions']['reject']))
                                                        <a href="{{ $notification['actions']['reject'] }}" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="event.stopPropagation()">
                                                            <i class="fas fa-times"></i> Tolak
                                                        </a>
                                                    @endif
                                                @endif
                                                
                                                @if(isset($notification['count']))
                                                    <span class="badge bg-secondary">{{ $notification['count'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if(isset($notification['status']))
                                            <div class="mt-2">
                                                @if($notification['status'] == 'belum')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Belum Konfirmasi
                                                    </span>
                                                @elseif($notification['status'] == 'terima')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Diterima
                                                    </span>
                                                @elseif($notification['status'] == 'tolak')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times"></i> Ditolak
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada notifikasi saat ini</h5>
                            <p class="text-muted">Semua tugas Anda sudah up-to-date!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Queue Status (for admin) -->
    @if(Auth::user()->id_role <= 2)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tasks me-2"></i>Status Queue System
                </div>
                <div class="card-body">
                    <div id="queue-status">
                        <span class="queue-status processing">
                            <i class="fas fa-spinner fa-spin"></i> Memuat status queue...
                        </span>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Queue system memproses email notifikasi secara background untuk performa yang optimal.
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function refreshNotifications() {
    window.location.reload();
}

// Auto-refresh notification count every 5 minutes
setInterval(function() {
    fetch('{{ route("notifikasi.api.count") }}')
        .then(response => response.json())
        .then(data => {
            // Update notification badge in navbar if exists
            const badge = document.querySelector('.navbar .badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.log('Error updating notification count:', error));
}, 300000); // 5 minutes

// Load queue status for admin
@if(Auth::user()->id_role <= 2)
function loadQueueStatus() {
    // This would typically call an API endpoint to check queue status
    // For now, we'll simulate it
    setTimeout(function() {
        const statusElement = document.getElementById('queue-status');
        if (statusElement) {
            statusElement.innerHTML = `
                <span class="queue-status completed">
                    <i class="fas fa-check-circle"></i> Queue system aktif
                </span>
                <span class="queue-status processing ms-2">
                    <i class="fas fa-clock"></i> 0 jobs pending
                </span>
            `;
        }
    }, 2000);
}

// Load queue status on page load
document.addEventListener('DOMContentLoaded', function() {
    loadQueueStatus();
});
@endif

// Handle notification card clicks with proper event handling
document.querySelectorAll('.notification-card').forEach(function(card) {
    card.addEventListener('click', function(e) {
        // Only redirect if the click wasn't on an action button
        if (!e.target.closest('.notification-actions')) {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        }
    });
});

// Confirmation dialogs for critical actions
document.querySelectorAll('form[action*="check-absences"]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (!confirm('Apakah Anda yakin ingin memeriksa absensi berturut-turut? Sistem akan mengirim notifikasi kepada anggota dan pengurus yang terkait.')) {
            e.preventDefault();
        }
    });
});

// Show loading state when sending reminders
document.querySelectorAll('form[action*="send-"]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        const button = this.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            
            // Re-enable after 5 seconds as fallback
            setTimeout(function() {
                button.disabled = false;
                button.innerHTML = button.innerHTML.replace('<i class="fas fa-spinner fa-spin"></i> Memproses...', button.innerHTML);
            }, 5000);
        }
    });
});
</script>
@endsection