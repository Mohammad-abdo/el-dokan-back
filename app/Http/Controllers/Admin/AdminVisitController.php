<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminVisitController extends Controller
{
    /**
     * Display a listing of all visits
     */
    public function index(Request $request): JsonResponse
    {
        $query = Visit::with(['shop', 'doctor', 'representative.user']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by representative
        if ($request->has('representative_id')) {
            $query->where('representative_id', $request->representative_id);
        }

        // Filter by shop
        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        // Filter by doctor
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        $visits = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $visits->items(),
            'pagination' => [
                'total' => $visits->total(),
                'per_page' => $visits->perPage(),
                'current_page' => $visits->currentPage(),
                'last_page' => $visits->lastPage(),
            ],
        ]);
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
     * Approve visit
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
            'data' => $visit->load(['shop', 'doctor', 'representative'])
        ]);
    }

    /**
     * Reject visit
     */
    public function reject(Request $request, Visit $visit): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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
            'message' => 'Visit rejected successfully',
            'data' => $visit->load(['shop', 'doctor', 'representative'])
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
}
