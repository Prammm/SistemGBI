<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 14px;
            margin-bottom: 15px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .filter-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .stats-summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            text-align: center;
        }
        .stat-item {
            padding: 10px;
            background-color: white;
            border-radius: 3px;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .date {
            text-align: right;
            margin-bottom: 20px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .status-diterima { 
            color: #155724; 
            font-weight: bold; 
        }
        .status-ditolak { 
            color: #721c24; 
            font-weight: bold; 
        }
        .status-menunggu { 
            color: #856404; 
            font-weight: bold; 
        }
        .reguler-ya {
            color: #155724;
            font-weight: bold;
        }
        .reguler-tidak {
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
        .group-header {
            background-color: #f6c23e;
            color: white;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GBI SITUBONDO</h1>
        <div>Jl. Pb. Sudirman, Karangasem, Situbondo</div>
        <div>Telp: (0338) 123456 | Email: gbisitubondo@example.com</div>
    </div>
    
    <h1>{{ $title }}</h1>
    
    @if(!empty($filters) && array_filter($filters))
        <div class="filter-info">
            <strong>FILTER YANG DITERAPKAN:</strong><br>
            @if(!empty($filters['start_date']))
                <strong>Tanggal Mulai:</strong> {{ \Carbon\Carbon::parse($filters['start_date'])->format('d F Y') }}<br>
            @endif
            @if(!empty($filters['end_date']))
                <strong>Tanggal Selesai:</strong> {{ \Carbon\Carbon::parse($filters['end_date'])->format('d F Y') }}<br>
            @endif
            @if(!empty($filters['posisi']))
                <strong>Posisi:</strong> {{ $filters['posisi'] }}<br>
            @endif
            @if(!empty($filters['status']))
                <strong>Status:</strong> 
                @switch($filters['status'])
                    @case('terima') Diterima @break
                    @case('tolak') Ditolak @break
                    @case('belum') Belum Konfirmasi @break
                    @default {{ $filters['status'] }}
                @endswitch
                <br>
            @endif
        </div>
    @endif
    
    <div class="stats-summary">
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['total'] ?? $historyData->count() }}</div>
            <div class="stat-label">Total Riwayat</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['accepted'] ?? 0 }}</div>
            <div class="stat-label">Diterima</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['rejected'] ?? 0 }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['pending'] ?? 0 }}</div>
            <div class="stat-label">Belum Konfirmasi</div>
        </div>
    </div>
    
    <div class="date">
        Tanggal Cetak: {{ $date }}
    </div>
    
    @if($historyData->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 8%;">Tanggal</th>
                    <th style="width: 6%;">Hari</th>
                    <th style="width: 15%;">Kegiatan</th>
                    <th style="width: 8%;">Waktu</th>
                    <th style="width: 10%;">Lokasi</th>
                    <th style="width: 8%;">Posisi</th>
                    <th style="width: 12%;">Nama Petugas</th>
                    <th style="width: 8%;">No. Telepon</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 5%;">Reguler</th>
                    <th style="width: 9%;">Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historyData as $index => $jadwal)
                    @php
                        $status = '';
                        $statusClass = '';
                        switch($jadwal->status_konfirmasi) {
                            case 'terima':
                                $status = 'Diterima';
                                $statusClass = 'status-diterima';
                                break;
                            case 'tolak':
                                $status = 'Ditolak';
                                $statusClass = 'status-ditolak';
                                break;
                            case 'belum':
                                $status = 'Belum Konfirmasi';
                                $statusClass = 'status-menunggu';
                                break;
                            default:
                                $status = 'Belum Diketahui';
                        }
                        
                        $isReguler = $jadwal->is_reguler || 
                                    (method_exists($jadwal->anggota, 'isRegularIn') && 
                                     $jadwal->anggota->isRegularIn($jadwal->posisi));
                        
                        // Get activity info
                        $kegiatan = $jadwal->pelaksanaan->kegiatan->nama_kegiatan ?? 
                                   $jadwal->kegiatan->nama_kegiatan ?? 'Tidak Diketahui';
                        
                        $waktu = '';
                        $lokasi = '';
                        if ($jadwal->pelaksanaan) {
                            $waktu = \Carbon\Carbon::parse($jadwal->pelaksanaan->jam_mulai)->format('H:i') . ' - ' . 
                                    \Carbon\Carbon::parse($jadwal->pelaksanaan->jam_selesai)->format('H:i');
                            $lokasi = $jadwal->pelaksanaan->lokasi ?? '-';
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->format('D') }}</td>
                        <td>{{ $kegiatan }}</td>
                        <td>{{ $waktu }}</td>
                        <td>{{ $lokasi }}</td>
                        <td>{{ $jadwal->posisi ?? '-' }}</td>
                        <td>{{ $jadwal->anggota->nama ?? 'Tidak Diketahui' }}</td>
                        <td>{{ $jadwal->anggota->no_telepon ?? '-' }}</td>
                        <td class="{{ $statusClass }}">{{ $status }}</td>
                        <td class="{{ $isReguler ? 'reguler-ya' : 'reguler-tidak' }}">
                            {{ $isReguler ? 'Ya' : 'Tidak' }}
                        </td>
                        <td>{{ $jadwal->created_at ? \Carbon\Carbon::parse($jadwal->created_at)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    
                    {{-- Page break every 25 rows --}}
                    @if(($index + 1) % 25 == 0 && !$loop->last)
                        </tbody>
                        </table>
                        <div class="page-break"></div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 3%;">No</th>
                                    <th style="width: 8%;">Tanggal</th>
                                    <th style="width: 6%;">Hari</th>
                                    <th style="width: 15%;">Kegiatan</th>
                                    <th style="width: 8%;">Waktu</th>
                                    <th style="width: 10%;">Lokasi</th>
                                    <th style="width: 8%;">Posisi</th>
                                    <th style="width: 12%;">Nama Petugas</th>
                                    <th style="width: 8%;">No. Telepon</th>
                                    <th style="width: 8%;">Status</th>
                                    <th style="width: 5%;">Reguler</th>
                                    <th style="width: 9%;">Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                    @endif
                @endforeach
            </tbody>
        </table>
        
        {{-- Summary by month if data spans multiple months --}}
        @php
            $groupedByMonth = $historyData->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->tanggal_pelayanan)->format('Y-m');
            });
        @endphp
        
        @if($groupedByMonth->count() > 1)
            <div class="page-break"></div>
            <div class="group-header">RINGKASAN PER BULAN</div>
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Total</th>
                        <th>Diterima</th>
                        <th>Ditolak</th>
                        <th>Belum Konfirmasi</th>
                        <th>Persentase Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedByMonth as $monthYear => $schedules)
                        @php
                            $monthName = \Carbon\Carbon::createFromFormat('Y-m', $monthYear)->format('F Y');
                            $total = $schedules->count();
                            $accepted = $schedules->where('status_konfirmasi', 'terima')->count();
                            $rejected = $schedules->where('status_konfirmasi', 'tolak')->count();
                            $pending = $schedules->where('status_konfirmasi', 'belum')->count();
                            $percentage = $total > 0 ? round(($accepted / $total) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>{{ $monthName }}</td>
                            <td>{{ $total }}</td>
                            <td class="status-diterima">{{ $accepted }}</td>
                            <td class="status-ditolak">{{ $rejected }}</td>
                            <td class="status-menunggu">{{ $pending }}</td>
                            <td>{{ $percentage }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <strong>Tidak ada data riwayat pelayanan yang sesuai dengan filter yang dipilih.</strong>
        </div>
    @endif
    
    <div class="footer">
        &copy; {{ date('Y') }} GBI Situbondo. All rights reserved.<br>
        Laporan ini digenerate secara otomatis pada {{ \Carbon\Carbon::now()->format('d F Y H:i') }}<br>
        Total {{ $historyData->count() }} riwayat pelayanan
    </div>
</body>
</html>