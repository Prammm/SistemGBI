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
        .date {
            text-align: right;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
    
    <div class="date">
        Tanggal: {{ $date }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Pelayan</th>
                <th>Kegiatan</th>
                <th>Posisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $pelayanan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($pelayanan->tanggal_pelayanan)->format('d-m-Y') }}</td>
                <td>{{ $pelayanan->anggota->nama ?? 'Tidak Diketahui' }}</td>
                <td>{{ $pelayanan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui' }}</td>
                <td>{{ $pelayanan->posisi ?? 'Tidak Diketahui' }}</td>
                <td>{{ ucfirst($pelayanan->status_konfirmasi) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        &copy; {{ date('Y') }} GBI Situbondo. All rights reserved.
    </div>
</body>
</html>