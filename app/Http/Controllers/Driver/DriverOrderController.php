<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DriverOrderController extends Controller
{
    protected function getDriver(Request $request): ?Driver
    {
        return Driver::where('user_id', $request->user()->id)->first();
    }

    /**
     * Get available orders for driver (no driver assigned or driver_id null)
     */
    public function availableOrders(Request $request): JsonResponse
    {
        $driver = $this->getDriver($request);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $radiusKm = $request->input('radius', 20);

        $query = Order::whereIn('status', ['received', 'confirmed'])
            ->where(function ($q) {
                $q->whereDoesntHave('delivery')
                    ->orWhereHas('delivery', fn ($q2) => $q2->whereNull('driver_id'));
            })
            ->with(['shop', 'deliveryAddress']);

        if ($latitude !== null && $longitude !== null && $radiusKm > 0) {
            $lat = (float) $latitude;
            $lng = (float) $longitude;
            $deg = $radiusKm / 111.0;
            $query->whereHas('deliveryAddress', function ($q) use ($lat, $lng, $deg) {
                $q->whereBetween('latitude', [$lat - $deg, $lat + $deg])
                    ->whereBetween('longitude', [$lng - $deg, $lng + $deg]);
            });
        }

        $orders = $query->latest()->paginate(20);

        $data = $orders->getCollection()->map(function ($order) {
            $shop = $order->shop;
            $addr = $order->deliveryAddress;
            return [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'pickup_address' => $shop ? $shop->address : null,
                'delivery_address' => $addr ? ($addr->detailed_address ?? $addr->district . ', ' . $addr->city) : null,
                'delivery_fee' => (float) $order->delivery_fee,
                'total_amount' => (float) $order->total_amount,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get delivery details for an order (for driver)
     */
    public function deliveryDetails(Request $request, $orderId): JsonResponse
    {
        $driver = $this->getDriver($request);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $order = Order::with(['shop', 'deliveryAddress', 'items.product'])->find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $delivery = $order->delivery;
        if ($delivery && $delivery->driver_id && $delivery->driver_id != $driver->id) {
            return response()->json(['success' => false, 'message' => 'Order already assigned to another driver'], 403);
        }

        $shop = $order->shop;
        $addr = $order->deliveryAddress;

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'pickup_address' => $shop ? $shop->address : null,
                'delivery_address' => $addr ? ($addr->detailed_address ?? $addr->district . ', ' . $addr->city) : null,
                'delivery_address_lat' => $addr ? $addr->latitude : null,
                'delivery_address_lng' => $addr ? $addr->longitude : null,
                'order_total' => (float) $order->total_amount,
                'delivery_fee' => (float) $order->delivery_fee,
                'grand_total' => (float) ($order->total_amount),
                'driver_offer' => $delivery ? $delivery->driver_offer : null,
            ],
        ]);
    }

    /**
     * Driver offers a delivery price
     */
    public function offer(Request $request, $orderId): JsonResponse
    {
        $driver = $this->getDriver($request);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $request->validate(['delivery_price' => 'required|numeric|min:0']);

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $delivery = $order->delivery;
        if (!$delivery) {
            $shop = $order->shop;
            $addr = $order->deliveryAddress;
            $delivery = Delivery::create([
                'order_id' => $order->id,
                'store_address' => $shop ? $shop->address : '',
                'delivery_address' => $addr ? ($addr->detailed_address ?? $addr->district . ', ' . $addr->city) : '',
                'driver_offer' => $request->delivery_price,
                'status' => 'pending',
            ]);
        } else {
            if ($delivery->driver_id && $delivery->driver_id != $driver->id) {
                return response()->json(['success' => false, 'message' => 'Order already assigned'], 403);
            }
            $delivery->update(['driver_offer' => $request->delivery_price]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Offer submitted successfully',
            'data' => ['delivery_offer' => (float) $request->delivery_price],
        ]);
    }

    /**
     * Driver accepts the order (assigns himself)
     */
    public function accept(Request $request, $orderId): JsonResponse
    {
        $driver = $this->getDriver($request);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $order = Order::with('shop', 'deliveryAddress')->find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $delivery = $order->delivery;
        if (!$delivery) {
            $shop = $order->shop;
            $addr = $order->deliveryAddress;
            $delivery = Delivery::create([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'store_address' => $shop ? $shop->address : '',
                'delivery_address' => $addr ? ($addr->detailed_address ?? $addr->district . ', ' . $addr->city) : '',
                'driver_offer' => $order->delivery_fee,
                'status' => 'assigned',
            ]);
        } else {
            if ($delivery->driver_id) {
                return response()->json(['success' => false, 'message' => 'Order already assigned'], 400);
            }
            $delivery->update(['driver_id' => $driver->id, 'status' => 'assigned']);
        }

        $driver->update(['status' => 'busy']);

        return response()->json([
            'success' => true,
            'message' => 'Order accepted successfully',
            'data' => ['delivery_id' => $delivery->id, 'order_id' => $order->id],
        ]);
    }

    /**
     * Driver confirms pickup from store
     */
    public function pickup(Request $request, $deliveryId): JsonResponse
    {
        $driver = $this->getDriver($request);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $delivery = Delivery::where('id', $deliveryId)->where('driver_id', $driver->id)->first();
        if (!$delivery) {
            return response()->json(['success' => false, 'message' => 'Delivery not found'], 404);
        }

        if (!in_array($delivery->status, ['assigned'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status for pickup'], 400);
        }

        $delivery->update(['status' => 'picked_up']);

        return response()->json([
            'success' => true,
            'message' => 'Pickup confirmed successfully',
            'data' => ['delivery_id' => $delivery->id, 'status' => 'picked_up'],
        ]);
    }

    /**
     * Driver confirms delivery to client (optional qr_code for verification)
     */
    public function confirmDelivery(Request $request, $deliveryId): JsonResponse
    {
        $driver = $this->getDriver($request);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $delivery = Delivery::with('order')->where('id', $deliveryId)->where('driver_id', $driver->id)->first();
        if (!$delivery) {
            return response()->json(['success' => false, 'message' => 'Delivery not found'], 404);
        }

        if (!in_array($delivery->status, ['picked_up', 'in_transit'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status for confirm delivery'], 400);
        }

        $delivery->update(['status' => 'delivered']);
        $delivery->order->update(['status' => 'delivered']);
        $driver->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'message' => 'Delivery confirmed successfully',
            'data' => ['delivery_id' => $delivery->id, 'order_id' => $delivery->order_id, 'status' => 'delivered'],
        ]);
    }
}
