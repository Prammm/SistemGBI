@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Total Anggota</div>
                        <div>{{ $data['total_anggota'] }}</div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('anggota.index') }}">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Total Komsel</div>
                        <div>{{ $data['total_komsel'] }}</div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('komsel.index') }}">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar me-1"></i>
                    Kegiatan Mendatang
                </div>
                <div class="card-body">
                    @if(count($data['kegiatan_mendatang']) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kegiatan</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['kegiatan_mendatang'] as $kegiatan)
                                        <tr>
                                            <td>{{ $kegiatan->kegiatan->nama_kegiatan }}</td>
                                            <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal_kegiatan)->format('d M Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($kegiatan->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($kegiatan->jam_selesai)->format('H:i') }}</td>
                                            <td>{{ $kegiatan->lokasi }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Tidak ada kegiatan mendatang</p>
                    @endif
                </div>
            </div>
        </div>
        
        @if(isset($data['anggota_baru']) && count($data['anggota_baru']) > 0)
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Anggota Baru
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tanggal Bergabung</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['anggota_baru'] as $anggota)
                                    <tr>
                                        <td>{{ $anggota->nama }}</td>
                                        <td>{{ \Carbon\Carbon::parse($anggota->created_at)->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if(isset($data['jadwal_pelayanan']) && count($data['jadwal_pelayanan']) > 0)
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-hand-holding-heart me-1"></i>
                    Jadwal Pelayanan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kegiatan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['jadwal_pelayanan'] as $jadwal)
                                    <tr>
                                        <td>{{ $jadwal->anggota->nama }}</td>
                                        <td>{{ $jadwal->kegiatan->nama_kegiatan }}</td>
                                        <td>{{ \Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->format('d M Y') }}</td>
                                        <td>
                                            @if($jadwal->status_konfirmasi == 'belum')
                                                <span class="badge bg-warning">Belum Konfirmasi</span>
                                            @elseif($jadwal->status_konfirmasi == 'terima')
                                                <span class="badge bg-success">Diterima</span>
                                            @else
                                                <span class="badge bg-danger">Ditolak</span>
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
        @endif
        
        @if(isset($data['jadwal_pelayanan_saya']) && count($data['jadwal_pelayanan_saya']) > 0)
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-hand-holding-heart me-1"></i>
                    Jadwal Pelayanan Saya
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Kegiatan</th>
                                    <th>Tanggal</th>
                                    <th>Posisi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['jadwal_pelayanan_saya'] as $jadwal)
                                    <tr>
                                        <td>{{ $jadwal->kegiatan->nama_kegiatan }}</td>
                                        <td>{{ \Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->format('d M Y') }}</td>
                                        <td>{{ $jadwal->posisi }}</td>
                                        <td>
                                            @if($jadwal->status_konfirmasi == 'belum')
                                                <span class="badge bg-warning">Belum Konfirmasi</span>
                                            @elseif($jadwal->status_konfirmasi == 'terima')
                                                <span class="badge bg-success">Diterima</span>
                                            @else
                                                <span class="badge bg-danger">Ditolak</span>
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
        @endif
        
        @if(isset($data['komsel_saya']) && count($data['komsel_saya']) > 0)
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Komsel Saya
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Komsel</th>
                                    <th>Hari</th>
                                    <th>Waktu</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['komsel_saya'] as $komsel)
                                    <tr>
                                        <td>{{ $komsel->nama_komsel }}</td>
                                        <td>{{ $komsel->hari }}</td>
                                        <td>{{ \Carbon\Carbon::parse($komsel->jam_mulai)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($komsel->jam_selesai)->format('H:i') }}</td>
                                        <td>{{ $komsel->lokasi }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection