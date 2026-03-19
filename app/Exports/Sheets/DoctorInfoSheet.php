<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorInfoSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(
        private array $doctor,
        private array $kpis,
        private array $period
    ) {}

    public function title(): string
    {
        return 'Doctor Info';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['DOCTOR REPORT – ' . strtoupper($this->doctor['name'] ?? 'DOCTOR')];
        $rows[] = ['Period', $this->period['from'] . ' to ' . $this->period['to']];
        $rows[] = ['Generated', date('Y-m-d H:i:s')];
        $rows[] = [];

        $rows[] = ['DOCTOR PROFILE'];
        $rows[] = ['Field', 'Value'];
        $rows[] = ['Doctor ID',            $this->doctor['id'] ?? '-'];
        $rows[] = ['Name (English)',        $this->doctor['name_en'] ?? $this->doctor['name'] ?? '-'];
        $rows[] = ['Name (Arabic)',         $this->doctor['name_ar'] ?? '-'];
        $rows[] = ['Specialty (English)',   $this->doctor['specialty_en'] ?? $this->doctor['specialty'] ?? '-'];
        $rows[] = ['Specialty (Arabic)',    $this->doctor['specialty_ar'] ?? '-'];
        $rows[] = ['Location (English)',    $this->doctor['location_en'] ?? $this->doctor['location'] ?? '-'];
        $rows[] = ['Location (Arabic)',     $this->doctor['location_ar'] ?? '-'];
        $rows[] = ['Status',               $this->doctor['status'] ?? 'active'];
        $rows[] = ['Active',               ($this->doctor['is_active'] ?? false) ? 'Yes' : 'No'];
        $rows[] = ['Rating',               $this->doctor['rating'] ?? '0.00'];
        $rows[] = ['Consultation Price',   'EGP ' . number_format($this->doctor['consultation_price'] ?? 0, 2)];
        $rows[] = ['Discount',             ($this->doctor['discount_percentage'] ?? 0) . '%'];
        $rows[] = ['Duration',             ($this->doctor['consultation_duration'] ?? '-') . ' min'];
        $rows[] = ['Available Days',       implode(', ', (array) ($this->doctor['available_days'] ?? []))];
        $rows[] = ['Available Hours',      ($this->doctor['available_hours_start'] ?? '-') . ' – ' . ($this->doctor['available_hours_end'] ?? '-')];
        $rows[] = ['Member Since',         $this->doctor['created_at'] ?? '-'];
        if (!empty($this->doctor['suspension_reason'])) {
            $rows[] = ['Suspension Reason', $this->doctor['suspension_reason']];
            $rows[] = ['Suspended At',      $this->doctor['suspended_at'] ?? '-'];
        }
        $rows[] = [];

        $rows[] = ['ACCOUNT DETAILS'];
        $rows[] = ['Field', 'Value'];
        if (!empty($this->doctor['user'])) {
            $rows[] = ['Username', $this->doctor['user']['name'] ?? '-'];
            $rows[] = ['Email',    $this->doctor['user']['email'] ?? '-'];
            $rows[] = ['Phone',    $this->doctor['user']['phone'] ?? '-'];
        }
        $rows[] = [];

        $rows[] = ['KEY PERFORMANCE INDICATORS'];
        $rows[] = ['Metric', 'Value'];
        $rows[] = ['Bookings This Period',       $this->kpis['bookings_this_month'] ?? 0];
        $rows[] = ['Bookings Today',             $this->kpis['bookings_today'] ?? 0];
        $rows[] = ['Unique Patients',            $this->kpis['unique_patients'] ?? 0];
        $rows[] = ['Total Prescriptions',        $this->kpis['total_prescriptions'] ?? 0];
        $rows[] = ['Purchased Prescriptions',    $this->kpis['purchased_prescriptions'] ?? 0];
        $rows[] = ['Prescription Purchase Rate', ($this->kpis['prescription_purchase_rate'] ?? 0) . '%'];
        $rows[] = ['Completed Revenue',          'EGP ' . number_format($this->kpis['completed_revenue'] ?? 0, 2)];
        $rows[] = ['Average Rating',             $this->kpis['avg_rating'] ?? 0];
        $rows[] = ['Total Ratings',              $this->kpis['total_ratings'] ?? 0];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1  => ['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1E40AF']]],
            5  => ['font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF1E40AF']]],
            6  => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            28 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF1E40AF']]],
            29 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
            34 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF1E40AF']]],
            35 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFdbeafe']]],
        ];
    }
}
