@extends('layouts.app')

@section('title', 'Laporan')

@section('styles')
<style>
    .report-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
    .report-icon {
        font-size: 3rem;
        color: white;
    }
    .report-header {
        padding: 20px;
        color: white;
    }
    .report-body {
        padding: 20px;
        background-color: white;
    }
    .report-title {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .kehadiran {
        background-color: #4e73df;
    }
    .pelayanan {
        background-color: #1cc88a;
    }
    .komsel {
        background-color: #f6c23e;
    }
    .anggota {
        background-color: #e74a3b;
    }
    .dashboard {
        background-color: #36b9cc;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Laporan</li>
    </ol>

    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="report-card">
                <div class="report-header kehadiran">
                    <div class="row">
                        <div class="col-3">
                            <i class="fas fa-clipboard-check report-icon"></i>
                        </div>
                        <div class="col-9 text-right">
                            <div class="report-title">Laporan Kehadiran</div>
                        </div>
                    </div>
                </div>
                <div class="report-body">
                    <p>Melihat statistik kehadiran jemaat pada kegiatan gereja dalam periode tertentu.</p>
                    <a href="{{ route('laporan.kehadiran') }}" class="btn btn-primary btn-sm">Lihat Laporan</a>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="report-card">
                <div class="report-header pelayanan">
                    <div class="row">
                        <div class="col-3">
                            <i class="fas fa-hands-helping report-icon"></i>
                        </div>
                        <div class="col-9 text-right">
                            <div class="report-title">Laporan Pelayanan</div>
                        </div>
                    </div>
                </div>
                <div class="report-body">
                    <p>Melihat statistik pelayanan dan aktivitas pelayan dalam periode tertentu.</p>
                    <a href="{{ route('laporan.pelayanan') }}" class="btn btn-success btn-sm">Lihat Laporan</a>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="report-card">
                <div class="report-header komsel">
                    <div class="row">
                        <div class="col-3">
                            <i class="fas fa-users report-icon"></i>
                        </div>
                        <div class="col-9 text-right">
                            <div class="report-title">Laporan Komsel</div>
                        </div>
                    </div>
                </div>
                <div class="report-body">
                    <p>Melihat statistik kelompok sel, anggota, dan kegiatan komsel dalam periode tertentu.</p>
                    <a href="{{ route('laporan.komsel') }}" class="btn btn-warning btn-sm">Lihat Laporan</a>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="report-card">
                <div class="report-header anggota">
                    <div class="row">
                        <div class="col-3">
                            <i class="fas fa-user-friends report-icon"></i>
                        </div>
                        <div class="col-9 text-right">
                            <div class="report-title">Laporan Anggota</div>
                        </div>
                    </div>
                </div>
                <div class="report-body">
                    <p>Melihat statistik anggota jemaat, demografi, dan pertumbuhan jemaat.</p>
                    <a href="{{ route('laporan.anggota') }}" class="btn btn-danger btn-sm">Lihat Laporan</a>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="report-card">
                <div class="report-header dashboard">
                    <div class="row">
                        <div class="col-3">
                            <i class="fas fa-chart-line report-icon"></i>
                        </div>
                        <div class="col-9 text-right">
                            <div class="report-title">Dashboard Analitik</div>
                        </div>
                    </div>
                </div>
                <div class="report-body">
                    <p>Melihat ringkasan statistik dan analitik untuk semua aspek kegiatan gereja.</p>
                    <a href="{{ route('laporan.dashboard') }}" class="btn btn-info btn-sm">Lihat Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection