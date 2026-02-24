<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorChatController extends Controller
{
    /**
     * Get conversations
     */
    public function conversations(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        $doctorUserId = $request->user()->id;

        $conversations = Message::where('sender_id', $doctorUserId)
            ->orWhere('receiver_id', $doctorUserId)
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(function ($message) use ($doctorUserId) {
                return $message->sender_id === $doctorUserId 
                    ? $message->receiver_id 
                    : $message->sender_id;
            });

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Get conversation with patient
     */
    public function conversation(Request $request, User $patient): JsonResponse
    {
        $doctorUserId = $request->user()->id;

        $messages = Message::where(function ($query) use ($doctorUserId, $patient) {
            $query->where('sender_id', $doctorUserId)
                  ->where('receiver_id', $patient->id);
        })->orWhere(function ($query) use ($doctorUserId, $patient) {
            $query->where('sender_id', $patient->id)
                  ->where('receiver_id', $doctorUserId);
        })
        ->latest()
        ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Send message to patient
     */
    public function send(Request $request, User $patient): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'content' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $patient->id,
            'content' => $request->content,
            'message_type' => 'text',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message->load('receiver')
        ], 201);
    }
}
