<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Sistem Informasi Keanggotaan GBI Situbondo" />
    <meta name="author" content="Alfons Pramudita" />
    <title>{{ config('app.name', 'GBI Situbondo') }} - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    @yield('styles')
    
    <style>
        /* Navbar logo styling */
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 1.2rem;
            color: #ffffff !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: #e9ecef !important;
            text-decoration: none;
        }
        
        .navbar-logo {
            width: 32px;
            height: 32px;
            margin-right: 10px;
            object-fit: contain;
            filter: brightness(1.1) drop-shadow(0 1px 3px rgba(0,0,0,0.3));
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover .navbar-logo {
            transform: scale(1.05);
            filter: brightness(1.2) drop-shadow(0 2px 5px rgba(0,0,0,0.4));
        }
        
        .navbar-text {
            margin: 0;
            line-height: 1.2;
            letter-spacing: 0.3px;
        }
        
        /* Responsive navbar logo */
        @media (max-width: 768px) {
            .navbar-logo {
                width: 28px;
                height: 28px;
                margin-right: 8px;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-logo {
                width: 24px;
                height: 24px;
                margin-right: 6px;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand with Logo -->
        <a class="navbar-brand ps-3" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo/logo.png') }}" alt="Logo GBI" class="navbar-logo">
            <span class="navbar-text">GBI Situbondo</span>
        </a>
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                    <span class="d-none d-md-inline ms-1">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="fas fa-user-circle me-2"></i>Profil Saya
                        </a>
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link" href="{{ route('notifikasi.index') }}" role="button">
                    <i class="fas fa-bell fa-fw"></i>
                    <!-- If there are unread notifications, add a badge -->
                    @php
                        $unreadCount = Auth::user()->id_anggota 
                            ? \App\Models\JadwalPelayanan::where('id_anggota', Auth::user()->id_anggota)
                                ->where('status_konfirmasi', 'belum')
                                ->count() 
                            : 0;
                    @endphp
                    @if($unreadCount > 0)
                        <span class="badge bg-danger">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item dropdown">
                <!-- User dropdown remains the same -->
            </li>
        </ul>
    </nav>
    
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading"></div>
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        
                        @if(Auth::check() && Auth::user()->id_role <= 3)
                        <div class="sb-sidenav-menu-heading"></div>
                        @if(Route::has('anggota.index'))
                        <a class="nav-link {{ request()->routeIs('anggota.*') ? 'active' : '' }}" href="{{ route('anggota.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Anggota Jemaat
                        </a>
                        @endif
                        
                        @if(Route::has('keluarga.index'))
                        <a class="nav-link {{ request()->routeIs('keluarga.*') ? 'active' : '' }}" href="{{ route('keluarga.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                            Keluarga
                        </a>
                        @endif
                        @endif
                        
                        <div class="sb-sidenav-menu-heading"></div>
                        @if(Route::has('kegiatan.index'))
                        <a class="nav-link {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" href="{{ route('kegiatan.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar"></i></div>
                            Kegiatan
                        </a>
                        @endif

                        @if(Route::has('pelaksanaan.index'))
                        <a class="nav-link {{ request()->routeIs('pelaksanaan.*') ? 'active' : '' }}" href="{{ route('pelaksanaan.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-plus"></i></div>
                            Jadwal Ibadah
                        </a>
                        @endif
                        
                        @if(Route::has('komsel.index'))
                        <a class="nav-link {{ request()->routeIs('komsel.*') ? 'active' : '' }}" href="{{ route('komsel.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Kelompok Sel
                        </a>
                        @endif
                        
                        @if(Route::has('pelayanan.index'))
                        <a class="nav-link {{ request()->routeIs('pelayanan.*') ? 'active' : '' }}" href="{{ route('pelayanan.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-hand-holding-heart"></i></div>
                            Pelayanan
                        </a>
                        @endif
                        
                        @if(Auth::check() && Auth::user()->id_role <= 3)
                        <div class="sb-sidenav-menu-heading"></div>
                        @if(Route::has('kehadiran.index'))
                        <a class="nav-link {{ request()->routeIs('kehadiran.*') ? 'active' : '' }}" href="{{ route('kehadiran.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-clipboard-check"></i></div>
                            Presensi
                        </a>
                        @endif
                        
                        @if(Route::has('laporan.index'))
                        <a class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Laporan
                        </a>
                        @endif
                        @endif
                        
                        @if(Auth::check() && Auth::user()->id_role == 1)
                        <div class="sb-sidenav-menu-heading"></div>
                        @if(Route::has('users.index'))
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-cog"></i></div>
                            Manajemen Pengguna
                        </a>
                        @endif

                        @if(Route::has('roles.index'))
                        <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-tag"></i></div>
                            Role & Permission
                        </a>
                        @endif
                        
                        @endif
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                @yield('content')
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; GBI Situbondo 2025</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    @yield('scripts')
</body>
</html>