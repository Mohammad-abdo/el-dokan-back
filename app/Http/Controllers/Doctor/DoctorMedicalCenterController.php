<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicalCenter;
use App\Models\DoctorMedicalCenter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DoctorMedicalCenterController extends Controller
{
    /**
     * Get doctor medical centers
     */
    public function index(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        $centers = $doctor->medicalCenters()->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $centers
        ]);
    }

    /**
     * Add medical center to doctor
     */
    public function add(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'medical_center_id' => 'required|exists:medical_centers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }

        DoctorMedicalCenter::firstOrCreate([
            'doctor_id' => $doctor->id,
            'medical_center_id' => $request->medical_center_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Medical center added successfully'
        ]);
    }

    /**
     * Remove medical center from doctor
     */
    public function remove(MedicalCenter $medicalCenter): JsonResponse
    {
        $doctor = request()->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        DoctorMedicalCenter::where('doctor_id', $doctor->id)
            ->where('medical_center_id', $medicalCenter->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medical center removed successfully'
        ]);
    }
}
