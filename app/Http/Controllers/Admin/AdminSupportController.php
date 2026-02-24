<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminSupportController extends Controller
{
    /**
     * Display a listing of support tickets
     */
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with(['user', 'messages']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Display the specified support ticket
     */
    public function show(SupportTicket $ticket): JsonResponse
    {
        $ticket->load(['user', 'messages.user', 'assignedTo']);

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Assign ticket to admin
     */
    public function assign(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'in_progress',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned successfully',
            'data' => $ticket
        ]);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated successfully',
            'data' => $ticket
        ]);
    }
}
