<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class ExcelExportService
{
    private $spreadsheet;
    private $worksheet;
    
    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->worksheet = $this->spreadsheet->getActiveSheet();
    }
    
    /**
     * Set document properties
     */
    public function setProperties($title, $creator = 'GBI Situbondo')
    {
        $this->spreadsheet->getProperties()
            ->setCreator($creator)
            ->setTitle($title)
            ->setDescription($title)
            ->setCreated(time());
            
        return $this;
    }
    
    /**
     * Add header with logo and church info
     */
    public function addHeader($title)
    {
        // Church info
        $this->worksheet->setCellValue('A1', 'GBI SITUBONDO');
        $this->worksheet->setCellValue('A2', 'Jl. Pb. Sudirman, Karangasem, Situbondo');
        $this->worksheet->setCellValue('A3', 'Telp: (0338) 123456 | Email: gbisitubondo@example.com');
        
        // Title
        $this->worksheet->setCellValue('A5', $title);
        $this->worksheet->setCellValue('A6', 'Tanggal: ' . Carbon::now()->format('d F Y'));
        
        // Style header
        $this->worksheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $this->worksheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
        $this->worksheet->getStyle('A1:A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Merge cells for title
        $this->worksheet->mergeCells('A1:F1');
        $this->worksheet->mergeCells('A2:F2');
        $this->worksheet->mergeCells('A3:F3');
        $this->worksheet->mergeCells('A5:F5');
        $this->worksheet->mergeCells('A6:F6');
        
        return $this;
    }
    
    /**
     * Set table headers
     */
    public function setHeaders($headers, $startRow = 8)
    {
        $column = 'A';
        foreach ($headers as $header) {
            $this->worksheet->setCellValue($column . $startRow, $header);
            $column++;
        }
        
        // Style headers
        $endColumn = chr(ord('A') + count($headers) - 1);
        $headerRange = 'A' . $startRow . ':' . $endColumn . $startRow;
        
        $this->worksheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4e73df']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        
        return $this;
    }
    
    /**
     * Add data rows
     */
    public function addData($data, $startRow = 9)
    {
        $row = $startRow;
        
        foreach ($data as $rowData) {
            $column = 'A';
            foreach ($rowData as $cellValue) {
                $this->worksheet->setCellValue($column . $row, $cellValue);
                $column++;
            }
            $row++;
        }
        
        // Style data
        if (!empty($data)) {
            $endColumn = chr(ord('A') + count($data[0]) - 1);
            $dataRange = 'A' . $startRow . ':' . $endColumn . ($row - 1);
            
            $this->worksheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);
        }
        
        return $this;
    }
    
    /**
     * Auto-size columns
     */
    public function autoSizeColumns()
    {
        foreach (range('A', $this->worksheet->getHighestColumn()) as $column) {
            $this->worksheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        return $this;
    }
    
    /**
     * Download Excel file
     */
    public function download($filename)
    {
        $this->autoSizeColumns();
        
        $writer = new Xlsx($this->spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Save to file
     */
    public function save($filepath)
    {
        $this->autoSizeColumns();
        
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filepath);
        
        return $this;
    }
    
    /**
     * Export Anggota data
     */
    public static function exportAnggota($data)
    {
        $service = new static();
        
        $service->setProperties('Laporan Anggota')
            ->addHeader('LAPORAN ANGGOTA')
            ->setHeaders([
                'No', 'Nama', 'Jenis Kelamin', 'Tanggal Lahir', 
                'Alamat', 'No. Telepon', 'Email', 'Status'
            ]);
        
        $exportData = [];
        foreach ($data as $index => $anggota) {
            $aktif = \App\Models\Kehadiran::where('id_anggota', $anggota->id_anggota)
                ->where('waktu_absensi', '>=', Carbon::now()->subMonths(3))
                ->exists();
                
            $exportData[] = [
                $index + 1,
                $anggota->nama,
                $anggota->jenis_kelamin == 'L' ? 'Laki-laki' : ($anggota->jenis_kelamin == 'P' ? 'Perempuan' : '-'),
                $anggota->tanggal_lahir ? Carbon::parse($anggota->tanggal_lahir)->format('d-m-Y') : '-',
                $anggota->alamat ?? '-',
                $anggota->no_telepon ?? '-',
                $anggota->email ?? '-',
                $aktif ? 'Aktif' : 'Tidak Aktif'
            ];
        }
        
        $service->addData($exportData);
        $service->download('laporan-anggota-' . Carbon::now()->format('Y-m-d') . '.xlsx');
    }
    
    /**
     * Export Kehadiran data
     */
    public static function exportKehadiran($data, $bulan, $tahun)
    {
        $service = new static();
        
        $service->setProperties('Laporan Kehadiran')
            ->addHeader('LAPORAN KEHADIRAN - ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'))
            ->setHeaders([
                'No', 'Tanggal', 'Nama Anggota', 'Kegiatan', 'Waktu Absensi'
            ]);
        
        $exportData = [];
        foreach ($data as $index => $kehadiran) {
            $exportData[] = [
                $index + 1,
                Carbon::parse($kehadiran->waktu_absensi)->format('d-m-Y'),
                $kehadiran->anggota->nama ?? 'Tidak Diketahui',
                $kehadiran->pelaksanaan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui',
                Carbon::parse($kehadiran->waktu_absensi)->format('H:i')
            ];
        }
        
        $service->addData($exportData);
        $service->download('laporan-kehadiran-' . $bulan . '-' . $tahun . '.xlsx');
    }
    
    /**
     * Export Pelayanan data
     */
    public static function exportPelayanan($data, $bulan, $tahun)
    {
        $service = new static();
        
        $service->setProperties('Laporan Pelayanan')
            ->addHeader('LAPORAN PELAYANAN - ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'))
            ->setHeaders([
                'No', 'Tanggal', 'Nama Pelayan', 'Kegiatan', 'Posisi', 'Status'
            ]);
        
        $exportData = [];
        foreach ($data as $index => $pelayanan) {
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
            
            $exportData[] = [
                $index + 1,
                Carbon::parse($pelayanan->tanggal_pelayanan)->format('d-m-Y'),
                $pelayanan->anggota->nama ?? 'Tidak Diketahui',
                $pelayanan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui',
                $pelayanan->posisi ?? 'Tidak Diketahui',
                $status
            ];
        }
        
        $service->addData($exportData);
        $service->download('laporan-pelayanan-' . $bulan . '-' . $tahun . '.xlsx');
    }
    
    /**
     * Export Komsel data
     */
    public static function exportKomsel($data)
    {
        $service = new static();
        
        $service->setProperties('Laporan Komsel')
            ->addHeader('LAPORAN KOMSEL')
            ->setHeaders([
                'No', 'Nama Komsel', 'Pemimpin', 'Jumlah Anggota', 
                'Lokasi', 'Hari', 'Jam'
            ]);
        
        $exportData = [];
        foreach ($data as $index => $komsel) {
            $exportData[] = [
                $index + 1,
                $komsel->nama_komsel,
                $komsel->pemimpin->nama ?? 'Belum ditentukan',
                $komsel->anggota->count(),
                $komsel->lokasi ?? '-',
                $komsel->hari ?? '-',
                ($komsel->jam_mulai && $komsel->jam_selesai) 
                    ? $komsel->jam_mulai . ' - ' . $komsel->jam_selesai 
                    : '-'
            ];
        }
        
        $service->addData($exportData);
        $service->download('laporan-komsel-' . Carbon::now()->format('Y-m-d') . '.xlsx');
    }
    
    /**
     * Export Personal Report data
     */
    public static function exportPersonalReport($kehadiran, $anggota, $startDate, $endDate)
    {
        $service = new static();
        
        $title = 'LAPORAN KEHADIRAN PRIBADI - ' . strtoupper($anggota->nama);
        $service->setProperties('Laporan Kehadiran Pribadi')
            ->addHeader($title)
            ->setHeaders([
                'No', 'Tanggal', 'Kegiatan', 'Waktu Absensi', 'Lokasi'
            ]);
        
        $exportData = [];
        foreach ($kehadiran as $index => $k) {
            $exportData[] = [
                $index + 1,
                Carbon::parse($k->waktu_absensi)->format('d-m-Y'),
                $k->pelaksanaan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui',
                Carbon::parse($k->waktu_absensi)->format('H:i'),
                $k->pelaksanaan->lokasi ?? '-'
            ];
        }
        
        $service->addData($exportData);
        $filename = 'laporan-kehadiran-' . strtolower(str_replace(' ', '-', $anggota->nama)) . 
                   '-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';
        $service->download($filename);
    }
    
    /**
     * Export Komsel Report data
     */
    public static function exportKomselReport($pelaksanaanKomsel, $kehadiran, $attendanceStats, $selectedKomsel, $startDate, $endDate)
    {
        $service = new static();
        
        $title = 'LAPORAN KOMSEL - ' . strtoupper($selectedKomsel->nama_komsel);
        $service->setProperties('Laporan Komsel')
            ->addHeader($title);
        
        // Summary section
        $service->worksheet->setCellValue('A8', 'RINGKASAN KEHADIRAN');
        $service->worksheet->getStyle('A8')->getFont()->setBold(true);
        
        $service->setHeaders([
            'No', 'Nama Anggota', 'Total Hadir', 'Total Pertemuan', 'Persentase (%)', 'Status'
        ], 9);
        
        $exportData = [];
        $index = 1;
        foreach ($attendanceStats as $stats) {
            $percentage = $stats['persentase'];
            if ($percentage >= 90) {
                $status = 'Sangat Baik';
            } elseif ($percentage >= 75) {
                $status = 'Baik';
            } elseif ($percentage >= 50) {
                $status = 'Cukup';
            } else {
                $status = 'Kurang';
            }
            
            $exportData[] = [
                $index++,
                $stats['anggota']->nama,
                $stats['total_kehadiran'],
                $stats['total_kegiatan'],
                $percentage,
                $status
            ];
        }
        
        $service->addData($exportData, 10);
        
        // Meeting history section
        $lastRow = 10 + count($exportData) + 2;
        $service->worksheet->setCellValue('A' . $lastRow, 'RIWAYAT PERTEMUAN');
        $service->worksheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
        
        $service->setHeaders([
            'No', 'Tanggal', 'Waktu', 'Lokasi', 'Jumlah Hadir'
        ], $lastRow + 1);
        
        $meetingData = [];
        $index = 1;
        foreach ($pelaksanaanKomsel as $pertemuan) {
            $pertemuanKehadiran = $kehadiran->where('id_pelaksanaan', $pertemuan->id_pelaksanaan);
            $attendanceCount = $pertemuanKehadiran->count();
            
            $meetingData[] = [
                $index++,
                Carbon::parse($pertemuan->tanggal_kegiatan)->format('d-m-Y'),
                Carbon::parse($pertemuan->jam_mulai)->format('H:i') . ' - ' . 
                Carbon::parse($pertemuan->jam_selesai)->format('H:i'),
                $pertemuan->lokasi ?? '-',
                $attendanceCount
            ];
        }
        
        $service->addData($meetingData, $lastRow + 2);
        
        $filename = 'laporan-komsel-' . strtolower(str_replace(' ', '-', $selectedKomsel->nama_komsel)) . 
                   '-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';
        $service->download($filename);
    }
    
    /**
     * Export Personal Service Report data
     */
    public static function exportPersonalServiceReport($jadwalPelayanan, $anggota, $startDate, $endDate)
    {
        $service = new static();
        
        $title = 'RIWAYAT PELAYANAN - ' . strtoupper($anggota->nama);
        $service->setProperties('Riwayat Pelayanan Pribadi')
            ->addHeader($title)
            ->setHeaders([
                'No', 'Tanggal', 'Kegiatan', 'Posisi', 'Status', 'Keterangan'
            ]);
        
        $exportData = [];
        foreach ($jadwalPelayanan as $index => $pelayanan) {
            $status = '';
            switch($pelayanan->status_konfirmasi) {
                case 'terima':
                    $status = 'Diterima';
                    break;
                case 'tolak':
                    $status = 'Ditolak';
                    break;
                case 'belum':
                    $status = 'Menunggu Konfirmasi';
                    break;
                default:
                    $status = 'Belum Diketahui';
            }
            
            $exportData[] = [
                $index + 1,
                Carbon::parse($pelayanan->tanggal_pelayanan)->format('d-m-Y'),
                $pelayanan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui',
                $pelayanan->posisi ?? 'Tidak Diketahui',
                $status,
                $pelayanan->keterangan ?? '-'
            ];
        }
        
        $service->addData($exportData);
        $filename = 'riwayat-pelayanan-' . strtolower(str_replace(' ', '-', $anggota->nama)) . 
                   '-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';
        $service->download($filename);
    }
}