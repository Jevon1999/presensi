<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendancesExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $data;
    protected $params;
    protected $stats;

    public function __construct(array $data, array $params, array $stats)
    {
        $this->data = $data;
        $this->params = $params;
        $this->stats = $stats;
    }

    public function headings(): array
    {
        return [
            ['PT. GLOBAL INTERMEDIA LINTAS BATAS'],
            ['LAPORAN REKAPITULASI ABSENSI PESERTA MAGANG'],
            [''],
            ['Periode Laporan', \Carbon\Carbon::parse($this->params['start_date'])->translatedFormat('d F Y') . ' s/d ' . \Carbon\Carbon::parse($this->params['end_date'])->translatedFormat('d F Y')],
            ['Dibuat Pada', now()->translatedFormat('d F Y, H:i') . ' WIB'],
            ['Total Data', count($this->data) . ' record absensi'],
            [''],
            ['RINGKASAN KEHADIRAN'],
            ['Status', 'Jumlah', 'Persentase'],
            ['Hadir', $this->stats['hadir'] ?? 0, $this->calcPct($this->stats['hadir'] ?? 0)],
            ['Izin', $this->stats['izin'] ?? 0, $this->calcPct($this->stats['izin'] ?? 0)],
            ['Sakit', $this->stats['sakit'] ?? 0, $this->calcPct($this->stats['sakit'] ?? 0)],
            ['Alpha', $this->stats['alpha'] ?? 0, $this->calcPct($this->stats['alpha'] ?? 0)],
            ['WFO', $this->stats['wfo'] ?? 0, $this->calcPct($this->stats['wfo'] ?? 0)],
            ['WFA', $this->stats['wfa'] ?? 0, $this->calcPct($this->stats['wfa'] ?? 0)],
            ['TOTAL', count($this->data), '100%'],
            [''],
            ['DETAIL DATA ABSENSI'],
            [
                'No',
                'Tanggal',
                'Nama Lengkap',
                'Asal Sekolah / Kampus',
                'Jurusan',
                'Kantor',
                'Jam Check In',
                'Jam Check Out',
                'Status Kehadiran',
                'Tipe Kehadiran',
                'Terlambat',
                'Keterangan / Alasan'
            ]
        ];
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;
        foreach ($this->data as $att) {
            $member = $att['member'] ?? [];
            $isLate = ($att['is_late'] ?? false) ? 'Ya' : 'Tidak';
            $status = strtoupper($att['status'] ?? '-');
            $workType = isset($att['work_type']) && $att['work_type'] ? strtoupper($att['work_type']) : '-';
            
            $reason = '-';
            if (!empty($att['permissions']) && count($att['permissions']) > 0) {
                $reason = $att['permissions'][0]['keterangan'] ?? $att['permissions'][0]['reason'] ?? '-';
            } elseif ($status !== 'HADIR') {
                $reason = 'Tanpa Keterangan';
            }

            $rows[] = [
                $no++,
                $att['tanggal'] ?? '-',
                $member['nama_lengkap'] ?? $member['name'] ?? '-',
                $member['asal_sekolah'] ?? '-',
                $member['jurusan'] ?? '-',
                $member['office']['name'] ?? '-',
                $att['check_in_time'] ?? '-',
                $att['check_out_time'] ?? '-',
                $status,
                $workType,
                $isLate,
                $reason
            ];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style header instansi
            1    => ['font' => ['bold' => true, 'size' => 14]],
            2    => ['font' => ['bold' => true, 'size' => 12]],
            
            // Style table detail headers (baris ke-19)
            19   => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F2937'] // Slate-800
                ]
            ],
            
            // Style ringkasan headers (baris ke-8 dan ke-9)
            8   => ['font' => ['bold' => true]],
            9   => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF475569'] // Slate-600
                ]
            ],
            
            // Detail
            18  => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Merge title
                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');
                
                // Add borders to Ringkasan table
                $sheet->getStyle('A9:C16')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Add borders to Detail table
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A19:L' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
    
    private function calcPct($value) {
        $total = count($this->data);
        if ($total == 0) return '0%';
        return round(($value / $total) * 100, 1) . '%';
    }
}
