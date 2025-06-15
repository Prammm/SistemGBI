@extends('layouts.app')

@section('title', 'Laporan Komsel')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<style>
    .filter-card {
        margin-bottom: 20px;
    }
    .stats-card {
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        color: white;
        text-align: center;
    }
    .stats-card.primary {
        background-color: #4e73df;
    }
    .stats-card.success {
        background-color: #1cc88a;
    }
    .stats-card.warning {
        background-color: #f6c23e;
    }
    .stats-card.info {
        background-color: #36b9cc;
    }
    .stats-card-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }
    .stats-card-title {
        font-size: 0.8rem;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .stats-card-value {
        font-size: 1.8rem;
        font-weight: bold;
    }
    .chart-container {
        position: relative;
        margin: auto;
        height: 300px;
        margin-bottom: 30px;
    }
    .table-container {
        margin-top: 20px;
    }
    .komsel-card {
        margin-bottom: 20px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .komsel-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
    .komsel-header {
        padding: 15px;
        background-color: #f6c23e;
        color: white;
    }
    .komsel-title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .komsel-subtitle {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    .komsel-body {
        padding: 15px;
        background-color: white;
    }
    .komsel-stat {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .komsel-stat-label {
        font-weight: bold;
        color: #4e73df;
    }
    
    /* Custom DataTable Styling for Komsel Grid */
    .komsel-search-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .komsel-search-container .form-control {
        border-radius: 25px;
        border: 1px solid #ced4da;
        padding: 10px 20px;
    }
    
    .komsel-search-container .form-control:focus {
        border-color: #f6c23e;
        box-shadow: 0 0 0 0.2rem rgba(246, 194, 62, 0.25);
    }
    
    .komsel-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }
    
    .komsel-pagination {
        margin-top: 30px;
        text-align: center;
    }
    
    .komsel-pagination .btn {
        margin: 0 5px;
        border-radius: 20px;
    }
    
    .komsel-info {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .no-komsel-message {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .no-komsel-message i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
        color: #f6c23e;
    }
    
    .export-buttons {
        margin-bottom: 20px;
    }
    
    .export-buttons .btn {
        margin-right: 10px;
        margin-bottom: 5px;
    }
    
    @media (max-width: 768px) {
        .komsel-grid {
            grid-template-columns: 1fr;
        }
        
        .komsel-card {
            margin-bottom: 15px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Komsel</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Komsel</li>
    </ol>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card primary">
                <div class="stats-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-card-title">Total Komsel</div>
                <div class="stats-card-value">{{ number_format($totalKomsel) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card success">
                <div class="stats-card-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stats-card-title">Total Anggota Komsel</div>
                <div class="stats-card-value">{{ number_format($totalAnggotaKomsel) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card warning">
                <div class="stats-card-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="stats-card-title">Rata-rata Anggota</div>
                <div class="stats-card-value">{{ number_format($rataRataAnggota, 1) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card info">
                <div class="stats-card-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stats-card-title">Kegiatan Bulan Ini</div>
                <div class="stats-card-value">{{ number_format($kegiatanKomsel->count()) }}</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Komsel dengan Anggota Terbanyak
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="komselTerbanyakChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Kehadiran Komsel per Minggu
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="kehadiranKomselChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Komsel List Section -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Daftar Komsel
                </div>
                <div class="card-body">
                    <!-- Export Buttons -->
                    <div class="export-buttons">
                        <a href="{{ route('laporan.export', ['jenis' => 'komsel', 'format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="{{ route('laporan.export', ['jenis' => 'komsel', 'format' => 'excel']) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                    
                    <!-- Search Container -->
                    <div class="komsel-search-container">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" id="komselSearch" class="form-control" placeholder="Cari nama komsel, pemimpin, atau lokasi...">
                            </div>
                            <div class="col-md-4">
                                <select id="komselPerPage" class="form-select">
                                    <option value="6">6 per halaman</option>
                                    <option value="12" selected>12 per halaman</option>
                                    <option value="18">18 per halaman</option>
                                    <option value="24">24 per halaman</option>
                                    <option value="999">Semua</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Komsel Info -->
                    <div class="komsel-info">
                        <span id="komselInfo">Menampilkan <strong id="komselStart">1</strong> sampai <strong id="komselEnd">12</strong> dari <strong id="komselTotal">{{ $komsel->count() }}</strong> komsel</span>
                    </div>
                    
                    <!-- Komsel Grid -->
                    <div id="komselContainer">
                        @if($komsel->count() > 0)
                            <div class="komsel-grid" id="komselGrid">
                                @foreach($komsel as $k)
                                    <div class="komsel-card" 
                                         data-search="{{ strtolower($k->nama_komsel . ' ' . ($k->pemimpin->nama ?? '') . ' ' . ($k->lokasi ?? '') . ' ' . ($k->hari ?? '')) }}">
                                        <div class="komsel-header">
                                            <div class="komsel-title">{{ $k->nama_komsel }}</div>
                                            <div class="komsel-subtitle">
                                                <i class="fas fa-user-tie me-1"></i>
                                                Pemimpin: {{ $k->pemimpin->nama ?? 'Belum ditentukan' }}
                                            </div>
                                        </div>
                                        <div class="komsel-body">
                                            <div class="komsel-stat">
                                                <div class="komsel-stat-label">
                                                    <i class="fas fa-users me-1"></i>Jumlah Anggota:
                                                </div>
                                                <div><strong>{{ $k->anggota->count() }}</strong> orang</div>
                                            </div>
                                            <div class="komsel-stat">
                                                <div class="komsel-stat-label">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Lokasi:
                                                </div>
                                                <div>{{ $k->lokasi ?? 'Belum ditentukan' }}</div>
                                            </div>
                                            <div class="komsel-stat">
                                                <div class="komsel-stat-label">
                                                    <i class="fas fa-calendar me-1"></i>Jadwal:
                                                </div>
                                                <div>
                                                    {{ $k->hari ?? 'Belum dijadwalkan' }}
                                                    @if($k->jam_mulai && $k->jam_selesai)
                                                        <br><small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($k->jam_mulai)->format('H:i') }} - 
                                                            {{ \Carbon\Carbon::parse($k->jam_selesai)->format('H:i') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                @if(Route::has('komsel.show'))
                                                    <a href="{{ route('komsel.show', $k->id_komsel) }}" class="btn btn-sm btn-warning w-100">
                                                        <i class="fas fa-eye me-1"></i>Detail Komsel
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary w-100" disabled>
                                                        <i class="fas fa-eye me-1"></i>Detail Komsel
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Pagination -->
                            <div class="komsel-pagination" id="komselPagination">
                                <!-- Pagination will be generated by JavaScript -->
                            </div>
                        @else
                            <div class="no-komsel-message">
                                <i class="fas fa-users"></i>
                                <h5>Tidak Ada Data Komsel</h5>
                                <p>Belum ada komsel yang terdaftar dalam sistem.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Komsel pagination and search functionality
        let allKomselCards = document.querySelectorAll('.komsel-card');
        let filteredCards = Array.from(allKomselCards);
        let currentPage = 1;
        let itemsPerPage = 12;
        
        const searchInput = document.getElementById('komselSearch');
        const perPageSelect = document.getElementById('komselPerPage');
        const komselGrid = document.getElementById('komselGrid');
        const paginationContainer = document.getElementById('komselPagination');
        
        // Search functionality
        function filterKomsel() {
            const searchTerm = searchInput.value.toLowerCase();
            filteredCards = Array.from(allKomselCards).filter(card => {
                const searchData = card.getAttribute('data-search');
                return searchData.includes(searchTerm);
            });
            
            currentPage = 1;
            displayKomsel();
        }
        
        // Display komsel based on current page and filters
        function displayKomsel() {
            // Hide all cards
            allKomselCards.forEach(card => card.style.display = 'none');
            
            if (filteredCards.length === 0) {
                komselGrid.innerHTML = `
                    <div class="no-komsel-message" style="grid-column: 1 / -1;">
                        <i class="fas fa-search"></i>
                        <h5>Tidak Ada Hasil</h5>
                        <p>Tidak ditemukan komsel yang sesuai dengan pencarian "${searchInput.value}".</p>
                    </div>
                `;
                updateInfo(0, 0, 0);
                paginationContainer.innerHTML = '';
                return;
            }
            
            // Reset grid
            komselGrid.innerHTML = '';
            allKomselCards.forEach(card => komselGrid.appendChild(card));
            
            // Calculate pagination
            const totalItems = filteredCards.length;
            const totalPages = itemsPerPage === 999 ? 1 : Math.ceil(totalItems / itemsPerPage);
            const startIndex = itemsPerPage === 999 ? 0 : (currentPage - 1) * itemsPerPage;
            const endIndex = itemsPerPage === 999 ? totalItems : Math.min(startIndex + itemsPerPage, totalItems);
            
            // Show filtered cards for current page
            filteredCards.forEach((card, index) => {
                if (itemsPerPage === 999 || (index >= startIndex && index < endIndex)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            updateInfo(startIndex + 1, endIndex, totalItems);
            generatePagination(currentPage, totalPages);
        }
        
        // Update info text
        function updateInfo(start, end, total) {
            document.getElementById('komselStart').textContent = start;
            document.getElementById('komselEnd').textContent = end;
            document.getElementById('komselTotal').textContent = total;
        }
        
        // Generate pagination
        function generatePagination(current, total) {
            if (total <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }
            
            let paginationHTML = '';
            
            // Previous button
            if (current > 1) {
                paginationHTML += `<button class="btn btn-outline-warning btn-sm" onclick="goToPage(${current - 1})">
                    <i class="fas fa-chevron-left"></i> Sebelumnya
                </button>`;
            }
            
            // Page numbers
            let startPage = Math.max(1, current - 2);
            let endPage = Math.min(total, current + 2);
            
            if (startPage > 1) {
                paginationHTML += `<button class="btn btn-outline-secondary btn-sm" onclick="goToPage(1)">1</button>`;
                if (startPage > 2) {
                    paginationHTML += `<span class="btn btn-sm disabled">...</span>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === current ? 'btn-warning' : 'btn-outline-warning';
                paginationHTML += `<button class="btn ${activeClass} btn-sm" onclick="goToPage(${i})">${i}</button>`;
            }
            
            if (endPage < total) {
                if (endPage < total - 1) {
                    paginationHTML += `<span class="btn btn-sm disabled">...</span>`;
                }
                paginationHTML += `<button class="btn btn-outline-secondary btn-sm" onclick="goToPage(${total})">${total}</button>`;
            }
            
            // Next button
            if (current < total) {
                paginationHTML += `<button class="btn btn-outline-warning btn-sm" onclick="goToPage(${current + 1})">
                    Selanjutnya <i class="fas fa-chevron-right"></i>
                </button>`;
            }
            
            paginationContainer.innerHTML = paginationHTML;
        }
        
        // Go to page function (global)
        window.goToPage = function(page) {
            currentPage = page;
            displayKomsel();
        }
        
        // Event listeners
        searchInput.addEventListener('input', filterKomsel);
        
        perPageSelect.addEventListener('change', function() {
            itemsPerPage = parseInt(this.value);
            currentPage = 1;
            displayKomsel();
        });
        
        // Initial display
        displayKomsel();
        
        // Komsel dengan Anggota Terbanyak Chart
        const komselTerbanyakCtx = document.getElementById('komselTerbanyakChart').getContext('2d');
        const komselTerbanyakData = @json($komselTerbanyak);
        const komselTerbanyakLabels = komselTerbanyakData.map(item => item.nama);
        const komselTerbanyakValues = komselTerbanyakData.map(item => item.jumlah);
        
        new Chart(komselTerbanyakCtx, {
            type: 'bar',
            data: {
                labels: komselTerbanyakLabels,
                datasets: [{
                    label: 'Jumlah Anggota',
                    data: komselTerbanyakValues,
                    backgroundColor: 'rgba(246, 194, 62, 0.8)',
                    borderColor: 'rgba(246, 194, 62, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Kehadiran Komsel per Minggu Chart
        const kehadiranKomselCtx = document.getElementById('kehadiranKomselChart').getContext('2d');
        const kehadiranKomselData = @json($kehadiranPerMinggu);
        const kehadiranKomselLabels = kehadiranKomselData.map(item => item.minggu);
        const kehadiranKomselValues = kehadiranKomselData.map(item => item.jumlah);
        
        new Chart(kehadiranKomselCtx, {
            type: 'line',
            data: {
                labels: kehadiranKomselLabels,
                datasets: [{
                    label: 'Jumlah Kehadiran',
                    data: kehadiranKomselValues,
                    backgroundColor: 'rgba(246, 194, 62, 0.2)',
                    borderColor: 'rgba(246, 194, 62, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endsection