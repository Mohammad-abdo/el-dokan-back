<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Events\DriverLocationUpdated;
use App\Models\Delivery;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DriverLocationController extends Controller
{
    /**
     * Update driver current location and broadcast to active delivery tracking pages (WebSocket).
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        $driver->update([
            'current_location_lat' => $lat,
            'current_location_lng' => $lng,
        ]);

        $activeDeliveries = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit'])
            ->pluck('id');

        foreach ($activeDeliveries as $deliveryId) {
            broadcast(new DriverLocationUpdated($deliveryId, $driver->id, $lat, $lng))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => [
                'latitude' => $lat,
                'longitude' => $lng,
            ],
        ]);
    }
}
