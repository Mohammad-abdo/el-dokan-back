<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorSelectedTreatment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DoctorSelectedTreatmentController extends Controller
{
    /**
     * List selected treatments (العلاجات المختارة)
     */
    public function index(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found'], 404);
        }
        $items = DoctorSelectedTreatment::where('doctor_id', $doctor->id)->latest()->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    /**
     * Add selected treatment
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }
        $doctor = $request->user()->doctor;
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found'], 404);
        }
        $item = DoctorSelectedTreatment::create([
            'doctor_id' => $doctor->id,
            'name' => $request->name,
            'company' => $request->company,
        ]);
        return response()->json(['success' => true, 'message' => 'Treatment added', 'data' => $item], 201);
    }

    /**
     * Remove selected treatment
     */
    public function destroy(Request $request, DoctorSelectedTreatment $treatment): JsonResponse
    {
        $doctor = $request->user()->doctor;
        if (!$doctor || (int) $treatment->doctor_id !== (int) $doctor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $treatment->delete();
        return response()->json(['success' => true, 'message' => 'Treatment removed']);
    }
}
