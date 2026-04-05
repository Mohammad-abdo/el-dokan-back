<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyOrder;
use App\Models\CompanyOrderItem;
use App\Models\CompanyProduct;
use App\Models\Doctor;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * مبيعات الشركة: طلبات من مندوبي الشركة لمتاجر أو أطباء (مرتبطة بالزيارات).
 */
class AdminCompanyOrderController extends Controller
{
    public function index(Request $request, Shop $shop): JsonResponse
    {
        $query = $shop->companyOrders()->with(['representative.user', 'visit', 'items.companyProduct']);

        if ($request->filled('representative_id')) {
            $query->where('representative_id', $request->representative_id);
        }
        if ($request->filled('visit_id')) {
            $query->where('visit_id', $request->visit_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->with(['customerable']);
        $orders = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Shop $shop, CompanyOrder $company_order): JsonResponse
    {
        if ($company_order->shop_id != $shop->id) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        $company_order->load(['representative.user', 'visit', 'items.companyProduct', 'customerable']);
        return response()->json(['success' => true, 'data' => $company_order]);
    }

    public function store(Request $request, Shop $shop): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'representative_id' => 'required|exists:representatives,id',
            'visit_id' => 'nullable|exists:visits,id',
            'customer_type' => 'required|in:shop,doctor',
            'customer_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.company_product_id' => 'required|exists:company_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $rep = $shop->representatives()->find($data['representative_id']);
        if (!$rep) {
            return response()->json(['success' => false, 'message' => 'Representative does not belong to this company'], 422);
        }

        if ($data['customer_type'] === CompanyOrder::CUSTOMER_TYPE_SHOP) {
            if (!Shop::where('id', $data['customer_id'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Invalid shop customer id'], 422);
            }
        } else {
            if (!Doctor::where('id', $data['customer_id'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Invalid doctor customer id'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $total = 0;
            $orderItems = [];
            foreach ($data['items'] as $item) {
                $product = CompanyProduct::where('id', $item['company_product_id'])->where('shop_id', $shop->id)->first();
                if (!$product) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Invalid company product'], 422);
                }
                $qty = (int) $item['quantity'];
                $price = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $product->unit_price;
                $rowTotal = $qty * $price;
                $total += $rowTotal;
                $orderItems[] = [
                    'company_product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $rowTotal,
                ];
            }

            $order = CompanyOrder::create([
                'shop_id' => $shop->id,
                'representative_id' => $data['representative_id'],
                'visit_id' => $data['visit_id'] ?? null,
                'customerable_type' => $data['customer_type'],
                'customerable_id' => $data['customer_id'],
                'total_amount' => $total,
                'status' => CompanyOrder::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
                'ordered_at' => now(),
            ]);

            foreach ($orderItems as $row) {
                $row['company_order_id'] = $order->id;
                CompanyOrderItem::create($row);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        $order->load(['representative.user', 'visit', 'items.companyProduct', 'customerable']);
        return response()->json(['success' => true, 'data' => $order], 201);
    }
}
