<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors
     */
    public function index(Request $request): JsonResponse
    {
        $query = Doctor::where('is_active', true);

        // Filter by specialty
        if ($request->has('specialty')) {
            $query->where('specialty', $request->specialty);
        }

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $doctors = $query->orderBy('rating', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $doctors
        ]);
    }

    /**
     * Display the specified doctor
     */
    public function show(Doctor $doctor): JsonResponse
    {
        if (!$doctor->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        $doctor->load(['medicalCenters', 'ratings']);

        return response()->json([
            'success' => true,
            'data' => $doctor
        ]);
    }

    /**
     * Get doctor availability
     */
    public function availability(Request $request, Doctor $doctor): JsonResponse
    {
        $date = $request->get('date', now()->toDateString());
        
        // Get available time slots
        $availableDays = $doctor->available_days ?? [];
        $dayOfWeek = strtolower(now()->parse($date)->format('l'));
        
        $isAvailable = in_array($dayOfWeek, $availableDays);
        
        // Generate time slots
        $timeSlots = [];
        if ($isAvailable) {
            $start = \Carbon\Carbon::parse($doctor->available_hours_start);
            $end = \Carbon\Carbon::parse($doctor->available_hours_end);
            $duration = $doctor->consultation_duration ?? 20;
            
            while ($start->addMinutes($duration)->lte($end)) {
                $timeSlots[] = $start->format('H:i');
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'is_available' => $isAvailable,
                'available_days' => $availableDays,
                'time_slots' => $timeSlots,
                'consultation_duration' => $duration,
            ]
        ]);
    }
}
