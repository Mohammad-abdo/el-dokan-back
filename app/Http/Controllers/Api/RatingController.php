<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Display a listing of ratings
     */
    public function index(Request $request): JsonResponse
    {
        $query = Rating::where('is_approved', true)->with('user');

        if ($request->has('rateable_type') && $request->has('rateable_id')) {
            $query->where('rateable_type', $request->rateable_type)
                  ->where('rateable_id', $request->rateable_id);
        }

        $ratings = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $ratings
        ]);
    }

    /**
     * Store a newly created rating
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rateable_type' => 'required|string|in:App\Models\Product,App\Models\Shop,App\Models\Doctor,App\Models\Driver',
            'rateable_id' => 'required|integer',
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $rating = Rating::create([
            'user_id' => $request->user()->id,
            'rateable_type' => $request->rateable_type,
            'rateable_id' => $request->rateable_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false, // Requires admin approval
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully. Waiting for approval.',
            'data' => $rating
        ], 201);
    }
}
