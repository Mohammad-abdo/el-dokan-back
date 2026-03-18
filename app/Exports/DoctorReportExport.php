<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DoctorReportExport implements WithMultipleSheets
{
    public function __construct(private array $report) {}

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new Sheets\DoctorInfoSheet(
            $this->report['doctor'],
            $this->report['kpis'],
            $this->report['period']
        );

        foreach ($this->report['sections'] as $section => $data) {
            if ($data === null) {
                continue;
            }

            $sheet = match ($section) {
                'schedule'        => new Sheets\DoctorScheduleSheet($data),
                'prescriptions'   => new Sheets\DoctorPrescriptionsSheet($data),
                'bookings'        => new Sheets\DoctorBookingsSheet($data),
                'patients'        => new Sheets\DoctorPatientsSheet($data),
                'wallet'          => new Sheets\DoctorWalletSheet($data),
                'visits'          => new Sheets\DoctorVisitsSheet($data),
                'treatments'      => new Sheets\DoctorTreatmentsSheet($data),
                'medical_centers' => new Sheets\DoctorMedicalCentersSheet($data),
                'ratings'         => new Sheets\DoctorRatingsSheet($data),
                default           => null,
            };

            if ($sheet !== null) {
                $sheets[] = $sheet;
            }
        }

        return $sheets;
    }
}
