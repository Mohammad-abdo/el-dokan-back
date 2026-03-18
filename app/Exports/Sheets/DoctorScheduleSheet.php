<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorScheduleSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Schedule & Pricing';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['SCHEDULE & PRICING'];
        $rows[] = [];
        $rows[] = ['Field', 'Value'];
        $rows[] = ['Working Days',          implode(', ', (array) ($this->data['available_days'] ?? []))];
        $rows[] = ['Start Time',            $this->data['available_hours_start'] ?? '-'];
        $rows[] = ['End Time',              $this->data['available_hours_end'] ?? '-'];
        $rows[] = ['Consultation Duration', ($this->data['consultation_duration'] ?? '-') . ' minutes'];
        $rows[] = ['Consultation Price',    'EGP ' . number_format($this->data['consultation_price'] ?? 0, 2)];
        $rows[] = ['Discount',             ($this->data['discount_percentage'] ?? 0) . '%'];
        $rows[] = ['Effective Price',      'EGP ' . number_format($this->data['effective_price'] ?? 0, 2)];

        $rows[] = [];
        $rows[] = ['WORKING DAYS BREAKDOWN'];
        $rows[] = ['Day', 'Working'];
        $allDays = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $activeDays = array_map('strtolower', (array) ($this->data['available_days'] ?? []));
        foreach ($allDays as $day) {
            $rows[] = [ucfirst($day), in_array($day, $activeDays) ? '✔ Yes' : '✘ No'];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1  => ['font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E40AF']]],
            3  => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            12 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF1E40AF']]],
            13 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']]],
        ];
    }
}
