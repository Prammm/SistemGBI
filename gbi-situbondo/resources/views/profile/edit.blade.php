@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">
        <i class="fas fa-user-edit me-2"></i>Edit Profil
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profil Saya</a></li>
        <li class="breadcrumb-item active">Edit Profil</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="row">
        <!-- Account Information Form -->
        <div class="col-xl-6 col-lg-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user-cog me-2"></i>Informasi Akun
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" id="profile-form">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-1"></i>Nama Pengguna
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="border-top pt-3" id="password-section">
                            <h6 class="mb-3">
                                <i class="fas fa-lock me-1"></i>Ubah Password (Opsional)
                            </h6>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Diperlukan jika ingin mengubah password</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimal 8 karakter</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        @if($user->anggota)
        <!-- Anggota Information Form -->
        <div class="col-xl-6 col-lg-6">
            <div class="card mb-4 shadow-sm" id="anggota-section">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-address-card me-2"></i>Data Anggota Jemaat
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update.anggota') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                <i class="fas fa-id-badge me-1"></i>Nama Lengkap
                            </label>
                            <input type="text" 
                                   class="form-control @error('nama') is-invalid @enderror" 
                                   id="nama" 
                                   name="nama" 
                                   value="{{ old('nama', $user->anggota->nama) }}" 
                                   required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_lahir" class="form-label">
                                    <i class="fas fa-birthday-cake me-1"></i>Tanggal Lahir
                                </label>
                                <input type="date" 
                                       class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                                       id="tanggal_lahir" 
                                       name="tanggal_lahir" 
                                       value="{{ old('tanggal_lahir', $user->anggota->tanggal_lahir) }}" 
                                       required>
                                @error('tanggal_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="jenis_kelamin" class="form-label">
                                    <i class="fas fa-venus-mars me-1"></i>Jenis Kelamin
                                </label>
                                <select class="form-select @error('jenis_kelamin') is-invalid @enderror" 
                                        id="jenis_kelamin" 
                                        name="jenis_kelamin" 
                                        required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L" {{ old('jenis_kelamin', $user->anggota->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                        Laki-laki
                                    </option>
                                    <option value="P" {{ old('jenis_kelamin', $user->anggota->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                        Perempuan
                                    </option>
                                </select>
                                @error('jenis_kelamin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="no_telepon" class="form-label">
                                <i class="fas fa-phone me-1"></i>No. Telepon
                            </label>
                            <input type="text" 
                                   class="form-control @error('no_telepon') is-invalid @enderror" 
                                   id="no_telepon" 
                                   name="no_telepon" 
                                   value="{{ old('no_telepon', $user->anggota->no_telepon) }}" 
                                   placeholder="Contoh: 081234567890">
                            @error('no_telepon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="email_anggota" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Anggota
                            </label>
                            <input type="email" 
                                   class="form-control @error('email_anggota') is-invalid @enderror" 
                                   id="email_anggota" 
                                   name="email_anggota" 
                                   value="{{ old('email_anggota', $user->anggota->email) }}" 
                                   placeholder="Email khusus data anggota (opsional)">
                            @error('email_anggota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Bisa sama dengan email akun atau berbeda</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="alamat" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Alamat
                            </label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                      id="alamat" 
                                      name="alamat" 
                                      rows="3" 
                                      placeholder="Alamat lengkap">{{ old('alamat', $user->anggota->alamat) }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-save me-1"></i>Simpan Data Anggota
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    @if(!$user->anggota)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Informasi:</strong> Akun Anda belum terkait dengan data anggota jemaat. 
                Silahkan hubungi administrator untuk menghubungkan akun Anda dengan data anggota.
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
    border-bottom: none;
    font-weight: 600;
}

.form-label {
    font-weight: 500;
    color: #5a5c69;
}

.form-control:focus, 
.form-select:focus {
    border-color: #bac8f3;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.btn {
    font-weight: 500;
}

.border-top {
    border-color: #e3e6f0 !important;
}

.alert {
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #36b9cc;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus on password fields when current password is filled
    const currentPasswordField = document.getElementById('current_password');
    const passwordField = document.getElementById('password');
    
    currentPasswordField.addEventListener('input', function() {
        if (this.value.length > 0) {
            passwordField.focus();
        }
    });
    
    // Form validation for password confirmation
    const passwordConfirmField = document.getElementById('password_confirmation');
    
    passwordConfirmField.addEventListener('input', function() {
        const password = passwordField.value;
        const confirmPassword = this.value;
        
        if (confirmPassword.length > 0 && password !== confirmPassword) {
            this.setCustomValidity('Password tidak cocok');
        } else {
            this.setCustomValidity('');
        }
    });
    
    passwordField.addEventListener('input', function() {
        const confirmPassword = passwordConfirmField.value;
        
        if (confirmPassword.length > 0 && this.value !== confirmPassword) {
            passwordConfirmField.setCustomValidity('Password tidak cocok');
        } else {
            passwordConfirmField.setCustomValidity('');
        }
    });
});
</script>
@endsection