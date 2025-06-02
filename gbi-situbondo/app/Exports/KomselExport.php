<?php
// app/Exports/KomselExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class KomselExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
            'Nama Komsel',
            'Pemimpin',
            'Jumlah Anggota',
            'Lokasi',
            'Hari',
            'Jam'
        ];
    }

    public function map($komsel): array
    {
        return [
            $komsel->nama_komsel,
            $komsel->pemimpin->nama ?? 'Belum ditentukan',
            $komsel->anggota->count(),
            $komsel->lokasi ?? '-',
            $komsel->hari ?? '-',
            $komsel->jam_mulai . ' - ' . $komsel->jam_selesai
        ];
    }

    public function title(): string
    {
        return 'Laporan Komsel';
    }
}