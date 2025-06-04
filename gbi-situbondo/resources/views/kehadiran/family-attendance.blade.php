@extends('layouts.app')

@section('title', 'Presensi Keluarga')

@section('styles')
<style>
    .family-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .success-header {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
    }
    
    .success-header i {
        font-size: 3rem;
        margin-bottom: 15px;
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
    }
    
    .family-member-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        background: white;
    }
    
    .family-member-card:hover {
        border-color: #007bff;
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.1);
        transform: translateY(-2px);
    }
    
    .family-member-card.selected {
        border-color: #28a745;
        background: #f8fff9;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
    }
    
    .family-member-card.already-attended {
        border-color: #6c757d;
        background: #f8f9fa;
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .family-member-card.already-attended:hover {
        transform: none;
        box-shadow: none;
    }
    
    .member-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .member-details {
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    .member-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .member-text {
        flex: 1;
    }
    
    .member-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .member-relation {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 3px;
    }
    
    .member-status {
        font-size: 0.85rem;
    }
    
    .member-status.available {
        color: #28a745;
    }
    
    .member-status.attended {
        color: #6c757d;
    }
    
    .member-checkbox {
        position: absolute;
        top: 15px;
        right: 15px;
        transform: scale(1.3);
    }
    
    .attended-badge {
        background: #6c757d;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .no-family-card {
        text-align: center;
        padding: 40px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 15px;
        color: #6c757d;
    }
    
    .no-family-card i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .action-buttons {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
        position: sticky;
        bottom: 20px;
        margin-top: 30px;
    }
    
    .selection-summary {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        text-align: center;
    }
    
    .selection-counter {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1976d2;
    }
    
    .event-reminder {
        background: #fff3e0;
        border: 1px solid #ffcc02;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        color: #e65100;
    }
    
    .quick-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    @media (max-width: 768px) {
        .family-container {
            padding: 15px;
        }
        
        .member-details {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .member-avatar {
            margin-bottom: 10px;
            margin-right: 0;
        }
        
        .quick-actions {
            flex-direction: column;
        }
        
        .quick-actions button {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Presensi Keluarga</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kehadiran.index') }}">Presensi Kehadiran</a></li>
        <li class="breadcrumb-item active">Presensi Keluarga</li>
    </ol>
    
    <div class="family-container">
        <div class="success-header">
            <i class="fas fa-check-circle"></i>
            <h3>Presensi Anda Berhasil!</h3>
            <p class="mb-0">{{ $anggota->nama }}, presensi Anda untuk kegiatan <strong>{{ $pelaksanaan->kegiatan->nama_kegiatan }}</strong> telah tercatat.</p>
        </div>
        
        <div class="event-reminder">
            <div class="row">
                <div class="col-md-6">
                    <i class="fas fa-calendar me-2"></i><strong>{{ \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('d F Y') }}</strong>
                </div>
                <div class="col-md-6">
                    <i class="fas fa-clock me-2"></i>{{ \Carbon\Carbon::parse($pelaksanaan->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pelaksanaan->jam_selesai)->format('H:i') }}
                </div>
            </div>
        </div>
        
        @if($familyMembers->count() > 0)
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Absenkan Anggota Keluarga
                    </h5>
                    <small>Pilih anggota keluarga yang hadir bersama Anda</small>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <button type="button" class="btn btn-outline-success btn-sm" id="select-all">
                            <i class="fas fa-check-double"></i> Pilih Semua
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-all">
                            <i class="fas fa-times"></i> Batal Pilih
                        </button>
                    </div>
                    
                    <form action="{{ route('kehadiran.store-family-attendance') }}" method="POST" id="family-form">
                        @csrf
                        <input type="hidden" name="id_pelaksanaan" value="{{ $pelaksanaan->id_pelaksanaan }}">
                        
                        <div id="family-members">
                            @foreach($familyMembers as $member)
                                @php
                                    $isAttended = in_array($member->id_anggota, $attendedFamily);
                                    $relation = $anggota->getHubunganDengan($member->id_anggota);
                                @endphp
                                
                                <div class="family-member-card {{ $isAttended ? 'already-attended' : '' }}" 
                                     data-member-id="{{ $member->id_anggota }}"
                                     {{ $isAttended ? 'data-attended="true"' : '' }}>
                                    
                                    @if(!$isAttended)
                                        <input type="checkbox" 
                                               class="form-check-input member-checkbox" 
                                               name="family_members[]" 
                                               value="{{ $member->id_anggota }}"
                                               id="member_{{ $member->id_anggota }}">
                                    @endif
                                    
                                    <div class="member-info">
                                        <div class="member-details">
                                            <div class="member-avatar">
                                                {{ strtoupper(substr($member->nama, 0, 1)) }}
                                            </div>
                                            <div class="member-text">
                                                <div class="member-name">{{ $member->nama }}</div>
                                                <div class="member-status {{ $isAttended ? 'attended' : 'available' }}">
                                                    @if($isAttended)
                                                        <i class="fas fa-check-circle me-1"></i>Sudah hadir
                                                    @else
                                                        <i class="fas fa-user-clock me-1"></i>Belum presensi
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($isAttended)
                                            <div class="attended-badge">
                                                <i class="fas fa-check"></i> Sudah Hadir
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="selection-summary">
                            <div class="selection-counter">
                                <span id="selected-count">0</span> dari {{ $familyMembers->where('id_anggota', 'not in', $attendedFamily)->count() }} anggota keluarga dipilih
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button type="submit" class="btn btn-success btn-lg me-md-2" id="submit-btn">
                                    <i class="fas fa-check"></i> Konfirmasi Presensi Keluarga
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-home"></i> Selesai
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="no-family-card">
                <i class="fas fa-users"></i>
                <h5>Tidak Ada Anggota Keluarga</h5>
                <p class="mb-4">Anda tidak memiliki anggota keluarga yang terdaftar dalam sistem, atau Anda belum tergabung dalam keluarga.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-home"></i> Kembali ke Dashboard
                </a>
            </div>
        @endif
        
        @if($familyMembers->count() > 0)
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Anda dapat melewati langkah ini dengan menekan tombol "Selesai" jika tidak ada keluarga yang hadir.
                </small>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const familyCards = document.querySelectorAll('.family-member-card:not([data-attended="true"])');
    const selectAllBtn = document.getElementById('select-all');
    const clearAllBtn = document.getElementById('clear-all');
    const selectedCountSpan = document.getElementById('selected-count');
    const submitBtn = document.getElementById('submit-btn');
    const familyForm = document.getElementById('family-form');
    
    // Update selection counter
    function updateSelectionCounter() {
        const checkedBoxes = document.querySelectorAll('.member-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCountSpan.textContent = count;
        
        // Update submit button text
        if (count > 0) {
            submitBtn.innerHTML = `<i class="fas fa-check"></i> Konfirmasi Presensi ${count} Anggota Keluarga`;
        } else {
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Konfirmasi Presensi Keluarga';
        }
    }
    
    // Handle card click
    familyCards.forEach(card => {
        if (!card.hasAttribute('data-attended')) {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on checkbox directly
                if (e.target.type === 'checkbox') return;
                
                const checkbox = card.querySelector('.member-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    toggleCardSelection(card, checkbox.checked);
                    updateSelectionCounter();
                }
            });
        }
    });
    
    // Handle checkbox change
    document.querySelectorAll('.member-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.family-member-card');
            toggleCardSelection(card, this.checked);
            updateSelectionCounter();
        });
    });
    
    // Toggle card visual selection
    function toggleCardSelection(card, isSelected) {
        if (isSelected) {
            card.classList.add('selected');
        } else {
            card.classList.remove('selected');
        }
    }
    
    // Select all available members
    selectAllBtn.addEventListener('click', function() {
        document.querySelectorAll('.member-checkbox').forEach(checkbox => {
            checkbox.checked = true;
            const card = checkbox.closest('.family-member-card');
            toggleCardSelection(card, true);
        });
        updateSelectionCounter();
    });
    
    // Clear all selections
    clearAllBtn.addEventListener('click', function() {
        document.querySelectorAll('.member-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            const card = checkbox.closest('.family-member-card');
            toggleCardSelection(card, false);
        });
        updateSelectionCounter();
    });
    
    // Form submission with loading state
    familyForm.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Menyimpan...';
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch (e.key) {
                case 'a':
                    e.preventDefault();
                    selectAllBtn.click();
                    break;
                case 'd':
                    e.preventDefault();
                    clearAllBtn.click();
                    break;
            }
        }
        
        if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            familyForm.submit();
        }
    });
    
    // Initialize counter
    updateSelectionCounter();
    
    // Auto-submit if no available family members
    const availableMembers = document.querySelectorAll('.member-checkbox').length;
    if (availableMembers === 0 && {{ $familyMembers->count() }} > 0) {
        // All family members already attended, show message and auto-redirect
        setTimeout(() => {
            window.location.href = "{{ route('dashboard') }}";
        }, 3000);
        
        submitBtn.innerHTML = '<i class="fas fa-info-circle"></i> Semua keluarga sudah hadir - Redirect otomatis...';
        submitBtn.disabled = true;
    }
});

// Add some animation effects
document.addEventListener('DOMContentLoaded', function() {
    // Animate cards on load
    const cards = document.querySelectorAll('.family-member-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

<style>
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.1em;
    }
</style>
@endsection