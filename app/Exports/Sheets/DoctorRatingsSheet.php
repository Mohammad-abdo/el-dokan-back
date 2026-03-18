<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorRatingsSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Ratings & Reviews';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['RATINGS & REVIEWS SUMMARY'];
        $rows[] = ['Total Ratings',   $this->data['total'] ?? 0];
        $rows[] = ['Average Rating',  $this->data['average'] ?? 0];
        $rows[] = ['Approved',        $this->data['approved'] ?? 0];
        $rows[] = [];

        $rows[] = ['RATING DISTRIBUTION'];
        $rows[] = ['Stars', 'Count'];
        foreach (array_reverse(range(1, 5)) as $stars) {
            $rows[] = [str_repeat('★', $stars) . ' (' . $stars . ' stars)', $this->data['distribution'][(string) $stars] ?? 0];
        }
        $rows[] = [];

        $rows[] = ['ALL REVIEWS'];
        $rows[] = ['#', 'Patient', 'Phone', 'Rating', 'Comment', 'Approved', 'Date'];

        foreach ($this->data['ratings'] ?? [] as $i => $r) {
            $rows[] = [
                $i + 1,
                $r['user']['username'] ?? '-',
                $r['user']['phone'] ?? '-',
                $r['rating'] ?? '-',
                $r['comment'] ?? '-',
                ($r['is_approved'] ?? false) ? 'Yes' : 'No',
                isset($r['created_at']) ? substr($r['created_at'], 0, 10) : '-',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1  => ['font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E40AF']]],
            6  => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            7  => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            14 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            15 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']]],
        ];
    }
}
