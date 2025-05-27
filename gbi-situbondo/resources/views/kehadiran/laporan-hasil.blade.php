@extends('layouts.app')

@section('title', 'Hasil Laporan Kehadiran')

@section('styles')
<style>
    .table-striped-columns tbody td:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .attendance-cell {
        text-align: center;
        width: 40px;
    }
    
    .cell-present {
        background-color: #d1e7dd !important;
    }
    
    .cell-absent {
        background-color: #f8d7da !important;
    }
    
    .rotate-text {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
        height: 120px;
        padding: 5px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Hasil Laporan Kehadiran</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kehadiran.index') }}">Presensi Kehadiran</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kehadiran.laporan') }}">Laporan Kehadiran</a></li>
        <li class="breadcrumb-item active">Hasil Laporan</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Laporan Kehadiran {{ $kegiatan->nama_kegiatan }}
            <div class="float-end">
                <a href="#" class="btn btn-success btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak
                </a>
                <a href="{{ route('kehadiran.laporan') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="150">Kegiatan</th>
                            <td>{{ $kegiatan->nama_kegiatan }}</td>
                        </tr>
                        <tr>
                            <th>Periode</th>
                            <td>{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Jumlah Pelaksanaan</th>
                            <td>{{ count($pelaksanaan) }} kali</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped-columns">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Nama</th>
                            <th colspan="{{ count($pelaksanaan) }}" class="text-center">Tanggal Kegiatan</th>
                            <th rowspan="2" class="align-middle text-center">Total Hadir</th>
                            <th rowspan="2" class="align-middle text-center">Persentase</th>
                        </tr>
                        <tr>
                            @foreach($pelaksanaan as $p)
                                <th class="rotate-text">{{ \Carbon\Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anggota as $index => $a)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $a->nama }}</td>
                                @php
                                    $totalPresent = 0;
                                @endphp
                                
                                @foreach($pelaksanaan as $p)
                                    @php
                                        $isPresent = isset($kehadiran[$p->id_pelaksanaan]) && in_array($a->id_anggota, $kehadiran[$p->id_pelaksanaan]);
                                        if($isPresent) $totalPresent++;
                                    @endphp
                                    <td class="attendance-cell {{ $isPresent ? 'cell-present' : 'cell-absent' }}">
                                        {!! $isPresent ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}
                                    </td>
                                @endforeach
                                
                                <td class="text-center">{{ $totalPresent }}</td>
                                <td class="text-center">
                                    @if(count($pelaksanaan) > 0)
                                        {{ round(($totalPresent / count($pelaksanaan)) * 100) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection