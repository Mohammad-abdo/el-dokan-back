<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorBookingsSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Bookings';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['BOOKINGS SUMMARY'];
        $rows[] = ['Total Bookings',     $this->data['total'] ?? 0];
        $rows[] = ['Completed Revenue',  'EGP ' . number_format($this->data['total_revenue'] ?? 0, 2)];
        $rows[] = ['Avg Booking Value',  'EGP ' . number_format($this->data['avg_booking_value'] ?? 0, 2)];
        $rows[] = [];

        $rows[] = ['BY STATUS'];
        $rows[] = ['Status', 'Count', 'Revenue'];
        foreach ($this->data['by_status'] ?? [] as $status => $d) {
            $rows[] = [ucfirst($status), $d['count'], 'EGP ' . number_format($d['total'] ?? 0, 2)];
        }
        $rows[] = [];

        $rows[] = ['BY BOOKING TYPE'];
        $rows[] = ['Type', 'Count'];
        foreach ($this->data['by_type'] ?? [] as $type => $count) {
            $rows[] = [ucfirst(str_replace('_', ' ', $type)), $count];
        }
        $rows[] = [];

        $rows[] = ['BY PAYMENT METHOD'];
        $rows[] = ['Method', 'Count', 'Total'];
        foreach ($this->data['by_payment_method'] ?? [] as $method => $d) {
            $rows[] = [ucfirst(str_replace('_', ' ', $method)), $d['count'], 'EGP ' . number_format($d['total'] ?? 0, 2)];
        }
        $rows[] = [];

        $rows[] = ['ALL BOOKINGS'];
        $rows[] = [
            '#', 'Booking No.', 'Patient', 'Phone', 'Email',
            'Date', 'Time', 'Type', 'Status',
            'Amount', 'Payment Method', 'Payment Status',
            'Rating', 'Complaint',
        ];

        foreach ($this->data['recent'] ?? [] as $i => $b) {
            $rows[] = [
                $i + 1,
                $b['booking_number'] ?? '-',
                $b['patient_name'] ?? ($b['user']['username'] ?? '-'),
                $b['user']['phone'] ?? '-',
                $b['user']['email'] ?? '-',
                isset($b['appointment_date']) ? substr($b['appointment_date'], 0, 10) : '-',
                $b['appointment_time'] ?? '-',
                ucfirst(str_replace('_', ' ', $b['booking_type'] ?? '-')),
                ucfirst($b['status'] ?? '-'),
                'EGP ' . number_format($b['total_amount'] ?? 0, 2),
                ucfirst(str_replace('_', ' ', $b['payment_method'] ?? '-')),
                ucfirst($b['payment_status'] ?? '-'),
                $b['rating'] ?? '-',
                $b['complaint'] ?? '-',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1  => ['font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E40AF']]],
            7  => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            8  => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            11 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            12 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            15 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            16 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            19 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            20 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']]],
        ];
    }
}
