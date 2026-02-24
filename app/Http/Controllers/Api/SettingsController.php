<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Get settings
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'language' => config('app.locale', 'ar'),
                'currency' => 'EGP',
                'app_version' => '1.0.0',
                'maintenance_mode' => false,
            ]
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'language_preference' => 'sometimes|string|in:ar,en',
            'notifications_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update($request->only(['language_preference']));

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Get support information
     */
    public function support(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'support_email' => 'support@eldokan.com',
                'support_phone' => '+20 123 456 7890',
                'support_hours' => '9:00 AM - 6:00 PM',
                'faq_url' => config('app.url') . '/faq',
            ]
        ]);
    }
}
