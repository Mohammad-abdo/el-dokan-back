<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DoctorPrescription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorDashboardController extends Controller
{
    /**
     * Get doctor dashboard (نسبة شراء الوصفات، مواعيد اليوم، عدد المرضى)
     */
    public function dashboard(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth();

        $totalPrescriptions = DoctorPrescription::where('doctor_id', $doctor->id)->where('is_template', false)->count();
        $prescriptionsWithCompletedItems = DoctorPrescription::where('doctor_id', $doctor->id)
            ->where('is_template', false)
            ->whereHas('items', fn ($q) => $q->where('status', 'completed'))
            ->count();
        $prescription_purchase_rate = $totalPrescriptions > 0
            ? round(($prescriptionsWithCompletedItems / $totalPrescriptions) * 100, 1)
            : 0;

        $patientsByMonth = Booking::where('doctor_id', $doctor->id)
            ->selectRaw('YEAR(appointment_date) as year, MONTH(appointment_date) as month, COUNT(DISTINCT user_id) as count')
            ->where('appointment_date', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'year' => (int) $r->year,
                'month' => (int) $r->month,
                'count' => (int) $r->count,
                'label' => date('M Y', mktime(0, 0, 0, $r->month, 1, $r->year)),
            ])
            ->values()
            ->toArray();

        $data = [
            'today_bookings' => Booking::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $today)
                ->count(),
            'today_appointments' => Booking::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $today)
                ->count(),
            'this_month_bookings' => Booking::where('doctor_id', $doctor->id)
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'total_prescriptions' => $totalPrescriptions,
            'prescription_purchase_rate' => $prescription_purchase_rate,
            'patients_by_month' => $patientsByMonth,
            'wallet_balance' => $doctor->wallet->balance ?? 0,
            'rating' => $doctor->rating,
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get doctor bookings
     */
    public function bookings(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        $query = Booking::where('doctor_id', $doctor->id)
            ->with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Get doctor patients
     */
    public function patients(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        $patients = Booking::where('doctor_id', $doctor->id)
            ->with('user')
            ->select('user_id')
            ->distinct()
            ->get()
            ->pluck('user');

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }
}
