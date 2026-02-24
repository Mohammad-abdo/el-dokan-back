<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\DeliveryStatusUpdated;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminDeliveryController extends Controller
{
    /**
     * Display a listing of deliveries
     */
    public function index(Request $request): JsonResponse
    {
        $query = Delivery::with(['order', 'driver']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by driver
        if ($request->has('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        $deliveries = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $deliveries
        ]);
    }

    /**
     * Display the specified delivery (for tracking: customer address lat/lng, driver location, order address)
     */
    public function show($id): JsonResponse
    {
        $delivery = Delivery::with(['order.user', 'order.deliveryAddress', 'order.shop', 'driver'])
            ->where('id', $id)
            ->orWhere('order_id', $id)
            ->first();

        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found'
            ], 404);
        }

        $order = $delivery->order;
        $addr = $order ? $order->deliveryAddress : null;
        $shop = $order ? $order->shop : null;

        // Store coordinates: prefer shop.latitude/longitude, else parse store_address if "lat,lng"
        $storeLat = null;
        $storeLng = null;
        if ($shop && $shop->latitude !== null && $shop->longitude !== null) {
            $storeLat = (float) $shop->latitude;
            $storeLng = (float) $shop->longitude;
        } elseif ($delivery->store_address && preg_match('/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)\s*$/', trim($delivery->store_address), $m)) {
            $storeLat = (float) $m[1];
            $storeLng = (float) $m[2];
        }

        $payload = $delivery->toArray();
        $payload['order_number'] = $order ? $order->order_number : null;

        // Customer (order user) and delivery address with coordinates
        $payload['customer'] = $order && $order->user ? [
            'id' => $order->user->id,
            'name' => $order->user->name ?? $order->user->username,
            'email' => $order->user->email,
            'phone' => $order->user->phone,
        ] : null;

        $payload['customer_address'] = $addr ? [
            'address' => $addr->detailed_address ?? trim(($addr->district ?? '') . ', ' . ($addr->city ?? '')),
            'latitude' => $addr->latitude ? (float) $addr->latitude : null,
            'longitude' => $addr->longitude ? (float) $addr->longitude : null,
            'city' => $addr->city,
            'district' => $addr->district,
        ] : null;

        $payload['store_address_text'] = $delivery->store_address;
        $payload['store_latitude'] = $storeLat;
        $payload['store_longitude'] = $storeLng;

        $payload['delivery_address_text'] = $delivery->delivery_address;
        $payload['delivery_latitude'] = $addr && $addr->latitude ? (float) $addr->latitude : null;
        $payload['delivery_longitude'] = $addr && $addr->longitude ? (float) $addr->longitude : null;

        if ($delivery->driver) {
            $d = $delivery->driver;
            $payload['driver'] = array_merge($d->toArray(), [
                'current_location_lat' => $d->current_location_lat ? (float) $d->current_location_lat : null,
                'current_location_lng' => $d->current_location_lng ? (float) $d->current_location_lng : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $payload
        ]);
    }

    /**
     * Update delivery status
     */
    public function updateStatus(Request $request, Delivery $delivery): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,assigned,picked_up,in_transit,delivered,failed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $delivery->update(['status' => $request->status]);

        broadcast(new DeliveryStatusUpdated($delivery->id, $request->status))->toOthers();

        // Update order status if delivered
        if ($request->status === 'delivered' && $delivery->order) {
            $delivery->order->update(['status' => 'delivered']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery status updated successfully',
            'data' => $delivery
        ]);
    }

    /**
     * Assign driver to delivery
     */
    public function assignDriver(Request $request, Delivery $delivery): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:drivers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $delivery->update([
            'driver_id' => $request->driver_id,
            'status' => 'assigned',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Driver assigned successfully',
            'data' => $delivery->load('driver')
        ]);
    }
}


