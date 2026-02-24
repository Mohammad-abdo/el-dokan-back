<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminRatingController extends Controller
{
    /**
     * Display a listing of ratings
     */
    public function index(Request $request): JsonResponse
    {
        $query = Rating::with(['user', 'rateable']);

        if ($request->has('is_approved')) {
            $query->where('is_approved', $request->is_approved);
        }

        if ($request->has('rateable_type')) {
            $query->where('rateable_type', $request->rateable_type);
        }

        $ratings = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $ratings
        ]);
    }

    /**
     * Approve rating
     */
    public function approve(Rating $rating): JsonResponse
    {
        $rating->update(['is_approved' => true]);

        // Update rateable average rating
        $this->updateRateableRating($rating);

        return response()->json([
            'success' => true,
            'message' => 'Rating approved successfully',
            'data' => $rating
        ]);
    }

    /**
     * Reject rating
     */
    public function reject(Rating $rating): JsonResponse
    {
        $rating->update(['is_approved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Rating rejected',
            'data' => $rating
        ]);
    }

    /**
     * Remove the specified rating
     */
    public function destroy(Rating $rating): JsonResponse
    {
        $rating->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rating deleted successfully'
        ]);
    }

    /**
     * Update rateable average rating
     */
    private function updateRateableRating(Rating $rating): void
    {
        $rateable = $rating->rateable;
        if ($rateable) {
            $avgRating = Rating::where('rateable_type', get_class($rateable))
                ->where('rateable_id', $rateable->id)
                ->where('is_approved', true)
                ->avg('rating');
            
            $rateable->update(['rating' => round($avgRating, 2)]);
        }
    }
}
