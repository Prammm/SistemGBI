@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="font-weight-light my-2">Sistem Informasi GBI Situbondo</h3>
                    <h5>Login</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-floating mb-3">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            <label for="email">Email</label>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-floating mb-3">
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                name="password" required autocomplete="current-password">
                            <label for="password">Password</label>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Ingat Saya
                            </label>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                            @if (Route::has('password.request'))
                                <a class="small" href="{{ route('password.request') }}">
                                    Lupa Password?
                                </a>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                Login
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small"><a href="{{ route('register') }}">Belum punya akun? Daftar di sini</a></div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
    .card {
        display: flex;
        flex-direction: column;
        min-height: 500px; /* Adjust this value based on your needs */
    }
    
    .card-body {
        flex: 1 0 auto;
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
    
    .card-footer {
        flex-shrink: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.125);
        margin-top: auto;
    }
</style>
@endsection