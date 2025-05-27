@extends('layouts.app')

@section('title', 'Jadwal Kegiatan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Jadwal Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Jadwal Kegiatan</li>
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
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-1"></i>
            Jadwal Kegiatan Gereja
            <div class="float-end">
                <a href="{{ route('kegiatan.calendar') }}" class="btn btn-info btn-sm ">
                    <i class="fas fa-calendar-alt"></i> Tampilan Kalender
                </a>
                <a href="{{ route('pelaksanaan.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Jadwal
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kegiatan</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pelaksanaan as $p)
                            <tr>
                                <td>
                                    {{ $p->kegiatan->nama_kegiatan }}
                                    @if($p->is_recurring)
                                        <br><small class="text-primary">
                                            <i class="fas fa-repeat"></i> 
                                            Induk - {{ ucfirst($p->recurring_type) }}
                                        </small>
                                    @elseif($p->parent_id)
                                        <br><small class="text-muted">
                                            <i class="fas fa-link"></i> 
                                            Bagian dari jadwal berulang
                                        </small>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($p->jam_selesai)->format('H:i') }}
                                </td>
                                <td>{{ $p->lokasi ?: '-' }}</td>
                                <td>
                                    @if($p->is_recurring)
                                        <span class="badge bg-primary">
                                            <i class="fas fa-repeat"></i> Berulang
                                        </span>
                                        <br><small class="text-muted">
                                            Sampai {{ \Carbon\Carbon::parse($p->recurring_end_date)->format('d/m/Y') }}
                                        </small>
                                    @elseif($p->parent_id)
                                        <span class="badge bg-info">
                                            <i class="fas fa-link"></i> Terjadwal
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-calendar"></i> Sekali
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('pelaksanaan.show', $p->id_pelaksanaan) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $p->id_pelaksanaan]) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-clipboard-check"></i>
                                    </a>
                                    <a href="{{ route('pelaksanaan.edit', $p->id_pelaksanaan) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($p->is_recurring)
                                        <!-- Dropdown for recurring schedules -->
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form action="{{ route('pelaksanaan.destroy', $p->id_pelaksanaan) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini saja?')">
                                                            <i class="fas fa-calendar-minus"></i> Hapus Jadwal Ini Saja
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('pelaksanaan.destroy-series', $p->id_pelaksanaan) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus SELURUH seri jadwal berulang ini?')">
                                                            <i class="fas fa-trash-alt"></i> Hapus Seluruh Seri
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @elseif($p->parent_id)
                                        <!-- For child recurring schedules -->
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form action="{{ route('pelaksanaan.destroy', $p->id_pelaksanaan) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini saja?')">
                                                            <i class="fas fa-calendar-minus"></i> Hapus Jadwal Ini Saja
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('pelaksanaan.destroy-series', $p->id_pelaksanaan) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus SELURUH seri jadwal berulang ini?')">
                                                            <i class="fas fa-trash-alt"></i> Hapus Seluruh Seri
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <!-- For single schedules -->
                                        <form action="{{ route('pelaksanaan.destroy', $p->id_pelaksanaan) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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