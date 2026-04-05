<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\UserWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->bookings()->with('doctor');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'booking_type' => 'required|in:online,in_clinic',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'patient_name' => 'required|string|max:255',
            'payment_method' => 'required|in:credit_card,e_wallet,cash_on_delivery',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $doctor = Doctor::findOrFail($request->doctor_id);
        
        // Calculate total amount
        $totalAmount = $doctor->consultation_price;
        if ($doctor->discount_percentage > 0) {
            $totalAmount = $totalAmount - ($totalAmount * $doctor->discount_percentage / 100);
        }

        $booking = Booking::create([
            'booking_number' => 'BK-' . str_pad(Booking::count() + 1, 6, '0', STR_PAD_LEFT),
            'user_id' => $request->user()->id,
            'doctor_id' => $request->doctor_id,
            'booking_type' => $request->booking_type,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'patient_name' => $request->patient_name,
            'total_amount' => $totalAmount,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_method === 'cash_on_delivery' ? 'pending' : 'paid',
            'status' => 'upcoming',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking->load('doctor')
        ], 201);
    }

    /**
     * Display the specified booking
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $booking->load('doctor');

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this booking'
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);

        // Refund if paid
        if ($booking->payment_status === 'paid') {
            DB::transaction(function () use ($request, $booking) {
                $user = \App\Models\User::lockForUpdate()->find($request->user()->id);
                $balanceBefore = $user->wallet_balance;
                $user->increment('wallet_balance', $booking->total_amount);
                UserWalletTransaction::create([
                    'user_id'        => $user->id,
                    'type'           => 'credit',
                    'amount'         => $booking->total_amount,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $user->fresh()->wallet_balance,
                    'description'    => 'Refund for cancelled booking #' . $booking->booking_number,
                    'reference_type' => Booking::class,
                    'reference_id'   => $booking->id,
                ]);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking
        ]);
    }

    /**
     * Rate booking
     */
    public function rate(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->update([
            'status' => $booking->status, // touch updated_at without writing rating
        ]);

        // Write rating to the centralised ratings table
        \App\Models\Rating::create([
            'user_id' => $request->user()->id,
            'rateable_type' => Doctor::class,
            'rateable_id' => $booking->doctor_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully',
            'data' => $booking
        ]);
    }

    /**
     * Submit complaint
     */
    public function complaint(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'complaint' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->update(['complaint' => $request->complaint]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint submitted successfully',
            'data' => $booking
        ]);
    }
}
