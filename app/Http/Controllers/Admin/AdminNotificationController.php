<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminNotificationController extends Controller
{
    /**
     * Display a listing of all notifications
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::with('user')->latest();

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read === 'true' || $request->is_read === true);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Store a newly created notification
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'action_url' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $notification = Notification::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully',
            'data' => $notification->load('user')
        ], 201);
    }

    /**
     * Display the specified notification
     */
    public function show(Notification $notification): JsonResponse
    {
        $notification->load('user');

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    /**
     * Update the specified notification
     */
    public function update(Request $request, Notification $notification): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
            'type' => 'sometimes|string|max:255',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'action_url' => 'nullable|string|max:500',
            'is_read' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $notification->update($request->all());
        $notification->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Notification updated successfully',
            'data' => $notification
        ]);
    }

    /**
     * Remove the specified notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Send notification to multiple users
     */
    public function sendBulk(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'action_url' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $notifications = [];
        foreach ($request->user_ids as $userId) {
            $notifications[] = Notification::create([
                'user_id' => $userId,
                'type' => $request->type,
                'title' => $request->title,
                'description' => $request->description,
                'action_url' => $request->action_url,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications sent successfully',
            'data' => $notifications
        ], 201);
    }

    /**
     * Get notification statistics
     */
    public function statistics(): JsonResponse
    {
        $total = Notification::count();
        $unread = Notification::where('is_read', false)->count();
        $read = Notification::where('is_read', true)->count();

        $byType = Notification::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'unread' => $unread,
                'read' => $read,
                'by_type' => $byType,
            ]
        ]);
    }
}

