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
        <strong>Nama Anggota:</strong> {{ $anggota->nama }}<br>
        <strong>Periode Laporan:</strong> {{ $startDate->format('d F Y') }} - {{ $endDate->format('d F Y') }}<br>
        @if($anggota->keluarga)
            <strong>Keluarga:</strong> {{ $anggota->keluarga->nama_keluarga }}<br>
        @endif
        @if($anggota->no_telepon)
            <strong>No. Telepon:</strong> {{ $anggota->no_telepon }}<br>
        @endif
    </div>
    
    <div class="stats-summary">
        <strong>RINGKASAN KEHADIRAN</strong><br>
        <div class="stats-row">
            <span>Total Kehadiran:</span>
            <span>{{ $kehadiran->count() }} kali</span>
        </div>
        <div class="stats-row">
            <span>Periode:</span>
            <span>{{ round($startDate->floatDiffInMonths($endDate)) }} bulan</span>
        </div>
        <div class="stats-row">
            <span>Rata-rata per Bulan:</span>
            <span>{{ $kehadiran->count() > 0 ? round($kehadiran->count() / max(1, round($startDate->floatDiffInMonths($endDate))), 1) : 0 }} kali</span>
        </div>
    </div>
    
    <div class="date">
        Tanggal Cetak: {{ $date }}
    </div>
    
    @if($kehadiran->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kegiatan</th>
                    <th>Waktu Absensi</th>
                    <th>Lokasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kehadiran as $index => $k)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($k->waktu_absensi)->format('d-m-Y') }}</td>
                    <td>{{ $k->pelaksanaan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui' }}</td>
                    <td>{{ \Carbon\Carbon::parse($k->waktu_absensi)->format('H:i') }}</td>
                    <td>{{ $k->pelaksanaan->lokasi ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <strong>Tidak ada data kehadiran dalam periode yang dipilih.</strong>
        </div>
    @endif
    
    <div class="footer">
        &copy; {{ date('Y') }} GBI Situbondo. All rights reserved.<br>
        Laporan ini digenerate secara otomatis pada {{ \Carbon\Carbon::now()->format('d F Y H:i') }}
    </div>
</body>
</html>