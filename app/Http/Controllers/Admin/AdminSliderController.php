<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminSliderController extends Controller
{
    /**
     * Display a listing of sliders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Slider::query();

        // Filter by vendor type
        if ($request->has('vendor_type')) {
            $query->where('vendor_type', $request->vendor_type);
        }

        // Filter by vendor_id
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $sliders = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $sliders
        ]);
    }

    /**
     * Store a newly created slider
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['link_url'] = trim((string) ($data['link_url'] ?? '')) ?: null;
        $data['link_type'] = trim((string) ($data['link_type'] ?? '')) ?: null;
        $data['link_id'] = isset($data['link_id']) && $data['link_id'] !== '' ? (int) $data['link_id'] : null;
        $data['vendor_id'] = isset($data['vendor_id']) && $data['vendor_id'] !== '' ? (int) $data['vendor_id'] : null;
        $data['start_date'] = trim((string) ($data['start_date'] ?? '')) ?: null;
        $data['end_date'] = trim((string) ($data['end_date'] ?? '')) ?: null;

        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'required|string',
            'link_type' => 'nullable|in:product,shop,doctor,booking,driver,representative',
            'link_id' => 'nullable|integer',
            'link_url' => 'nullable|url',
            'vendor_type' => 'nullable|in:shop,doctor,driver,representative,general',
            'vendor_id' => 'nullable|integer',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'start_date' => 'nullable|date',
            'end_date' => ['nullable', 'date', Rule::when(!empty($data['start_date']), 'after_or_equal:start_date')],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $slider = Slider::create(collect($data)->only([
            'title', 'description', 'image_url', 'link_type', 'link_id', 'link_url',
            'vendor_type', 'vendor_id', 'order', 'is_active', 'start_date', 'end_date',
        ])->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Slider created successfully',
            'data' => $slider
        ], 201);
    }

    /**
     * Display the specified slider
     */
    public function show(Slider $slider): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $slider
        ]);
    }

    /**
     * Update the specified slider
     */
    public function update(Request $request, Slider $slider): JsonResponse
    {
        $data = $request->all();
        $data['link_url'] = trim((string) ($data['link_url'] ?? '')) ?: null;
        $data['link_type'] = trim((string) ($data['link_type'] ?? '')) ?: null;
        $data['link_id'] = isset($data['link_id']) && $data['link_id'] !== '' ? (int) $data['link_id'] : null;
        $data['vendor_id'] = isset($data['vendor_id']) && $data['vendor_id'] !== '' ? (int) $data['vendor_id'] : null;
        $data['start_date'] = trim((string) ($data['start_date'] ?? '')) ?: null;
        $data['end_date'] = trim((string) ($data['end_date'] ?? '')) ?: null;

        $validator = Validator::make($data, [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'sometimes|string',
            'link_type' => 'nullable|in:product,shop,doctor,booking,driver,representative',
            'link_id' => 'nullable|integer',
            'link_url' => 'nullable|url',
            'vendor_type' => 'nullable|in:shop,doctor,driver,representative,general',
            'vendor_id' => 'nullable|integer',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'start_date' => 'nullable|date',
            'end_date' => ['nullable', 'date', Rule::when(!empty($data['start_date']), 'after_or_equal:start_date')],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $slider->update(collect($data)->only([
            'title', 'description', 'image_url', 'link_type', 'link_id', 'link_url',
            'vendor_type', 'vendor_id', 'order', 'is_active', 'start_date', 'end_date',
        ])->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Slider updated successfully',
            'data' => $slider
        ]);
    }

    /**
     * Remove the specified slider
     */
    public function destroy(Slider $slider): JsonResponse
    {
        $slider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slider deleted successfully'
        ]);
    }
}
