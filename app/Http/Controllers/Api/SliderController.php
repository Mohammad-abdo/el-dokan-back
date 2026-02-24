<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SliderController extends Controller
{
    /**
     * Display a listing of sliders
     */
    public function index(): JsonResponse
    {
        $sliders = Slider::where('is_active', true)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sliders
        ]);
    }
}
