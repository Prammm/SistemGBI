<?php
// app/Exports/PelayananExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class PelayananExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
            'Nama Pelayan',
            'Kegiatan',
            'Posisi',
            'Status'
        ];
    }

    public function map($pelayanan): array
    {
        $status = '';
        switch($pelayanan->status_konfirmasi) {
            case 'terima':
                $status = 'Diterima';
                break;
            case 'tolak':
                $status = 'Ditolak';
                break;
            case 'belum':
                $status = 'Belum Dikonfirmasi';
                break;
            default:
                $status = 'Belum Diketahui';
        }
        
        return [
            Carbon::parse($pelayanan->tanggal_pelayanan)->format('d-m-Y'),
            $pelayanan->anggota->nama ?? 'Tidak Diketahui',
            $pelayanan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui',
            $pelayanan->posisi ?? 'Tidak Diketahui',
            $status
        ];
    }

    public function title(): string
    {
        return 'Laporan Pelayanan';
    }
}