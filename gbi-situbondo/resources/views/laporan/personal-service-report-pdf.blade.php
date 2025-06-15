<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .date {
            text-align: right;
            margin-bottom: 20px;
        }
        .stats-summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status-diterima { color: #155724; font-weight: bold; }
        .status-ditolak { color: #721c24; font-weight: bold; }
        .status-menunggu { color: #856404; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
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
    
    <div class="profile-info">
        <strong>Nama Pelayan:</strong> {{ $anggota->nama }}<br>
        <strong>Periode Laporan:</strong> {{ $startDate->format('d F Y') }} - {{ $endDate->format('d F Y') }}<br>
        @if($anggota->keluarga)
            <strong>Keluarga:</strong> {{ $anggota->keluarga->nama_keluarga }}<br>
        @endif
        @if($anggota->no_telepon)
            <strong>No. Telepon:</strong> {{ $anggota->no_telepon }}<br>
        @endif
    </div>
    
    <div class="stats-summary">
        <strong>RINGKASAN PELAYANAN</strong><br>
        <div class="stats-row">
            <span>Total Pelayanan:</span>
            <span>{{ $jadwalPelayanan->count() }} kali</span>
        </div>
        <div class="stats-row">
            <span>Periode:</span>
            <span>{{ round($startDate->floatDiffInMonths($endDate)) }} bulan</span>
        </div>
        <div class="stats-row">
            <span>Rata-rata per Bulan:</span>
            <span>{{ $jadwalPelayanan->count() > 0 ? round($jadwalPelayanan->count() / max(1, round($startDate->floatDiffInMonths($endDate))), 1) : 0 }} kali</span>
        </div>
        @php
            $statusCounts = $jadwalPelayanan->groupBy('status_konfirmasi')->map->count();
        @endphp
        @if($statusCounts->get('terima', 0) > 0)
        <div class="stats-row">
            <span>Pelayanan Diterima:</span>
            <span>{{ $statusCounts->get('terima', 0) }} kali</span>
        </div>
        @endif
        @if($statusCounts->get('tolak', 0) > 0)
        <div class="stats-row">
            <span>Pelayanan Ditolak:</span>
            <span>{{ $statusCounts->get('tolak', 0) }} kali</span>
        </div>
        @endif
        @if($statusCounts->get('belum', 0) > 0)
        <div class="stats-row">
            <span>Menunggu Konfirmasi:</span>
            <span>{{ $statusCounts->get('belum', 0) }} kali</span>
        </div>
        @endif
    </div>
    
    <div class="date">
        Tanggal Cetak: {{ $date }}
    </div>
    
    @if($jadwalPelayanan->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kegiatan</th>
                    <th>Posisi</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwalPelayanan as $index => $pelayanan)
                    @php
                        $status = '';
                        $statusClass = '';
                        switch($pelayanan->status_konfirmasi) {
                            case 'terima':
                                $status = 'Diterima';
                                $statusClass = 'status-diterima';
                                break;
                            case 'tolak':
                                $status = 'Ditolak';
                                $statusClass = 'status-ditolak';
                                break;
                            case 'belum':
                                $status = 'Menunggu Konfirmasi';
                                $statusClass = 'status-menunggu';
                                break;
                            default:
                                $status = 'Belum Diketahui';
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($pelayanan->tanggal_pelayanan)->format('d-m-Y') }}</td>
                        <td>{{ $pelayanan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui' }}</td>
                        <td>{{ $pelayanan->posisi ?? 'Tidak Diketahui' }}</td>
                        <td class="{{ $statusClass }}">{{ $status }}</td>
                        <td>{{ $pelayanan->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <strong>Tidak ada data pelayanan dalam periode yang dipilih.</strong>
        </div>
    @endif
    
    <div class="footer">
        &copy; {{ date('Y') }} GBI Situbondo. All rights reserved.<br>
        Laporan ini digenerate secara otomatis pada {{ \Carbon\Carbon::now()->format('d F Y H:i') }}
    </div>
</body>
</html>