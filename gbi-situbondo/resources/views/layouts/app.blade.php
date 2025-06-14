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
    <script src="https://unpkg.com/@zxing/library@latest"></script>
    @yield('styles')
    
    <style>
        /* Top Navbar styling */
        .top-navbar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
            border-bottom: 2px solid #3498db;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
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
            color: #3498db !important;
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
        
        /* Second Navbar styling */
        .second-navbar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            min-height: 50px;
        }
        
        .second-navbar .navbar-nav {
            width: 100%;
        }
        
        .second-navbar .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 12px 20px !important;
            border-radius: 6px;
            margin: 2px 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .second-navbar .nav-link:hover {
            background: rgba(255,255,255,0.15) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .second-navbar .nav-link.active {
            background: rgba(255,255,255,0.25) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        
        .second-navbar .nav-link i {
            margin-right: 12px;
            font-size: 0.9rem;
            width: 18px;
            text-align: center;
        }
        
        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .notification-link {
            position: relative;
            color: rgba(255,255,255,0.9) !important;
            padding: 8px 12px !important;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .notification-link:hover {
            background: rgba(255,255,255,0.15);
            color: #ffffff !important;
        }
        
        /* User dropdown styling */
        .user-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .user-dropdown .dropdown-item {
            padding: 10px 20px;
            transition: all 0.2s ease;
        }
        
        .user-dropdown .dropdown-item:hover {
            background: #f8f9fa;
            transform: translateX(4px);
        }
        
        /* Mobile responsiveness */
        @media (max-width: 991.98px) {
            .navbar-logo {
                width: 28px;
                height: 28px;
                margin-right: 8px;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
            
            .second-navbar {
                padding: 0;
            }
            
            .second-navbar .navbar-nav {
                max-height: 70vh;
                overflow-y: auto;
                padding: 10px 0;
            }
            
            .second-navbar .nav-link {
                padding: 10px 20px !important;
                margin: 1px 10px;
                border-radius: 4px;
            }
            
            .menu-heading {
                padding: 6px 20px 2px 20px;
                margin: 6px 0 2px 0;
                font-size: 0.7rem;
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
            
            .second-navbar .nav-link {
                padding: 8px 15px !important;
                margin: 1px 5px;
                font-size: 0.9rem;
            }
            
            .second-navbar .nav-link i {
                margin-right: 8px;
                font-size: 0.8rem;
            }
            
            .user-dropdown {
                position: static !important;
            }
            
            .notification-link {
                padding: 6px 10px !important;
            }
            
            .notification-badge {
                width: 16px;
                height: 16px;
                font-size: 0.6rem;
                top: 6px;
                right: 6px;
            }
        }
        
        /* Custom scrollbar for mobile menu */
        @media (max-width: 991.98px) {
            .second-navbar .navbar-nav::-webkit-scrollbar {
                width: 4px;
            }
            
            .second-navbar .navbar-nav::-webkit-scrollbar-track {
                background: rgba(255,255,255,0.1);
            }
            
            .second-navbar .navbar-nav::-webkit-scrollbar-thumb {
                background: rgba(255,255,255,0.3);
                border-radius: 2px;
            }
            
            .second-navbar .navbar-nav::-webkit-scrollbar-thumb:hover {
                background: rgba(255,255,255,0.5);
            }
        }
        
        /* Animation for mobile toggle */
        .navbar-toggler {
            border: none;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .navbar-toggler:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .navbar-toggler-icon {
            filter: brightness(0) invert(1);
        }
        
        /* Main content padding adjustment */
        .main-content {
            min-height: calc(100vh - 160px);
        }
        
        /* Footer styling */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: rgba(255,255,255,0.8);
            border-top: 2px solid #3498db;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo/logo.png') }}" alt="Logo GBI" class="navbar-logo">
                <span class="navbar-text" style="color: white;">GBI Situbondo</span>
            </a>
            
            <!-- Right side items -->
            <div class="d-flex align-items-center">
                <!-- Notifications -->
                <a class="notification-link me-3" href="{{ route('notifikasi.index') }}">
                    <i class="fas fa-bell"></i>
                    @php
                        $unreadCount = Auth::user()->id_anggota 
                            ? \App\Models\JadwalPelayanan::where('id_anggota', Auth::user()->id_anggota)
                                ->where('status_konfirmasi', 'belum')
                                ->count() 
                            : 0;
                    @endphp
                    @if($unreadCount > 0)
                        <span class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </a>
                
                <!-- User Dropdown -->
                <div class="dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user fa-fw"></i>
                        <span class="d-none d-md-inline ms-1">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
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
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Second Navigation Navbar -->
    <nav class="navbar navbar-expand-lg second-navbar">
        <div class="container-fluid">
            <!-- Mobile toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#secondNavbar" aria-controls="secondNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation items -->
            <div class="collapse navbar-collapse" id="secondNavbar">
                <ul class="navbar-nav w-100">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    
                    @if(Auth::check() && Auth::user()->id_role <= 3)
                    
                    @if(Route::has('anggota.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('anggota.*') ? 'active' : '' }}" href="{{ route('anggota.index') }}">
                            <i class="fas fa-users"></i>
                            Anggota Jemaat
                        </a>
                    </li>
                    @endif
                    
                    @if(Route::has('keluarga.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('keluarga.*') ? 'active' : '' }}" href="{{ route('keluarga.index') }}">
                            <i class="fas fa-home"></i>
                            Keluarga
                        </a>
                    </li>
                    @endif
                    
                    @if(Route::has('kegiatan.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" href="{{ route('kegiatan.index') }}">
                            <i class="fas fa-calendar"></i>
                            Kegiatan
                        </a>
                    </li>
                    @endif
                    @endif

                    @if(Route::has('pelaksanaan.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pelaksanaan.*') ? 'active' : '' }}" href="{{ route('pelaksanaan.index') }}">
                            <i class="fas fa-calendar-plus"></i>
                            Jadwal Kegiatan
                        </a>
                    </li>
                    @endif
                    
                    @if(Route::has('komsel.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('komsel.*') ? 'active' : '' }}" href="{{ route('komsel.index') }}">
                            <i class="fas fa-users"></i>
                            Kelompok Sel
                        </a>
                    </li>
                    @endif
                    
                    @if(Route::has('pelayanan.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pelayanan.*') ? 'active' : '' }}" href="{{ route('pelayanan.index') }}">
                            <i class="fas fa-hand-holding-heart"></i>
                            Pelayanan
                        </a>
                    </li>
                    @endif
                    
                    @if(Route::has('kehadiran.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kehadiran.*') ? 'active' : '' }}" href="{{ route('kehadiran.index') }}">
                            <i class="fas fa-clipboard-check"></i>
                            Presensi
                        </a>
                    </li>
                    @endif
                    
                    @if(Route::has('laporan.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">
                            <i class="fas fa-chart-line"></i>
                            Laporan
                        </a>
                    </li>
                    @endif
                    
                    @if(Auth::check() && Auth::user()->id_role == 1)
                    
                    @if(Route::has('users.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="fas fa-user-cog"></i>
                            Manajemen Pengguna
                        </a>
                    </li>
                    @endif

                    @if(Route::has('roles.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                            <i class="fas fa-user-tag"></i>
                            Role & Permission
                        </a>
                    </li>
                    @endif
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content flex-grow-1">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="footer py-3 mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">Copyright &copy; GBI Situbondo 2025</div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    
    <script>
        // Auto-close mobile menu when clicking on a link
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.second-navbar .nav-link');
            const navbarCollapse = document.getElementById('secondNavbar');
            
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                            toggle: false
                        });
                        bsCollapse.hide();
                    }
                });
            });
            
            // Smooth scroll for active nav items
            const activeNavItem = document.querySelector('.second-navbar .nav-link.active');
            if (activeNavItem && window.innerWidth < 992) {
                setTimeout(() => {
                    activeNavItem.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 300);
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>