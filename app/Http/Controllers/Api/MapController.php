<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalCenter;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MapController extends Controller
{
    /**
     * Get medical centers
     */
    public function medicalCenters(Request $request): JsonResponse
    {
        $query = MedicalCenter::where('is_active', true);

        // Filter by location if provided
        if ($request->has('latitude') && $request->has('longitude')) {
            // TODO: Implement distance-based filtering
        }

        $centers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $centers
        ]);
    }

    /**
     * Get doctor clinics
     */
    public function doctorClinics(Request $request, Doctor $doctor): JsonResponse
    {
        $clinics = $doctor->medicalCenters()->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $clinics
        ]);
    }

    /**
     * Calculate distance
     */
    public function calculateDistance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from_latitude' => 'required|numeric',
            'from_longitude' => 'required|numeric',
            'to_latitude' => 'required|numeric',
            'to_longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Calculate distance using Haversine formula
        $earthRadius = 6371; // km
        
        $latFrom = deg2rad($request->from_latitude);
        $lonFrom = deg2rad($request->from_longitude);
        $latTo = deg2rad($request->to_latitude);
        $lonTo = deg2rad($request->to_longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        $distance = $earthRadius * $c; // in km
        $estimatedTime = round($distance * 2); // Rough estimate: 2 minutes per km

        return response()->json([
            'success' => true,
            'data' => [
                'distance_km' => round($distance, 2),
                'estimated_time_minutes' => $estimatedTime,
            ]
        ]);
    }
}
