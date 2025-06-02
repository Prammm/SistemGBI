<?php
// app/Exports/AnggotaExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class AnggotaExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Alamat',
            'No. Telepon',
            'Email',
            'Status'
        ];
    }

    public function map($anggota): array
    {
        $aktif = \App\Models\Kehadiran::where('id_anggota', $anggota->id_anggota)
            ->where('waktu_absensi', '>=', Carbon::now()->subMonths(3))
            ->exists();
            
        return [
            $anggota->nama,
            $anggota->jenis_kelamin == 'L' ? 'Laki-laki' : ($anggota->jenis_kelamin == 'P' ? 'Perempuan' : '-'),
            $anggota->tanggal_lahir ? Carbon::parse($anggota->tanggal_lahir)->format('d-m-Y') : '-',
            $anggota->alamat ?? '-',
            $anggota->no_telepon ?? '-',
            $anggota->email ?? '-',
            $aktif ? 'Aktif' : 'Tidak Aktif'
        ];
    }

    public function title(): string
    {
        return 'Laporan Anggota';
    }
}