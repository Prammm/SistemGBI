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
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Tanggal Lahir</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $anggota)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $anggota->nama }}</td>
                <td>{{ $anggota->jenis_kelamin == 'L' ? 'Laki-laki' : ($anggota->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                <td>{{ $anggota->tanggal_lahir ? \Carbon\Carbon::parse($anggota->tanggal_lahir)->format('d-m-Y') : '-' }}</td>
                <td>{{ $anggota->alamat ?? '-' }}</td>
                <td>{{ $anggota->no_telepon ?? '-' }}</td>
                <td>{{ $anggota->email ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        &copy; {{ date('Y') }} GBI Situbondo. All rights reserved.
    </div>
</body>
</html>