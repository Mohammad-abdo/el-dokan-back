<?php

namespace App\Http\Controllers\Representative;

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
 * المندوب ينشئ طلب شركة (بيع منتجات الشركة لمتجر أو طبيب).
 */
class RepresentativeCompanyOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $rep = $request->user()->representative;
        if (!$rep || !$rep->shop_id) {
            return response()->json(['success' => false, 'message' => 'Representative must belong to a company'], 404);
        }

        $shop = $rep->shop;
        if (!$shop || !$shop->isCompany()) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $validator = Validator::make($request->all(), [
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
        if (!empty($data['visit_id'])) {
            $visit = $rep->visits()->find($data['visit_id']);
            if (!$visit) {
                return response()->json(['success' => false, 'message' => 'Visit not found or does not belong to you'], 422);
            }
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
                'representative_id' => $rep->id,
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
            return response()->json(['success' => false, 'message' => 'Failed to create order'], 500);
        }

        $order->load(['visit', 'items.companyProduct', 'customerable']);
        return response()->json(['success' => true, 'data' => $order], 201);
    }
}
