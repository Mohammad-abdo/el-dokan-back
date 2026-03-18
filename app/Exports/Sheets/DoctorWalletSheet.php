<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorWalletSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Wallet & Revenue';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['WALLET SUMMARY'];
        $rows[] = ['Current Balance',     'EGP ' . number_format($this->data['balance'] ?? 0, 2)];
        $rows[] = ['Pending Balance',     'EGP ' . number_format($this->data['pending_balance'] ?? 0, 2)];
        $rows[] = ['Total Earnings',      'EGP ' . number_format($this->data['total_earnings'] ?? 0, 2)];
        $rows[] = ['Commission Rate',     ($this->data['commission_rate'] ?? 0) . '%'];
        $rows[] = ['Total Transactions',  $this->data['transactions_count'] ?? 0];
        $rows[] = [];

        $rows[] = ['BY TRANSACTION TYPE'];
        $rows[] = ['Type', 'Count', 'Total Amount'];
        foreach ($this->data['by_type'] ?? [] as $type => $d) {
            $rows[] = [
                ucfirst(str_replace('_', ' ', $type)),
                $d['count'],
                'EGP ' . number_format($d['total'] ?? 0, 2),
            ];
        }
        $rows[] = [];

        $rows[] = ['ALL TRANSACTIONS'];
        $rows[] = ['#', 'Type', 'Amount', 'Description', 'Status', 'Booking ID', 'Date'];

        foreach ($this->data['transactions'] ?? [] as $i => $t) {
            $rows[] = [
                $i + 1,
                ucfirst(str_replace('_', ' ', $t['type'] ?? '-')),
                'EGP ' . number_format($t['amount'] ?? 0, 2),
                $t['description'] ?? '-',
                ucfirst($t['status'] ?? '-'),
                $t['booking_id'] ?? '-',
                isset($t['created_at']) ? substr($t['created_at'], 0, 10) : '-',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $byTypeCount = count($this->data['by_type'] ?? []);
        $txHeaderRow = 12 + $byTypeCount;
        return [
            1            => ['font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E40AF']]],
            8            => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            9            => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            $txHeaderRow => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            $txHeaderRow + 1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']]],
        ];
    }
}
