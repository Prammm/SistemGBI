@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="login-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card login-card shadow-lg border-0 rounded-lg">
                    <div class="card-header text-white text-center py-4">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="logo-container me-3">
                                <img src="{{ asset('images/logo/logo.png') }}" alt="Logo GBI">
                            </div>
                            <div class="header-text">
                                <h3 class="font-weight-light mb-1">Sistem Informasi</h3>
                                <h4 class="font-weight-bold">GBI Situbondo</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-floating mb-3">
                                <input id="email" type="email" class="form-control custom-input @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                <label for="email">Email</label>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-floating mb-3">
                                <input id="password" type="password" class="form-control custom-input @error('password') is-invalid @enderror" 
                                    name="password" required autocomplete="current-password">
                                <label for="password">Password</label>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    <i class="fas fa-user-check me-1"></i>Ingat Saya
                                </label>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-4 mb-3">
                                <button type="submit" class="btn btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <div class="small footer-text">
                            <i class="fas fa-phone me-1"></i>
                            Untuk mendapatkan akun atau reset password,<br>
                            silahkan hubungi <strong>Pengurus Gereja GBI Situbondo</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Background gradient */
    .login-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #000000 100%);
        display: flex;
        align-items: center;
        padding: 2rem 0;
    }
    
    /* Main card styling */
    .login-card {
        border: none;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
        border-radius: 15px;
        overflow: hidden;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }
    
    /* Header styling with dark theme */
    .card-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        border-bottom: none;
        padding: 2rem 1.5rem;
    }
    
    /* Logo container */
    .logo-container {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px;
        flex-shrink: 0;
    }
    
    .logo-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: brightness(1.2) drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    
    /* Logo placeholder fallback */
    .logo-placeholder {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed rgba(255, 255, 255, 0.4);
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .logo-placeholder:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.6);
    }
    
    .logo-placeholder i {
        font-size: 24px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    /* Header text */
    .header-text {
        flex: 1;
        text-align: left;
    }
    
    .header-text h3, .header-text h4 {
        color: white;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        line-height: 1.2;
    }
    
    .header-text h3 {
        font-size: 1.1rem;
        font-weight: 300;
        margin-bottom: 2px;
    }
    
    .header-text h4 {
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .login-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 300;
        margin: 0;
        font-size: 1.1rem;
    }
    
    /* Card body */
    .card-body {
        padding: 2.5rem 2rem;
        background: white;
    }
    
    /* Custom input styling */
    .custom-input {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1rem 0.75rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .custom-input:focus {
        border-color: #495057;
        box-shadow: 0 0 0 0.2rem rgba(73, 80, 87, 0.25);
        background: white;
    }
    
    .form-floating > label {
        color: #6c757d;
        font-weight: 500;
    }
    
    /* Checkbox styling */
    .form-check-input:checked {
        background-color: #495057;
        border-color: #495057;
    }
    
    .form-check-label {
        color: #495057;
        font-weight: 500;
    }
    
    /* Login button */
    .btn-login {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 10px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(73, 80, 87, 0.3);
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(73, 80, 87, 0.4);
        background: linear-gradient(135deg, #343a40 0%, #495057 100%);
        color: white;
    }
    
    /* Help text */
    .help-text {
        color: #6c757d;
        font-size: 0.875rem;
        line-height: 1.4;
    }
    
    /* Footer */
    .card-footer {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-top: 1px solid #dee2e6;
        padding: 1.5rem;
    }
    
    .footer-text {
        color: #495057;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    .footer-text strong {
        color: #343a40;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .login-wrapper {
            padding: 1rem;
        }
        
        .header-text h3 {
            font-size: 1rem;
        }
        
        .header-text h4 {
            font-size: 1.2rem;
        }
        
        .logo-container, .logo-placeholder {
            width: 50px;
            height: 50px;
        }
        
        .logo-placeholder i {
            font-size: 20px;
        }
        
        .card-header {
            padding: 1.5rem 1rem;
        }
        
        .d-flex.align-items-center.justify-content-center.mb-3 {
            gap: 0.75rem;
        }
    }
    
    /* Animation for card entrance */
    .login-card {
        animation: slideUp 0.6s ease-out;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection