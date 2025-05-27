<!DOCTYPE html>
<html>
<head>
    <title>Pengingat Jadwal Pelayanan</title>
</head>
<body>
    <h2>Pengingat Jadwal Pelayanan GBI Situbondo</h2>
    <p>Halo, {{ $nama }}</p>
    <p>Ini adalah pengingat untuk jadwal pelayanan Anda pada kegiatan {{ $kegiatan }}.</p>
    <p>Detail pelayanan:</p>
    <ul>
        <li><strong>Posisi:</strong> {{ $posisi }}</li>
        <li><strong>Tanggal:</strong> {{ $tanggal }}</li>
        <li><strong>Jam:</strong> {{ $jam_mulai }}</li>
        <li><strong>Lokasi:</strong> {{ $lokasi }}</li>
    </ul>
    <p>Mohon hadir tepat waktu dan persiapkan diri Anda dengan baik.</p>
    <p>Tuhan Yesus memberkati pelayanan Anda!</p>
    <p>Salam,<br>Pengurus GBI Situbondo</p>
</body>
</html>