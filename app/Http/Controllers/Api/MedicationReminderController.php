<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicationReminder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MedicationReminderController extends Controller
{
    /**
     * Display a listing of reminders
     */
    public function index(Request $request): JsonResponse
    {
        $reminders = $request->user()->medicationReminders()
            ->where('is_active', true)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reminders
        ]);
    }

    /**
     * Store a newly created reminder
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'medication_name' => 'required|string|max:255',
            'prescription_medication_id' => 'nullable|exists:prescription_medications,id',
            'reminder_time' => 'required|date_format:H:i',
            'time_period' => 'required|in:am,pm',
            'frequency' => 'required|in:twice_daily,three_times_daily,daily,specific_days',
            'specific_days' => 'nullable|array',
            'duration' => 'required|in:week,two_weeks,three_weeks,month,specific_period',
            'duration_days' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $reminder = $request->user()->medicationReminders()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reminder created successfully',
            'data' => $reminder
        ], 201);
    }

    /**
     * Display the specified reminder
     */
    public function show(Request $request, MedicationReminder $reminder): JsonResponse
    {
        if ($reminder->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $reminder
        ]);
    }

    /**
     * Update the specified reminder
     */
    public function update(Request $request, MedicationReminder $reminder): JsonResponse
    {
        if ($reminder->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'medication_name' => 'sometimes|string|max:255',
            'reminder_time' => 'sometimes|date_format:H:i',
            'time_period' => 'sometimes|in:am,pm',
            'frequency' => 'sometimes|in:twice_daily,three_times_daily,daily,specific_days',
            'specific_days' => 'nullable|array',
            'duration' => 'sometimes|in:week,two_weeks,three_weeks,month,specific_period',
            'duration_days' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $reminder->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reminder updated successfully',
            'data' => $reminder
        ]);
    }

    /**
     * Remove the specified reminder
     */
    public function destroy(MedicationReminder $reminder): JsonResponse
    {
        $reminder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reminder deleted successfully'
        ]);
    }

    /**
     * Toggle reminder active status
     */
    public function toggle(Request $request, MedicationReminder $reminder): JsonResponse
    {
        if ($reminder->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $reminder->update(['is_active' => !$reminder->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Reminder ' . ($reminder->is_active ? 'activated' : 'deactivated'),
            'data' => $reminder
        ]);
    }
}
