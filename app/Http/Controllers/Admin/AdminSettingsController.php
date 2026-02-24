<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    /**
     * Get all settings
     */
    public function index(): JsonResponse
    {
        try {
            // Check if settings table exists
            if (!DB::getSchemaBuilder()->hasTable('settings')) {
                // Return default settings if table doesn't exist
                $defaultSettings = [
                    'site_name' => 'Eldokan',
                    'site_name_ar' => 'الدكان',
                    'site_logo' => null,
                    'site_favicon' => null,
                    'primary_color' => '#3b82f6',
                    'theme' => 'light',
                    'language' => 'ar',
                    'currency' => 'EGP',
                    'currency_symbol' => 'EGP',
                    'maintenance_mode' => false,
                    'maintenance_message' => 'We are currently under maintenance. Please check back later.',
                ];

                return response()->json([
                    'success' => true,
                    'data' => $defaultSettings
                ]);
            }

            $settings = DB::table('settings')->pluck('value', 'key')->toArray();

            // Default settings if table is empty
            $defaultSettings = [
                'site_name' => 'Eldokan',
                'site_name_ar' => 'الدكان',
                'site_logo' => null,
                'site_favicon' => null,
                'primary_color' => '#3b82f6',
                'theme' => 'light',
                'language' => 'ar',
                'currency' => 'EGP',
                'currency_symbol' => 'EGP',
                'maintenance_mode' => false,
                'maintenance_message' => 'We are currently under maintenance. Please check back later.',
            ];

            $mergedSettings = array_merge($defaultSettings, $settings);

            return response()->json([
                'success' => true,
                'data' => $mergedSettings
            ]);
        } catch (\Exception $e) {
            // Return default settings on error
            return response()->json([
                'success' => true,
                'data' => [
                    'site_name' => 'Eldokan',
                    'site_name_ar' => 'الدكان',
                    'site_logo' => null,
                    'site_favicon' => null,
                    'primary_color' => '#3b82f6',
                    'theme' => 'light',
                    'language' => 'ar',
                    'currency' => 'EGP',
                    'currency_symbol' => 'EGP',
                    'maintenance_mode' => false,
                    'maintenance_message' => 'We are currently under maintenance. Please check back later.',
                ]
            ]);
        }
    }

    /**
     * Update settings
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'sometimes|string|max:255',
            'site_name_ar' => 'sometimes|string|max:255',
            'site_logo' => 'nullable|string',
            'site_favicon' => 'nullable|string',
            'primary_color' => 'sometimes|string|max:7',
            'theme' => 'sometimes|string|in:light,dark',
            'language' => 'sometimes|string|in:ar,en',
            'currency' => 'sometimes|string|max:10',
            'currency_symbol' => 'sometimes|string|max:10',
            'maintenance_mode' => 'sometimes|boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if settings table exists
            if (!DB::getSchemaBuilder()->hasTable('settings')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Settings table does not exist. Please run migrations.'
                ], 500);
            }

            DB::beginTransaction();

            foreach ($request->all() as $key => $value) {
                $valueToStore = is_array($value) || is_object($value) ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : $value);
                
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => $valueToStore,
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())')
                    ]
                );
            }

            DB::commit();

            // Get updated settings
            $settings = DB::table('settings')->pluck('value', 'key')->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload logo or favicon
     */
    public function uploadFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            'type' => 'required|string|in:logo,favicon',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->type;
            $filename = $type . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('settings', $filename, 'public');

            $url = Storage::url($path);
            $fullUrl = url($url);

            // Update setting
            $key = $type === 'logo' ? 'site_logo' : 'site_favicon';
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $fullUrl, 'updated_at' => now()]
            );

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'url' => $fullUrl,
                    'key' => $key
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

