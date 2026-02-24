<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use App\Models\DoctorPrescription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminDoctorController extends Controller
{
    /**
     * Display a listing of doctors
     */
    public function index(): JsonResponse
    {
        $doctors = Doctor::with(['user', 'wallet', 'bookings'])
            ->withCount('bookings')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $doctors
        ]);
    }

    /**
     * Store a newly created doctor
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'specialty' => 'required|string|max:255',
            'specialty_ar' => 'nullable|string|max:255',
            'specialty_en' => 'nullable|string|max:255',
            'photo_url' => 'nullable|string|url',
            'consultation_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'available_days' => 'required|array',
            'available_hours_start' => 'required|date_format:H:i',
            'available_hours_end' => 'required|date_format:H:i',
            'location' => 'required|string',
            'location_ar' => 'nullable|string',
            'location_en' => 'nullable|string',
            'consultation_duration' => 'nullable|integer|min:10',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Only create with fillable fields
        $fillableFields = [
            'user_id',
            'name', 'name_ar', 'name_en',
            'specialty', 'specialty_ar', 'specialty_en',
            'photo_url',
            'consultation_price',
            'discount_percentage',
            'available_days',
            'available_hours_start',
            'available_hours_end',
            'location', 'location_ar', 'location_en',
            'consultation_duration',
            'is_active',
        ];

        $createData = $request->only($fillableFields);
        
        // Handle empty strings as null for nullable fields
        $nullableFields = ['name_ar', 'name_en', 'specialty_ar', 'specialty_en', 'location_ar', 'location_en', 'photo_url', 'discount_percentage'];
        foreach ($nullableFields as $field) {
            if (isset($createData[$field]) && $createData[$field] === '') {
                $createData[$field] = null;
            }
        }

        $doctor = Doctor::create($createData);

        // Assign 'doctor' role to the user
        if ($doctor->user_id) {
            $user = User::find($doctor->user_id);
            if ($user) {
                // Assign role using Spatie Permissions
                if (!$user->hasRole('doctor')) {
                    $user->assignRole('doctor');
                }
                // Update role field in users table
                $user->update(['role' => 'doctor']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Doctor created successfully',
            'data' => $doctor->load('user')
        ], 201);
    }

    /**
     * Display the specified doctor
     */
    public function show(Doctor $doctor): JsonResponse
    {
        $doctor->load(['user', 'medicalCenters', 'primaryMedicalCenter', 'ratings', 'wallet']);
        $doctor->loadCount('bookings');

        $bookingsCompleted = $doctor->bookings()->where('status', 'completed')->count();
        $bookingsCancelled = $doctor->bookings()->where('status', 'cancelled')->count();
        $bookingsPending = $doctor->bookings()->whereIn('status', ['pending', 'confirmed', 'scheduled'])->count();

        $data = $doctor->toArray();
        $data['bookings_completed'] = $bookingsCompleted;
        $data['bookings_cancelled'] = $bookingsCancelled;
        $data['bookings_pending'] = $bookingsPending;
        $data['prescriptions_count'] = DoctorPrescription::where('doctor_id', $doctor->id)->where('is_template', false)->count();
        $data['visits_count'] = \App\Models\Visit::where('doctor_id', $doctor->id)->count();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * List bookings for a doctor (admin)
     */
    public function bookings(Request $request, Doctor $doctor): JsonResponse
    {
        $query = $doctor->bookings()->with(['user'])->latest('appointment_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $bookings = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ],
        ]);
    }

    /**
     * List prescriptions for a doctor (admin)
     */
    public function prescriptions(Request $request, Doctor $doctor): JsonResponse
    {
        $query = DoctorPrescription::where('doctor_id', $doctor->id)
            ->with(['patient', 'items'])
            ->latest();

        if ($request->filled('is_template')) {
            $query->where('is_template', $request->boolean('is_template'));
        }

        $perPage = min((int) $request->get('per_page', 20), 50);
        $prescriptions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $prescriptions->items(),
            'pagination' => [
                'total' => $prescriptions->total(),
                'per_page' => $prescriptions->perPage(),
                'current_page' => $prescriptions->currentPage(),
                'last_page' => $prescriptions->lastPage(),
            ],
        ]);
    }

    /**
     * Update the specified doctor
     */
    public function update(Request $request, Doctor $doctor): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'specialty' => 'sometimes|string|max:255',
            'specialty_ar' => 'nullable|string|max:255',
            'specialty_en' => 'nullable|string|max:255',
            'photo_url' => 'nullable|string|url',
            'consultation_price' => 'sometimes|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'available_days' => 'nullable|array',
            'available_hours_start' => 'nullable|date_format:H:i',
            'available_hours_end' => 'nullable|date_format:H:i',
            'location' => 'sometimes|string',
            'location_ar' => 'nullable|string',
            'location_en' => 'nullable|string',
            'consultation_duration' => 'nullable|integer|min:10',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Only update fillable fields
        $fillableFields = [
            'name', 'name_ar', 'name_en',
            'specialty', 'specialty_ar', 'specialty_en',
            'photo_url',
            'consultation_price',
            'discount_percentage',
            'available_days',
            'available_hours_start',
            'available_hours_end',
            'location', 'location_ar', 'location_en',
            'consultation_duration',
            'is_active',
        ];

        $updateData = $request->only($fillableFields);
        
        // Handle empty strings as null for nullable fields
        $nullableFields = ['name_ar', 'name_en', 'specialty_ar', 'specialty_en', 'location_ar', 'location_en', 'photo_url', 'discount_percentage'];
        foreach ($nullableFields as $field) {
            if (isset($updateData[$field]) && $updateData[$field] === '') {
                $updateData[$field] = null;
            }
        }

        $doctor->update($updateData);

        // Ensure user has 'doctor' role if user_id is updated
        if ($request->has('user_id') && $doctor->user_id) {
            $user = User::find($doctor->user_id);
            if ($user) {
                // Assign role using Spatie Permissions
                if (!$user->hasRole('doctor')) {
                    $user->assignRole('doctor');
                }
                // Update role field in users table
                $user->update(['role' => 'doctor']);
            }
        } elseif ($doctor->user_id) {
            // Ensure existing user has doctor role
            $user = User::find($doctor->user_id);
            if ($user) {
                // Assign role using Spatie Permissions
                if (!$user->hasRole('doctor')) {
                    $user->assignRole('doctor');
                }
                // Update role field in users table
                if ($user->role !== 'doctor') {
                    $user->update(['role' => 'doctor']);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Doctor updated successfully',
            'data' => $doctor->load('user')
        ]);
    }

    /**
     * Remove the specified doctor
     */
    public function destroy(Doctor $doctor): JsonResponse
    {
        // Remove 'doctor' role from user if this is their only doctor profile
        if ($doctor->user_id) {
            $user = User::find($doctor->user_id);
            if ($user && $user->hasRole('doctor')) {
                // Check if user has other doctor profiles
                $otherDoctors = Doctor::where('user_id', $doctor->user_id)
                    ->where('id', '!=', $doctor->id)
                    ->count();
                
                if ($otherDoctors === 0) {
                    $user->removeRole('doctor');
                    // Update role field in users table - set to null or first available role
                    $remainingRoles = $user->roles->pluck('name')->toArray();
                    $newRole = !empty($remainingRoles) ? $remainingRoles[0] : null;
                    $user->update(['role' => $newRole]);
                }
            }
        }

        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor deleted successfully'
        ]);
    }

    /**
     * Suspend doctor
     */
    public function suspend(Request $request, Doctor $doctor): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        $doctor->update([
            'status' => 'suspended',
            'suspension_reason' => $request->reason,
            'suspended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor suspended successfully',
            'data' => $doctor
        ]);
    }

    /**
     * Activate doctor
     */
    public function activate(Doctor $doctor): JsonResponse
    {
        $doctor->update([
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor activated successfully',
            'data' => $doctor
        ]);
    }
}
