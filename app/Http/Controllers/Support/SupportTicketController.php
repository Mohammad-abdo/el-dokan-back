<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of tickets
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->supportTickets()->with('messages');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-' . str_pad(SupportTicket::count() + 1, 6, '0', STR_PAD_LEFT),
            'user_id' => $request->user()->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    /**
     * Display the specified ticket
     */
    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $ticket->load(['messages', 'assignedTo']);

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Update the specified ticket
     */
    public function update(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully',
            'data' => $ticket
        ]);
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(SupportTicket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket deleted successfully'
        ]);
    }

    /**
     * Add message to ticket
     */
    public function addMessage(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id && $ticket->assigned_to !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $supportMessage = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        // Update ticket status if user replied
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'open']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message added successfully',
            'data' => $supportMessage
        ], 201);
    }
}
