<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * زيارات مندوبي الشركة — قائمة الزيارات.
 */
class CompanyVisitController extends Controller
{
    private function getCompanyShop(Request $request): ?Shop
    {
        $shop = Shop::where('user_id', $request->user()->id)->first();
        return $shop && $shop->isCompany() ? $shop : null;
    }

    public function index(Request $request): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $repIds = $shop->representatives()->pluck('id');
        $query = Visit::with(['representative.user', 'shop', 'doctor'])
            ->whereIn('representative_id', $repIds);

        if ($request->filled('representative_id')) {
            $query->where('representative_id', $request->representative_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $visits = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $visits->items(),
            'meta' => [
                'current_page' => $visits->currentPage(),
                'last_page' => $visits->lastPage(),
                'per_page' => $visits->perPage(),
                'total' => $visits->total(),
            ],
        ]);
    }
}
