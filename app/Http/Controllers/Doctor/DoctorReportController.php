<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPrescription;
use App\Models\Product;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DoctorReportController extends Controller
{
    /**
     * Get prescriptions report
     */
    public function prescriptionsReport(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $prescriptions = DoctorPrescription::where('doctor_id', $doctor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'prescriptions' => $prescriptions,
                'total' => $prescriptions->sum('count'),
            ]
        ]);
    }

    /**
     * Get products report
     */
    public function productsReport(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        // Get products from prescriptions
        $products = DoctorPrescriptionItem::whereHas('prescription', function ($query) use ($doctor) {
            $query->where('doctor_id', $doctor->id);
        })
        ->select('medication_name', DB::raw('sum(quantity) as total_quantity'))
        ->groupBy('medication_name')
        ->orderBy('total_quantity', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get patients report
     */
    public function patientsReport(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $patients = Booking::where('doctor_id', $doctor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->select('user_id', DB::raw('count(*) as visit_count'))
            ->groupBy('user_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'patients' => $patients,
                'total_patients' => $patients->count(),
            ]
        ]);
    }
}
