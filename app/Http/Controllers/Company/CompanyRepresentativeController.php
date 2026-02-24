<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * مندوبو الشركة — قائمة مندوبي الشركة المسجلة.
 */
class CompanyRepresentativeController extends Controller
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

        $reps = $shop->representatives()->with('user')->latest()->get();
        $data = $reps->map(function ($r) {
            return [
                'id' => $r->id,
                'employee_id' => $r->employee_id,
                'territory' => $r->territory,
                'status' => $r->status,
                'user' => $r->user ? [
                    'id' => $r->user->id,
                    'username' => $r->user->username,
                    'email' => $r->user->email,
                    'phone' => $r->user->phone,
                ] : null,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }
}
