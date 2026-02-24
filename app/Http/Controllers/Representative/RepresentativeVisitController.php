<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RepresentativeVisitController extends Controller
{
    /**
     * Display a listing of visits
     */
    public function index(Request $request): JsonResponse
    {
        $representative = $request->user()->representative;
        
        if (!$representative) {
            return response()->json([
                'success' => false,
                'message' => 'Representative profile not found'
            ], 404);
        }
        
        $visits = Visit::where('representative_id', $representative->id)
            ->with(['shop', 'doctor'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $visits
        ]);
    }

    /**
     * Store a newly created visit
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'nullable|exists:shops,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'visit_date' => 'required|date|after_or_equal:today',
            'visit_time' => 'required|date_format:H:i',
            'purpose' => 'required|string|max:1000',
            'notes' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $representative = $request->user()->representative;
        
        if (!$representative) {
            return response()->json([
                'success' => false,
                'message' => 'Representative profile not found'
            ], 404);
        }

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('visits', 'public');
                $filePaths[] = Storage::url($path);
            }
        }

        $visit = Visit::create([
            'representative_id' => $representative->id,
            'shop_id' => $request->shop_id,
            'doctor_id' => $request->doctor_id,
            'visit_date' => $request->visit_date,
            'visit_time' => $request->visit_time,
            'purpose' => $request->purpose,
            'notes' => $request->notes,
            'files' => $filePaths,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visit request created successfully',
            'data' => $visit->load(['shop', 'doctor'])
        ], 201);
    }

    /**
     * Display the specified visit
     */
    public function show(Visit $visit): JsonResponse
    {
        $visit->load(['shop', 'doctor', 'representative']);

        return response()->json([
            'success' => true,
            'data' => $visit
        ]);
    }

    /**
     * Update the specified visit
     */
    public function update(Request $request, Visit $visit): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visit_date' => 'sometimes|date',
            'visit_time' => 'sometimes|date_format:H:i',
            'purpose' => 'sometimes|string|max:1000',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $visit->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Visit updated successfully',
            'data' => $visit->load(['shop', 'doctor'])
        ]);
    }

    /**
     * Remove the specified visit
     */
    public function destroy(Visit $visit): JsonResponse
    {
        $visit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Visit deleted successfully'
        ]);
    }

    /**
     * Approve visit (Admin only)
     */
    public function approve(Visit $visit): JsonResponse
    {
        $visit->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visit approved successfully',
            'data' => $visit
        ]);
    }

    /**
     * Reject visit (Admin only)
     */
    public function reject(Request $request, Visit $visit): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $visit->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visit rejected',
            'data' => $visit
        ]);
    }
}
