<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Representative;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminRepresentativeController extends Controller
{
    /**
     * Display a listing of representatives
     */
    public function index(Request $request): JsonResponse
    {
        $query = Representative::with(['user', 'shop']);

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        $representatives = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $representatives
        ]);
    }

    /**
     * Store a newly created representative
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'shop_id' => 'nullable|exists:shops,id',
            'employee_id' => 'nullable|string|max:100',
            'territory' => 'required|string|max:255',
            'status' => 'sometimes|in:pending,approved,suspended,active,pending_approval',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        if (empty($data['employee_id'])) {
            $data['employee_id'] = 'REP-' . uniqid();
        }
        $representative = Representative::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Representative created successfully',
            'data' => $representative->load('user')
        ], 201);
    }

    /**
     * Approve representative
     */
    public function approve(Representative $representative): JsonResponse
    {
        $representative->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Representative approved successfully',
            'data' => $representative
        ]);
    }

    /**
     * Suspend representative
     */
    public function suspend(Representative $representative): JsonResponse
    {
        $representative->update(['status' => 'suspended']);

        return response()->json([
            'success' => true,
            'message' => 'Representative suspended successfully',
            'data' => $representative
        ]);
    }

    /**
     * Display the specified representative
     */
    public function show(Representative $representative): JsonResponse
    {
        $representative->load(['user', 'visits']);

        return response()->json([
            'success' => true,
            'data' => $representative
        ]);
    }

    /**
     * Update the specified representative
     */
    public function update(Request $request, Representative $representative): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
            'shop_id' => 'nullable|exists:shops,id',
            'territory' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,approved,suspended,active,pending_approval',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $representative->update($request->all());

        // Update user role if user_id is provided
        if ($request->has('user_id') && $request->user_id) {
            $user = User::find($request->user_id);
            if ($user) {
                if (!$user->hasRole('representative')) {
                    $user->assignRole('representative');
                }
                $user->update(['role' => 'representative']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Representative updated successfully',
            'data' => $representative->load('user')
        ]);
    }

    /**
     * Remove the specified representative
     */
    public function destroy(Representative $representative): JsonResponse
    {
        $representative->delete();

        return response()->json([
            'success' => true,
            'message' => 'Representative deleted successfully'
        ]);
    }
}
