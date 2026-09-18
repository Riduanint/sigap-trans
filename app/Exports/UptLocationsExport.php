<?php

namespace App\Exports;

use App\Models\UptLocation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UptLocationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected ?int $regencyId;
    protected ?string $status;

    public function __construct(?int $regencyId = null, ?string $status = null)
    {
        $this->regencyId = $regencyId;
        $this->status = $status;
    }

    public function collection()
    {
        $query = UptLocation::with('regency')->orderBy('upt_number');

        if ($this->regencyId) {
            $query->where('regency_id', $this->regencyId);
        }

        if ($this->status && $this->status !== 'all') {
            $query->where('issue_status', $this->status);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NO UPT',
            'KABUPATEN',
            'NAMA UPT ASAL',
            'DESA DEFINITIF SAAT INI',
            'POLA USAHA',
            'TAHUN PENEMPATAN',
            'KK PENEMPATAN',
            'JIWA PENEMPATAN',
            'TAHUN / TANGGAL BAST',
            'KK PENYERAHAN',
            'JIWA PENYERAHAN',
            'STATUS LAHAN',
            'STATUS SHM',
            'CATATAN PERKEMBANGAN LAPANGAN',
            'LATITUDE',
            'LONGITUDE',
        ];
    }

    public function map($upt): array
    {
        return [
            'UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT),
            $upt->regency?->name,
            $upt->upt_name,
            $upt->current_village_name,
            $upt->business_pattern,
            $upt->placement_year,
            $upt->placement_kk,
            $upt->placement_population,
            $upt->handover_year ?: '-',
            $upt->handover_kk,
            $upt->handover_population,
            strtoupper($upt->issue_status),
            $upt->shm_status ?: 'SHM Tuntas 100%',
            $upt->issue_note ?: 'Lokasi beroperasi normal.',
            $upt->latitude,
            $upt->longitude,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Baris Header: Biru Navy Kedinasan (#0B1849) dengan teks putih tebal
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'name' => 'Inter',
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0B1849'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
