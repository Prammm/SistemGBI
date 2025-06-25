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
                @if(auth()->user()->id_role <= 3)
                    <a href="{{ route('pelaksanaan.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Jadwal
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kegiatan</th>
                            <th data-sort="date">Tanggal</th>
                            <th>Waktu</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            @if(auth()->user()->id_role <= 3)
                                <th style="width: 150px;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pelaksanaan as $p)
                            @php
                                $tanggalKegiatan = \Carbon\Carbon::parse($p->tanggal_kegiatan);
                                $isUpcoming = $tanggalKegiatan->isFuture();
                                $isToday = $tanggalKegiatan->isToday();
                                $isPast = $tanggalKegiatan->isPast() && !$isToday;
                                
                                // Check if attendance can be taken
                                try {
                                    $eventStartTime = $tanggalKegiatan->copy()->setTimeFromTimeString($p->jam_mulai);
                                    $eventEndTime = $tanggalKegiatan->copy()->setTimeFromTimeString($p->jam_selesai);
                                } catch (\Exception $e) {
                                    $eventStartTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                                        $tanggalKegiatan->format('Y-m-d') . ' ' . substr($p->jam_mulai, 0, 5));
                                    $eventEndTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                                        $tanggalKegiatan->format('Y-m-d') . ' ' . substr($p->jam_selesai, 0, 5));
                                }
                                $now = \Carbon\Carbon::now();
                                $canTakeAttendance = $now->gte($eventStartTime) && $now->lte($eventEndTime);
                                $isPastEvent = $eventEndTime->isPast();
                                
                                // Create proper sort value: YYYYMMDD format for proper sorting
                                $sortValue = $tanggalKegiatan->format('Ymd');
                            @endphp
                            <tr class="{{ $isPast ? 'table-secondary' : ($isToday ? 'table-warning' : '') }}">
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
                                    @if($isPast)
                                        <br><small class="text-muted"><i class="fas fa-history"></i> Selesai</small>
                                    @elseif($isToday)
                                        <br><small class="text-warning"><i class="fas fa-clock"></i> Hari ini</small>
                                    @endif
                                </td>
                                <td data-order="{{ $sortValue }}">
                                    {{ $tanggalKegiatan->format('d/m/Y') }}
                                    @if($isToday)
                                        <span class="badge bg-warning text-dark ms-1">Hari ini</span>
                                    @endif
                                </td>
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
                                @if(auth()->user()->id_role <= 3)
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('pelaksanaan.show', $p->id_pelaksanaan) }}" 
                                               class="btn btn-info btn-sm" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('pelaksanaan.edit', $p->id_pelaksanaan) }}" 
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($canTakeAttendance || $isPastEvent)
                                                <a href="{{ route('kehadiran.create', ['id_pelaksanaan' => $p->id_pelaksanaan]) }}" 
                                                   class="btn btn-success btn-sm" title="Presensi">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </a>
                                            @else
                                                <button class="btn btn-secondary btn-sm" disabled 
                                                        title="Presensi belum dapat dilakukan (kegiatan belum dimulai)">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <div class="btn-group mt-1" role="group">
                                            @if($p->is_recurring)
                                                <!-- Dropdown for recurring schedules -->
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-danger btn-sm dropdown-toggle" 
                                                            data-bs-toggle="dropdown" aria-expanded="false" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form action="{{ route('pelaksanaan.destroy', $p->id_pelaksanaan) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-warning" 
                                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini saja?')">
                                                                    <i class="fas fa-calendar-minus"></i> Hapus Jadwal Ini Saja
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('pelaksanaan.destroy-series', $p->id_pelaksanaan) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger" 
                                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus SELURUH seri jadwal berulang ini?')">
                                                                    <i class="fas fa-trash-alt"></i> Hapus Seluruh Seri
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @elseif($p->parent_id)
                                                <!-- For child recurring schedules -->
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-danger btn-sm dropdown-toggle" 
                                                            data-bs-toggle="dropdown" aria-expanded="false" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form action="{{ route('pelaksanaan.destroy', $p->id_pelaksanaan) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-warning" 
                                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini saja?')">
                                                                    <i class="fas fa-calendar-minus"></i> Hapus Jadwal Ini Saja
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('pelaksanaan.destroy-series', $p->id_pelaksanaan) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger" 
                                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus SELURUH seri jadwal berulang ini?')">
                                                                    <i class="fas fa-trash-alt"></i> Hapus Seluruh Seri
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @else
                                                <!-- For single schedules -->
                                                <form action="{{ route('pelaksanaan.destroy', $p->id_pelaksanaan) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table-secondary td {
        opacity: 0.7;
    }
    .table-warning td {
        background-color: #fff3cd !important;
    }
    .btn[disabled] {
        cursor: not-allowed;
    }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#datatablesSimple').DataTable({
        order: [[1, 'asc']], // Sort by date column
        columnDefs: [
            {
                targets: 1, // Date column
                type: 'num', // Use numeric type for YYYYMMDD format
                render: function(data, type, row) {
                    if (type === 'display') {
                        // Return the display HTML for showing
                        return data;
                    } else if (type === 'sort' || type === 'type') {
                        // Extract the data-order value for sorting
                        var $data = $(data);
                        if ($data.length) {
                            return $data.attr('data-order') || '0';
                        } else {
                            // If it's already just the data-order value
                            return data;
                        }
                    }
                    return data;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});
</script>
@endsection