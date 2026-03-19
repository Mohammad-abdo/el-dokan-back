<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorPrescription;
use App\Models\DoctorWalletTransaction;
use App\Models\Rating;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class DoctorReportService
{
    private const ALLOWED_SECTIONS = [
        'schedule',
        'prescriptions',
        'bookings',
        'patients',
        'wallet',
        'visits',
        'treatments',
        'medical_centers',
        'ratings',
    ];

    public function generateFullReport(Doctor $doctor, ?string $from, ?string $to): array
    {
        return $this->buildReport($doctor, self::ALLOWED_SECTIONS, $from, $to);
    }

    public function generateCustomReport(Doctor $doctor, array $sections, ?string $from, ?string $to): array
    {
        $sections = array_intersect($sections, self::ALLOWED_SECTIONS);
        return $this->buildReport($doctor, $sections, $from, $to);
    }

    private function buildReport(Doctor $doctor, array $sections, ?string $from, ?string $to): array
    {
        $dateFrom = $from ?? now()->startOfMonth()->toDateString();
        $dateTo   = $to   ?? now()->toDateString();

        $report = [
            'doctor'       => $this->getDoctorInfo($doctor),
            'kpis'         => $this->getDashboardKPIs($doctor, $dateFrom, $dateTo),
            'period'       => ['from' => $dateFrom, 'to' => $dateTo],
            'sections'     => [],
            'generated_at' => now()->toDateTimeString(),
        ];

        foreach ($sections as $section) {
            $report['sections'][$section] = match ($section) {
                'schedule'        => $this->getSchedule($doctor),
                'prescriptions'   => $this->getPrescriptionsSummary($doctor, $dateFrom, $dateTo),
                'bookings'        => $this->getBookingsSummary($doctor, $dateFrom, $dateTo),
                'patients'        => $this->getPatientsList($doctor, $dateFrom, $dateTo),
                'wallet'          => $this->getWalletData($doctor),
                'visits'          => $this->getVisits($doctor, $dateFrom, $dateTo),
                'treatments'      => $this->getTreatments($doctor),
                'medical_centers' => $this->getMedicalCenters($doctor),
                'ratings'         => $this->getRatings($doctor),
                default           => null,
            };
        }

        return $report;
    }

    public function getDoctorInfo(Doctor $doctor): array
    {
        $doctor->loadMissing('user');

        return [
            'id'                    => $doctor->id,
            'name'                  => $doctor->name,
            'name_ar'               => $doctor->name_ar ?? null,
            'name_en'               => $doctor->name_en ?? null,
            'specialty'             => $doctor->specialty,
            'specialty_ar'          => $doctor->specialty_ar ?? null,
            'specialty_en'          => $doctor->specialty_en ?? null,
            'location'              => $doctor->location,
            'location_ar'           => $doctor->location_ar ?? null,
            'location_en'           => $doctor->location_en ?? null,
            'consultation_price'    => (float) $doctor->consultation_price,
            'discount_percentage'   => (float) $doctor->discount_percentage,
            'consultation_duration' => $doctor->consultation_duration,
            'rating'                => $doctor->rating,
            'status'                => $doctor->status ?? 'active',
            'is_active'             => $doctor->is_active,
            'suspension_reason'     => $doctor->suspension_reason,
            'suspended_at'          => $doctor->suspended_at?->toDateTimeString(),
            'available_days'        => $doctor->available_days ?? [],
            'available_hours_start' => $doctor->available_hours_start,
            'available_hours_end'   => $doctor->available_hours_end,
            'available_hours'       => $doctor->available_hours_start . ' - ' . $doctor->available_hours_end,
            'photo_url'             => $doctor->photo_url,
            'created_at'            => $doctor->created_at?->toDateString(),
            'user'                  => $doctor->user ? [
                'id'    => $doctor->user->id,
                'name'  => $doctor->user->username,
                'email' => $doctor->user->email,
                'phone' => $doctor->user->phone,
            ] : null,
        ];
    }

    public function getSchedule(Doctor $doctor): array
    {
        $effectivePrice = round(
            (float) $doctor->consultation_price * (1 - ((float) $doctor->discount_percentage / 100)),
            2
        );

        return [
            'available_days'        => $doctor->available_days ?? [],
            'available_hours_start' => $doctor->available_hours_start,
            'available_hours_end'   => $doctor->available_hours_end,
            'consultation_duration' => $doctor->consultation_duration,
            'consultation_price'    => (float) $doctor->consultation_price,
            'discount_percentage'   => (float) $doctor->discount_percentage,
            'effective_price'       => $effectivePrice,
        ];
    }

    public function getDashboardKPIs(Doctor $doctor, string $from, string $to): array
    {
        $totalPrescriptions = DoctorPrescription::where('doctor_id', $doctor->id)
            ->where('is_template', false)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->count();

        $purchasedCount = DoctorPrescription::where('doctor_id', $doctor->id)
            ->where('is_template', false)
            ->where('is_shared', true)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->count();

        $prescriptionPurchaseRate = $totalPrescriptions > 0
            ? round(($purchasedCount / $totalPrescriptions) * 100, 1)
            : 0;

        $bookingsToday = $doctor->bookings()
            ->whereDate('appointment_date', today())
            ->count();

        $bookingsThisMonth = $doctor->bookings()
            ->whereBetween('appointment_date', [$from, $to])
            ->count();

        $completedRevenue = $doctor->bookings()
            ->where('status', 'completed')
            ->whereBetween('appointment_date', [$from, $to])
            ->sum('total_amount');

        $avgRating      = $doctor->ratings()->avg('rating') ?? 0;
        $totalRatings   = $doctor->ratings()->count();
        $uniquePatients = $doctor->bookings()
            ->whereBetween('appointment_date', [$from, $to])
            ->distinct('user_id')
            ->count('user_id');

        return [
            'prescription_purchase_rate' => $prescriptionPurchaseRate,
            'total_prescriptions'        => $totalPrescriptions,
            'purchased_prescriptions'    => $purchasedCount,
            'bookings_today'             => $bookingsToday,
            'bookings_this_month'        => $bookingsThisMonth,
            'completed_revenue'          => (float) $completedRevenue,
            'avg_rating'                 => round((float) $avgRating, 2),
            'total_ratings'              => $totalRatings,
            'unique_patients'            => $uniquePatients,
        ];
    }

    public function getPrescriptionsSummary(Doctor $doctor, string $from, string $to): array
    {
        $query = DoctorPrescription::where('doctor_id', $doctor->id)
            ->where('is_template', false)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59']);

        $byShared = (clone $query)
            ->select('is_shared', DB::raw('count(*) as count'))
            ->groupBy('is_shared')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->is_shared ? 'shared' : 'not_shared' => $row->count]);

        $recent = (clone $query)
            ->with([
                'patient:id,username,phone',
                'items:id,doctor_prescription_id,medication_name,dosage,quantity,price,status,duration_days,instructions,notes',
            ])
            ->latest()
            ->get([
                'id', 'prescription_number', 'prescription_name',
                'patient_id', 'patient_name', 'patient_phone',
                'notes', 'is_shared', 'created_at',
            ]);

        return [
            'total'           => (clone $query)->count(),
            'shared'          => $byShared['shared'] ?? 0,
            'not_shared'      => $byShared['not_shared'] ?? 0,
            'templates_count' => DoctorPrescription::where('doctor_id', $doctor->id)->where('is_template', true)->count(),
            'recent'          => $recent->toArray(),
        ];
    }

    public function getBookingsSummary(Doctor $doctor, string $from, string $to): array
    {
        $query = $doctor->bookings()->whereBetween('appointment_date', [$from, $to]);

        $byStatus = (clone $query)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->status => ['count' => $r->count, 'total' => (float) $r->total]]);

        $byType = (clone $query)
            ->select('booking_type', DB::raw('count(*) as count'))
            ->groupBy('booking_type')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->booking_type => $r->count]);

        $byPaymentMethod = (clone $query)
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->payment_method => ['count' => $r->count, 'total' => (float) $r->total]]);

        $recent = (clone $query)
            ->with('user:id,username,phone,email')
            ->latest('appointment_date')
            ->get([
                'id', 'booking_number', 'user_id', 'patient_name',
                'appointment_date', 'appointment_time', 'status', 'booking_type',
                'total_amount', 'payment_method', 'payment_status',
                'rating', 'complaint',
            ]);

        return [
            'total'             => (clone $query)->count(),
            'total_revenue'     => (float) (clone $query)->where('status', 'completed')->sum('total_amount'),
            'avg_booking_value' => round((float) ((clone $query)->avg('total_amount') ?? 0), 2),
            'by_status'         => $byStatus,
            'by_type'           => $byType,
            'by_payment_method' => $byPaymentMethod,
            'recent'            => $recent->toArray(),
        ];
    }

    public function getPatientsList(Doctor $doctor, string $from, string $to): array
    {
        $patients = $doctor->bookings()
            ->whereBetween('appointment_date', [$from, $to])
            ->with('user:id,username,phone,email')
            ->select(
                'user_id',
                'patient_name',
                DB::raw('count(*) as visits_count'),
                DB::raw('sum(total_amount) as total_spent'),
                DB::raw('max(appointment_date) as last_visit'),
                DB::raw('min(appointment_date) as first_visit')
            )
            ->groupBy('user_id', 'patient_name')
            ->orderByDesc('last_visit')
            ->get();

        return [
            'total'    => $patients->count(),
            'patients' => $patients->toArray(),
        ];
    }

    public function getWalletData(Doctor $doctor): array
    {
        $wallet = $doctor->wallet ?? $doctor->wallet()->firstOrCreate(
            ['doctor_id' => $doctor->id],
            ['balance' => 0, 'commission_rate' => 15]
        );

        // Refresh to ensure all DB columns (pending_balance, total_earnings) are loaded
        $wallet->refresh();

        $transactions = DoctorWalletTransaction::where('doctor_id', $doctor->id)
            ->latest()
            ->get(['id', 'type', 'amount', 'description', 'status', 'booking_id', 'created_at']);

        $byType = $transactions->groupBy('type')->map(fn ($group) => [
            'count' => $group->count(),
            'total' => (float) $group->sum('amount'),
        ]);

        return [
            'balance'            => (float) $wallet->balance,
            'pending_balance'    => (float) ($wallet->pending_balance ?? 0),
            'total_earnings'     => (float) ($wallet->total_earnings ?? 0),
            'commission_rate'    => (float) $wallet->commission_rate,
            'transactions_count' => $transactions->count(),
            'by_type'            => $byType,
            'transactions'       => $transactions->toArray(),
        ];
    }

    public function getVisits(Doctor $doctor, string $from, string $to): array
    {
        $visits = Visit::where('doctor_id', $doctor->id)
            ->whereBetween('visit_date', [$from, $to])
            ->with('representative:id,user_id', 'representative.user:id,username,phone')
            ->select('id', 'representative_id', 'shop_id', 'visit_date', 'visit_time', 'purpose', 'notes', 'status', 'rejection_reason', 'doctor_confirmed_at')
            ->latest('visit_date')
            ->get();

        $byStatus = $visits->groupBy('status')->map->count();

        return [
            'total'     => $visits->count(),
            'by_status' => $byStatus,
            'visits'    => $visits->toArray(),
        ];
    }

    public function getMedicalCenters(Doctor $doctor): array
    {
        $doctor->loadMissing('medicalCenters', 'primaryMedicalCenter');

        return [
            'total'   => $doctor->medicalCenters->count(),
            'primary' => $doctor->primaryMedicalCenter?->toArray(),
            'centers' => $doctor->medicalCenters->toArray(),
        ];
    }

    public function getTreatments(Doctor $doctor): array
    {
        $doctor->loadMissing('selectedTreatments');

        return [
            'total'      => $doctor->selectedTreatments->count(),
            'treatments' => $doctor->selectedTreatments->toArray(),
        ];
    }

    public function getRatings(Doctor $doctor): array
    {
        $ratings = Rating::where('rateable_type', Doctor::class)
            ->where('rateable_id', $doctor->id)
            ->with('user:id,username,phone')
            ->latest()
            ->get(['id', 'user_id', 'rating', 'comment', 'is_approved', 'created_at']);

        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[(string) $i] = $ratings->where('rating', $i)->count();
        }

        return [
            'total'        => $ratings->count(),
            'average'      => round((float) ($ratings->avg('rating') ?? 0), 2),
            'approved'     => $ratings->where('is_approved', true)->count(),
            'distribution' => $distribution,
            'ratings'      => $ratings->toArray(),
        ];
    }

    public function getAllowedSections(): array
    {
        return self::ALLOWED_SECTIONS;
    }
}
