<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalCenter;
use App\Models\Shop;
use App\Models\Doctor;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminMapController extends Controller
{
    /**
     * Get all map entities (shops, doctors, drivers, medical centers) with coordinates for admin map view.
     */
    public function entities(): JsonResponse
    {
        $shops = Shop::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('is_active', true)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'type' => 'shop',
                'name' => $s->name,
                'address' => $s->address,
                'lat' => (float) $s->latitude,
                'lng' => (float) $s->longitude,
                'category' => $s->category,
                'phone' => $s->phone,
            ]);

        $doctors = Doctor::with(['primaryMedicalCenter', 'medicalCenters' => fn ($q) => $q->whereNotNull('latitude')->whereNotNull('longitude')])
            ->where('is_active', true)
            ->get()
            ->map(function ($d) {
                $center = $d->primaryMedicalCenter && $d->primaryMedicalCenter->latitude !== null
                    ? $d->primaryMedicalCenter
                    : $d->medicalCenters->first();
                if (!$center) {
                    return null;
                }
                return [
                    'id' => $d->id,
                    'type' => 'doctor',
                    'name' => $d->name,
                    'specialty' => $d->specialty ?? $d->specialty_en ?? $d->specialty_ar,
                    'address' => $center->address ?? $d->location,
                    'lat' => (float) $center->latitude,
                    'lng' => (float) $center->longitude,
                    'consultation_price' => $d->consultation_price,
                ];
            })
            ->filter()
            ->values();

        $drivers = Driver::whereNotNull('current_location_lat')
            ->whereNotNull('current_location_lng')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'type' => 'driver',
                'name' => $d->name,
                'phone' => $d->phone,
                'lat' => (float) $d->current_location_lat,
                'lng' => (float) $d->current_location_lng,
                'status' => $d->status ?? 'unknown',
            ]);

        $medicalCenters = MedicalCenter::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when(request()->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'type' => 'medical_center',
                'name' => $m->name,
                'address' => $m->address,
                'lat' => (float) $m->latitude,
                'lng' => (float) $m->longitude,
                'phone' => $m->phone,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'shops' => $shops->values(),
                'doctors' => $doctors->values(),
                'drivers' => $drivers->values(),
                'medical_centers' => $medicalCenters->values(),
            ],
        ]);
    }

    /**
     * Get medical centers
     */
    public function medicalCenters(): JsonResponse
    {
        $centers = MedicalCenter::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $centers
        ]);
    }

    /**
     * Store medical center
     */
    public function storeMedicalCenter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $center = MedicalCenter::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Medical center created successfully',
            'data' => $center
        ], 201);
    }

    /**
     * Update medical center
     */
    public function updateMedicalCenter(Request $request, MedicalCenter $medicalCenter): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'phone' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $medicalCenter->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Medical center updated successfully',
            'data' => $medicalCenter
        ]);
    }

    /**
     * Delete medical center
     */
    public function destroyMedicalCenter(MedicalCenter $medicalCenter): JsonResponse
    {
        $medicalCenter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medical center deleted successfully'
        ]);
    }
}
