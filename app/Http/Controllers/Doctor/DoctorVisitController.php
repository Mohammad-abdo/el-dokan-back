<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorVisitController extends Controller
{
    /**
     * List visits for the authenticated doctor (مواعيد الزيارات)
     * Filter: period = all | upcoming | completed
     */
    public function index(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found',
            ], 404);
        }

        $query = Visit::where('doctor_id', $doctor->id)
            ->with(['representative.user', 'shop']);

        $period = $request->get('period', 'all');
        $today = now()->toDateString();

        if ($period === 'upcoming') {
            $query->where(function ($q) use ($today) {
                $q->where('visit_date', '>=', $today)
                    ->whereIn('status', ['pending', 'approved']);
            });
        } elseif ($period === 'completed') {
            $query->where(function ($q) use ($today) {
                $q->where('visit_date', '<', $today)
                    ->orWhere('status', 'completed');
            });
        }

        $visits = $query->orderBy('visit_date')->orderBy('visit_time')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $visits->items(),
            'pagination' => [
                'total' => $visits->total(),
                'per_page' => $visits->perPage(),
                'current_page' => $visits->currentPage(),
                'last_page' => $visits->lastPage(),
            ],
        ]);
    }

    /**
     * Doctor confirms a visit (تأكيد)
     */
    public function confirm(Request $request, Visit $visit): JsonResponse
    {
        $doctor = $request->user()->doctor;
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found',
            ], 404);
        }

        if ((int) $visit->doctor_id !== (int) $doctor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($visit->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Visit was rejected',
            ], 422);
        }

        $visit->update([
            'doctor_confirmed_at' => now(),
            'status' => $visit->status === 'pending' ? 'approved' : $visit->status,
        ]);

        $visit->load(['representative.user', 'shop']);

        return response()->json([
            'success' => true,
            'message' => 'Visit confirmed successfully',
            'data' => $visit,
        ]);
    }
}
