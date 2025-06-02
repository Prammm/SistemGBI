<?php
// app/Exports/KehadiranExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class KehadiranExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
            'Tanggal',
            'Nama Anggota',
            'Kegiatan',
            'Waktu Absensi'
        ];
    }

    public function map($kehadiran): array
    {
        return [
            Carbon::parse($kehadiran->waktu_absensi)->format('d-m-Y'),
            $kehadiran->anggota->nama ?? 'Tidak Diketahui',
            $kehadiran->pelaksanaan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui',
            Carbon::parse($kehadiran->waktu_absensi)->format('H:i')
        ];
    }

    public function title(): string
    {
        return 'Laporan Kehadiran';
    }
}