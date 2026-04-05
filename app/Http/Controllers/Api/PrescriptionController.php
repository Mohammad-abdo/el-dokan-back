<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrescriptionController extends Controller
{
    /**
     * Upload prescription images
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'required|image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePaths = [];
        foreach ($request->file('images') as $image) {
            $hashedName = hash('sha256', uniqid('', true) . $image->getClientOriginalName()) . '.' . $image->extension();
            $path = $image->storeAs('prescriptions', $hashedName, 'public');
            $imagePaths[] = Storage::url($path);
        }

        $prescription = Prescription::create([
            'prescription_number' => 'RX-' . str_pad(Prescription::count() + 1, 6, '0', STR_PAD_LEFT),
            'user_id' => $request->user()->id,
            'images' => $imagePaths,
            'status' => 'under_review',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prescription uploaded successfully',
            'data' => $prescription
        ], 201);
    }

    /**
     * Display a listing of prescriptions
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->prescriptions()->with(['pharmacy', 'pharmacist']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $prescriptions = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $prescriptions
        ]);
    }

    /**
     * Display the specified prescription
     */
    public function show(Request $request, Prescription $prescription): JsonResponse
    {
        if ($prescription->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $prescription->load(['pharmacy', 'pharmacist', 'medications']);

        return response()->json([
            'success' => true,
            'data' => $prescription
        ]);
    }
}
