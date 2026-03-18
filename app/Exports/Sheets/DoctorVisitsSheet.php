<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorVisitsSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Visits';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['VISITS SUMMARY'];
        $rows[] = ['Total Visits', $this->data['total'] ?? 0];

        $rows[] = [];
        $rows[] = ['BY STATUS'];
        $rows[] = ['Status', 'Count'];
        foreach ($this->data['by_status'] ?? [] as $status => $count) {
            $rows[] = [ucfirst($status), $count];
        }

        $rows[] = [];
        $rows[] = ['ALL VISITS'];
        $rows[] = ['#', 'Representative', 'Rep Phone', 'Visit Date', 'Time', 'Purpose', 'Notes', 'Status', 'Rejection Reason', 'Doctor Confirmed'];

        foreach ($this->data['visits'] ?? [] as $i => $v) {
            $rows[] = [
                $i + 1,
                $v['representative']['user']['username'] ?? '-',
                $v['representative']['user']['phone'] ?? '-',
                isset($v['visit_date']) ? substr($v['visit_date'], 0, 10) : '-',
                $v['visit_time'] ?? '-',
                $v['purpose'] ?? '-',
                $v['notes'] ?? '-',
                ucfirst($v['status'] ?? '-'),
                $v['rejection_reason'] ?? '-',
                $v['doctor_confirmed_at'] ? 'Yes' : 'No',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $byStatusCount = count($this->data['by_status'] ?? []);
        $headerRow = 9 + $byStatusCount;
        return [
            1          => ['font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E40AF']]],
            4          => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            5          => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            $headerRow => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            $headerRow + 1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']]],
        ];
    }
}
