<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPrescription;
use App\Models\DoctorPrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DoctorPrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions
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
        
        $query = DoctorPrescription::where('doctor_id', $doctor->id)->with(['patient', 'items']);
        if ($request->boolean('is_template')) {
            $query->where('is_template', true);
        }
        $prescriptions = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $prescriptions
        ]);
    }

    /**
     * Store a newly created prescription
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prescription_name' => 'required|string|max:255',
            'patient_name' => 'required|string|max:255',
            'patient_id' => 'nullable|exists:users,id',
            'patient_phone' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medication_name' => 'required|string',
            'items.*.dosage' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.status' => 'nullable|string|in:pending,in_cart,completed',
            'items.*.duration_days' => 'nullable|integer|min:1',
            'items.*.instructions' => 'nullable|string',
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

        $prescription = DoctorPrescription::create([
            'prescription_number' => 'RX-' . str_pad(DoctorPrescription::count() + 1, 6, '0', STR_PAD_LEFT),
            'doctor_id' => $doctor->id,
            'patient_id' => $request->patient_id,
            'prescription_name' => $request->prescription_name,
            'patient_name' => $request->patient_name,
            'patient_phone' => $request->patient_phone,
            'notes' => $request->notes,
            'is_template' => $request->boolean('is_template', false),
        ]);

        // Create prescription items
        foreach ($request->items as $item) {
            DoctorPrescriptionItem::create([
                'doctor_prescription_id' => $prescription->id,
                'medication_name' => $item['medication_name'],
                'dosage' => $item['dosage'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['price'] ?? 0,
                'status' => $item['status'] ?? 'pending',
                'duration_days' => $item['duration_days'] ?? null,
                'instructions' => $item['instructions'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prescription created successfully',
            'data' => $prescription->load(['patient', 'items'])
        ], 201);
    }

    /**
     * List templates only (وصفات مكررة)
     */
    public function templates(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found'], 404);
        }
        $templates = DoctorPrescription::where('doctor_id', $doctor->id)
            ->where('is_template', true)
            ->with('items')
            ->latest()
            ->get();
        return response()->json(['success' => true, 'data' => $templates]);
    }

    /**
     * Display the specified prescription
     */
    public function show(Request $request, DoctorPrescription $prescription): JsonResponse
    {
        if ($request->user()->doctor && (int) $prescription->doctor_id !== (int) $request->user()->doctor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $prescription->load(['patient', 'items', 'doctor']);

        return response()->json([
            'success' => true,
            'data' => $prescription
        ]);
    }

    /**
     * Update the specified prescription
     */
    public function update(Request $request, DoctorPrescription $prescription): JsonResponse
    {
        if ($request->user()->doctor && (int) $prescription->doctor_id !== (int) $request->user()->doctor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $validator = Validator::make($request->all(), [
            'prescription_name' => 'sometimes|string|max:255',
            'patient_name' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $prescription->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Prescription updated successfully',
            'data' => $prescription->load(['patient', 'items'])
        ]);
    }

    /**
     * Remove the specified prescription
     */
    public function destroy(Request $request, DoctorPrescription $prescription): JsonResponse
    {
        if ($request->user()->doctor && (int) $prescription->doctor_id !== (int) $request->user()->doctor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $prescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prescription deleted successfully'
        ]);
    }

    /**
     * Share prescription
     */
    public function share(Request $request, DoctorPrescription $prescription): JsonResponse
    {
        if (!$prescription->share_link) {
            $prescription->update([
                'share_link' => Str::random(32),
                'is_shared' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prescription share link generated',
            'data' => [
                'share_link' => $prescription->share_link,
                'share_url' => config('app.url') . '/prescriptions/link/' . $prescription->share_link,
            ]
        ]);
    }

    /**
     * View prescription by link
     */
    public function viewByLink(string $link): JsonResponse
    {
        $prescription = DoctorPrescription::where('share_link', $link)
            ->where('is_shared', true)
            ->with(['doctor', 'items'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $prescription
        ]);
    }

    /**
     * Print prescription
     */
    public function print(DoctorPrescription $prescription): JsonResponse
    {
        $prescription->load(['doctor', 'patient', 'items']);

        // TODO: Generate PDF using DomPDF
        return response()->json([
            'success' => true,
            'message' => 'Prescription print data',
            'data' => $prescription
        ]);
    }
}
