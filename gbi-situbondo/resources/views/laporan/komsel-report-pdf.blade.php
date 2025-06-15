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
        .komsel-info {
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
            margin-top: 15px;
            margin-bottom: 30px;
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
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
        }
        .section-title {
            background-color: #f6c23e;
            color: white;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .status-excellent { color: #155724; }
        .status-good { color: #0c5460; }
        .status-fair { color: #856404; }
        .status-poor { color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GBI SITUBONDO</h1>
        <div>Jl. Pb. Sudirman, Karangasem, Situbondo</div>
        <div>Telp: (0338) 123456 | Email: gbisitubondo@example.com</div>
    </div>
    
    <h1>{{ $title }}</h1>
    
    <div class="komsel-info">
        <strong>Nama Komsel:</strong> {{ $selectedKomsel->nama_komsel }}<br>
        <strong>Pemimpin:</strong> {{ $selectedKomsel->pemimpin->nama ?? 'Belum ditentukan' }}<br>
        <strong>Jumlah Anggota:</strong> {{ $selectedKomsel->anggota->count() }} orang<br>
        <strong>Periode Laporan:</strong> {{ $startDate->format('d F Y') }} - {{ $endDate->format('d F Y') }}<br>
        @if($selectedKomsel->lokasi)
            <strong>Lokasi:</strong> {{ $selectedKomsel->lokasi }}<br>
        @endif
        @if($selectedKomsel->hari)
            <strong>Jadwal:</strong> {{ $selectedKomsel->hari }}, {{ $selectedKomsel->jam ?? '-' }}<br>
        @endif
    </div>
    
    <div class="stats-summary">
        <strong>RINGKASAN AKTIVITAS</strong><br>
        <div class="stats-row">
            <span>Total Pertemuan:</span>
            <span>{{ $pelaksanaanKomsel->count() }} kali</span>
        </div>
        <div class="stats-row">
            <span>Total Kehadiran:</span>
            <span>{{ $kehadiran->count() }} orang</span>
        </div>
        <div class="stats-row">
            <span>Rata-rata Kehadiran:</span>
            <span>
                @php
                    $statsArray = is_array($attendanceStats) ? $attendanceStats : $attendanceStats->toArray();
                    $avgPercentage = $selectedKomsel->anggota->count() > 0 ? 
                        round((array_sum(array_column($statsArray, 'persentase')) / count($statsArray)), 1) : 0;
                @endphp
                {{ $avgPercentage }}%
            </span>
        </div>
    </div>
    
    <div class="date">
        Tanggal Cetak: {{ $date }}
    </div>
    
    @if(count($attendanceStats) > 0)
        <div class="section-title">STATISTIK KEHADIRAN ANGGOTA</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>Total Hadir</th>
                    <th>Total Pertemuan</th>
                    <th>Persentase (%)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceStats as $index => $stats)
                    @php
                        $percentage = $stats['persentase'];
                        if ($percentage >= 90) {
                            $statusClass = 'status-excellent';
                            $statusText = 'Sangat Baik';
                        } elseif ($percentage >= 75) {
                            $statusClass = 'status-good';
                            $statusText = 'Baik';
                        } elseif ($percentage >= 50) {
                            $statusClass = 'status-fair';
                            $statusText = 'Cukup';
                        } else {
                            $statusClass = 'status-poor';
                            $statusText = 'Kurang';
                        }
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $stats['anggota']->nama }}</td>
                        <td>{{ $stats['total_kehadiran'] }}</td>
                        <td>{{ $stats['total_kegiatan'] }}</td>
                        <td>{{ $percentage }}%</td>
                        <td class="{{ $statusClass }}">{{ $statusText }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    @if($pelaksanaanKomsel->count() > 0)
        <div class="section-title">RIWAYAT PERTEMUAN KOMSEL</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Lokasi</th>
                    <th>Jumlah Hadir</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pelaksanaanKomsel as $index => $pertemuan)
                    @php
                        $pertemuanKehadiran = $kehadiran->where('id_pelaksanaan', $pertemuan->id_pelaksanaan);
                        $attendanceCount = $pertemuanKehadiran->count();
                        $totalMembers = $selectedKomsel->anggota->count();
                        $attendancePercentage = $totalMembers > 0 ? round(($attendanceCount / $totalMembers) * 100) : 0;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($pertemuan->tanggal_kegiatan)->format('d-m-Y') }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($pertemuan->jam_mulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($pertemuan->jam_selesai)->format('H:i') }}
                        </td>
                        <td>{{ $pertemuan->lokasi ?? '-' }}</td>
                        <td>{{ $attendanceCount }}/{{ $totalMembers }}</td>
                        <td>{{ $attendancePercentage }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <strong>Tidak ada data pertemuan dalam periode yang dipilih.</strong>
        </div>
    @endif
    
    <div class="footer">
        &copy; {{ date('Y') }} GBI Situbondo. All rights reserved.<br>
        Laporan ini digenerate secara otomatis pada {{ \Carbon\Carbon::now()->format('d F Y H:i') }}
    </div>
</body>
</html>