<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminDriverController extends Controller
{
    /**
     * Display a listing of drivers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Driver::with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $drivers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $drivers
        ]);
    }

    /**
     * Store a newly created driver
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:drivers,phone',
            'photo_url' => 'nullable|string|url',
            'status' => 'sometimes|in:available,busy,offline',
            'current_location_lat' => 'nullable|numeric',
            'current_location_lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver = Driver::create($request->only([
            'user_id',
            'name',
            'phone',
            'photo_url',
            'status',
            'current_location_lat',
            'current_location_lng',
        ]));

        // Update user role if user_id is provided
        if ($request->has('user_id') && $request->user_id) {
            $user = \App\Models\User::find($request->user_id);
            if ($user) {
                if (!$user->hasRole('driver')) {
                    $user->assignRole('driver');
                }
                $user->update(['role' => 'driver']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Driver created successfully',
            'data' => $driver
        ], 201);
    }

    /**
     * Display the specified driver
     */
    public function show(Driver $driver): JsonResponse
    {
        $driver->load(['user', 'deliveries']);

        return response()->json([
            'success' => true,
            'data' => $driver
        ]);
    }

    /**
     * Update the specified driver
     */
    public function update(Request $request, Driver $driver): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:drivers,phone,' . $driver->id,
            'photo_url' => 'nullable|string|url',
            'status' => 'sometimes|in:available,busy,offline',
            'current_location_lat' => 'nullable|numeric',
            'current_location_lng' => 'nullable|numeric',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver->update($request->only([
            'user_id',
            'name',
            'phone',
            'photo_url',
            'status',
            'current_location_lat',
            'current_location_lng',
            'rating',
        ]));

        // Update user role if user_id is provided
        if ($request->has('user_id') && $request->user_id) {
            $user = \App\Models\User::find($request->user_id);
            if ($user) {
                if (!$user->hasRole('driver')) {
                    $user->assignRole('driver');
                }
                $user->update(['role' => 'driver']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Driver updated successfully',
            'data' => $driver
        ]);
    }

    /**
     * Remove the specified driver
     */
    public function destroy(Driver $driver): JsonResponse
    {
        // Check if driver has active deliveries
        $activeDeliveries = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit'])
            ->count();

        if ($activeDeliveries > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete driver with active deliveries'
            ], 400);
        }

        $driver->delete();

        return response()->json([
            'success' => true,
            'message' => 'Driver deleted successfully'
        ]);
    }

    /**
     * Get driver deliveries
     */
    public function deliveries(Driver $driver): JsonResponse
    {
        $deliveries = Delivery::where('driver_id', $driver->id)
            ->with('order')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $deliveries
        ]);
    }
}


