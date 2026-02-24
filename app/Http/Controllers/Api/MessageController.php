<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Display a listing of conversations
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = Message::where('sender_id', $request->user()->id)
            ->orWhere('receiver_id', $request->user()->id)
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(function ($message) use ($request) {
                return $message->sender_id === $request->user()->id 
                    ? $message->receiver_id 
                    : $message->sender_id;
            });

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Get conversation with specific user
     */
    public function conversation(Request $request, User $user): JsonResponse
    {
        $messages = Message::where(function ($query) use ($request, $user) {
            $query->where('sender_id', $request->user()->id)
                  ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($request, $user) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', $request->user()->id);
        })
        ->latest()
        ->paginate(50);

        // Mark messages as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Send message
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
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
            'receiver_id' => $request->receiver_id,
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

    /**
     * Send voice message
     */
    public function sendVoice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'voice' => 'required|file|mimes:mp3,wav,m4a|max:10240', // 10MB
            'duration' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $voicePath = $request->file('voice')->store('voice_messages', 'public');
        $voiceUrl = Storage::url($voicePath);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message_type' => 'voice',
            'voice_url' => $voiceUrl,
            'voice_duration' => $request->duration,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Voice message sent successfully',
            'data' => $message->load('receiver')
        ], 201);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, Message $message): JsonResponse
    {
        if ($message->receiver_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read',
            'data' => $message
        ]);
    }
}
