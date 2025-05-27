@extends('layouts.app')
@section('title', 'Notifikasi')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Notifikasi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Notifikasi</li>
    </ol>
    
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bell me-1"></i>
                    Notifikasi dan Pengingat
                    @if(Auth::user()->id_role <= 2)
                    <div class="float-end">
                        <a href="{{ route('notifikasi.send-pelayanan') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane"></i> Kirim Pengingat Pelayanan
                        </a>
                        <a href="{{ route('notifikasi.send-komsel') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-paper-plane"></i> Kirim Pengingat Komsel
                        </a>
                        <a href="{{ route('notifikasi.send-ibadah') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-paper-plane"></i> Kirim Pengingat Ibadah
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if(count($notifications) > 0)
                        <div class="list-group">
                            @foreach($notifications as $notification)
                                <a href="{{ $notification['url'] }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">
                                            @if($notification['type'] == 'pelayanan')
                                                <i class="fas fa-hand-holding-heart text-primary me-2"></i>
                                            @elseif($notification['type'] == 'komsel')
                                                <i class="fas fa-users text-success me-2"></i>
                                            @elseif($notification['type'] == 'ibadah')
                                                <i class="fas fa-church text-info me-2"></i>
                                            @endif
                                            {{ $notification['title'] }}
                                        </h5>
                                        <small>{{ \Carbon\Carbon::parse($notification['date'])->format('d/m/Y') }}</small>
                                    </div>
                                    <p class="mb-1">{{ $notification['description'] }}</p>
                                    @if(isset($notification['status']))
                                        @if($notification['status'] == 'belum')
                                            <span class="badge bg-warning">Belum Konfirmasi</span>
                                        @elseif($notification['status'] == 'terima')
                                            <span class="badge bg-success">Diterima</span>
                                        @elseif($notification['status'] == 'tolak')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center">Tidak ada notifikasi saat ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection