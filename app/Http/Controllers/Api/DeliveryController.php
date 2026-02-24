<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    /**
     * Track delivery
     */
    public function track(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $delivery = $order->delivery;
        
        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found'
            ], 404);
        }

        $delivery->load('driver');

        return response()->json([
            'success' => true,
            'data' => $delivery
        ]);
    }

    /**
     * Get delivery driver
     */
    public function driver(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $delivery = $order->delivery;
        
        if (!$delivery || !$delivery->driver_id) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not assigned yet'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'driver' => $delivery->driver,
                'status' => $delivery->status,
                'estimated_arrival' => $delivery->estimated_arrival_minutes,
            ]
        ]);
    }

    /**
     * Contact driver
     */
    public function contactDriver(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $delivery = $order->delivery;
        
        if (!$delivery || !$delivery->driver_id) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not assigned yet'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Driver contact information',
            'data' => [
                'driver_phone' => $delivery->driver->phone,
                'driver_name' => $delivery->driver->name,
            ]
        ]);
    }

    /**
     * Confirm delivery
     */
    public function confirm(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $delivery = $order->delivery;
        
        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found'
            ], 404);
        }

        if ($delivery->status !== 'in_transit') {
            return response()->json([
                'success' => false,
                'message' => 'Delivery cannot be confirmed at this stage'
            ], 400);
        }

        $delivery->update(['status' => 'delivered']);
        $order->update(['status' => 'delivered']);

        return response()->json([
            'success' => true,
            'message' => 'Delivery confirmed successfully',
            'data' => $delivery
        ]);
    }

    /**
     * Get delivery map data
     */
    public function map(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $delivery = $order->delivery;
        $deliveryAddress = $order->deliveryAddress;

        return response()->json([
            'success' => true,
            'data' => [
                'delivery_address' => [
                    'latitude' => $deliveryAddress->latitude,
                    'longitude' => $deliveryAddress->longitude,
                    'address' => $deliveryAddress->detailed_address,
                ],
                'driver_location' => $delivery && $delivery->driver ? [
                    'latitude' => $delivery->driver->current_latitude,
                    'longitude' => $delivery->driver->current_longitude,
                ] : null,
                'status' => $delivery ? $delivery->status : 'pending',
            ]
        ]);
    }
}
