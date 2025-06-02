@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">
        <i class="fas fa-user-circle me-2"></i>Profil Saya
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Profil Saya</li>
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
    
    <div class="row">
        <!-- Profile Overview Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-id-card me-2"></i>Informasi Akun
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="profile-avatar mx-auto mb-3">
                            <i class="fas fa-user-circle text-muted" style="font-size: 6rem;"></i>
                        </div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-0">{{ $user->role ? $user->role->nama_role : 'Tidak ada role' }}</p>
                    </div>
                    <div class="row text-start">
                        <div class="col-12 mb-2">
                            <small class="text-muted">Email:</small><br>
                            <span>{{ $user->email }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Bergabung:</small><br>
                            <span>{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Update Terakhir:</small><br>
                            <span>{{ $user->updated_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-edit me-1"></i>Edit Profil
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Detailed Information -->
        <div class="col-xl-8 col-lg-7">
            @if($user->anggota)
            <!-- Anggota Information Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-user me-2"></i>Data Anggota Jemaat</span>
                    <a href="{{ route('profile.edit') }}#anggota-section" class="btn btn-light btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Nama Lengkap</label>
                            <div class="fw-bold">{{ $user->anggota->nama }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Jenis Kelamin</label>
                            <div class="fw-bold">
                                @if($user->anggota->jenis_kelamin == 'L')
                                    <i class="fas fa-mars text-primary me-1"></i>Laki-laki
                                @else
                                    <i class="fas fa-venus text-danger me-1"></i>Perempuan
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Tanggal Lahir</label>
                            <div class="fw-bold">
                                {{ \Carbon\Carbon::parse($user->anggota->tanggal_lahir)->format('d F Y') }}
                                <small class="text-muted">
                                    ({{ \Carbon\Carbon::parse($user->anggota->tanggal_lahir)->age }} tahun)
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">No. Telepon</label>
                            <div class="fw-bold">
                                @if($user->anggota->no_telepon)
                                    <i class="fas fa-phone me-1"></i>{{ $user->anggota->no_telepon }}
                                @else
                                    <span class="text-muted">Belum diisi</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small mb-1">Alamat</label>
                            <div class="fw-bold">
                                @if($user->anggota->alamat)
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $user->anggota->alamat }}
                                @else
                                    <span class="text-muted">Belum diisi</span>
                                @endif
                            </div>
                        </div>
                        @if($user->anggota->email && $user->anggota->email != $user->email)
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small mb-1">Email Anggota</label>
                            <div class="fw-bold">
                                <i class="fas fa-envelope me-1"></i>{{ $user->anggota->email }}
                            </div>
                        </div>
                        @endif
                        @if($user->anggota->keluarga)
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small mb-1">Keluarga</label>
                            <div class="fw-bold">
                                <i class="fas fa-home me-1"></i>{{ $user->anggota->keluarga->nama_keluarga }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Security Settings Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-shield-alt me-2"></i>Keamanan Akun
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Password</label>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">••••••••</span>
                                <a href="{{ route('profile.edit') }}#password-section" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-key me-1"></i>Ubah Password
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Login Terakhir</label>
                            <div class="fw-bold">
                                <i class="fas fa-clock me-1"></i>
                                {{ $user->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    
                    @if($user->role)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Role Anda:</strong> {{ $user->role->nama_role }}
                        @if($user->role->deskripsi)
                            <br><small>{{ $user->role->deskripsi }}</small>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-avatar {
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(145deg, #f8f9fa, #e9ecef);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card {
    border: none;
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-bottom: none;
    font-weight: 600;
}

.alert {
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #0dcaf0;
}
</style>
@endsection