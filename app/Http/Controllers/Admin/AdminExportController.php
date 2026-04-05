<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyOrder;
use App\Models\CompanyPlan;
use App\Models\CompanyProduct;
use App\Models\Representative;
use App\Models\Shop;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AdminExportController extends Controller
{
    private function fullImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $base = rtrim(config('app.url'), '/');
        return $base . (str_starts_with($path, '/') ? $path : '/' . $path);
    }

    private function normalizeCompanyProduct(CompanyProduct $p): array
    {
        $arr = $p->toArray();

        if (!empty($arr['images']) && is_array($arr['images'])) {
            $arr['images'] = array_map(fn ($u) => $this->fullImageUrl(is_string($u) ? $u : null), $arr['images']);
        }

        $arr['first_image_url'] = $p->first_image_url;

        return $arr;
    }

    private function buildFullPayload(Shop $shop): array
    {
        $orders = $shop->companyOrders()
            ->with(['representative.user', 'visit', 'items.companyProduct', 'customerShop', 'customerDoctor'])
            ->orderByDesc('created_at')
            ->get();

        $companyProducts = $shop->companyProducts()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $companyPlans = CompanyPlan::query()->orderBy('sort_order')->get();

        $representatives = Representative::query()
            ->where('shop_id', $shop->id)
            ->with(['user'])
            ->orderByDesc('created_at')
            ->get();

        $visits = Visit::query()
            ->where('shop_id', $shop->id)
            ->with(['doctor', 'representative.user'])
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->get();

        $normalizedOrders = $orders->map(function (CompanyOrder $order) {
            $arr = $order->toArray();
            $arr['items'] = $order->items->map(function ($item) {
                $product = $item->companyProduct;
                $productArr = $product ? $this->normalizeCompanyProduct($product) : null;

                return [
                    'id' => $item->id,
                    'company_order_id' => $item->company_order_id,
                    'company_product_id' => $item->company_product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'companyProduct' => $productArr,
                ];
            })->values();

            return $arr;
        })->values();

        $companyOrderItems = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $product = $item->companyProduct;
                $companyOrderItems[] = [
                    'id' => $item->id,
                    'company_order_item_id' => $item->id,
                    'company_order_id' => $item->company_order_id,
                    'order_number' => $order->order_number,
                    'order_status' => $order->status,
                    'company_product_id' => $item->company_product_id,
                    'company_product_name' => $product?->name,
                    'company_product_sku' => $product?->sku,
                    'company_product_image_url' => $product?->first_image_url,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ];
            }
        }

        return [
            'meta' => [
                'shop_id' => $shop->id,
                'company' => [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'logo_url' => $this->fullImageUrl($shop->image_url),
                ],
                'exportDate' => now()->toISOString(),
            ],
            'company_orders' => $normalizedOrders,
            'company_order_items' => $companyOrderItems,
            'company_products' => $companyProducts->map(fn (CompanyProduct $p) => $this->normalizeCompanyProduct($p))->values(),
            'company_plans' => $companyPlans->values(),
            'representatives' => $representatives->values(),
            'visits' => $visits->values(),
        ];
    }

    public function data(Request $request): JsonResponse
    {
        $shopId = $request->query('shop_id');

        $shop = null;
        if ($shopId) {
            $shop = Shop::query()->find($shopId);
        }

        if (!$shop) {
            $shop = Shop::query()->where('category', 'company')->first();
        }

        if (!$shop) {
            return response()->json([
                'success' => true,
                'data' => [
                    'meta' => [
                        'shop_id' => null,
                        'company' => ['id' => null, 'name' => null, 'logo_url' => null],
                        'exportDate' => now()->toISOString(),
                    ],
                    'company_orders' => [],
                    'company_order_items' => [],
                    'company_products' => [],
                    'company_plans' => [],
                    'representatives' => [],
                    'visits' => [],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->buildFullPayload($shop),
        ]);
    }

    /**
     * Generate PDF with Puppeteer using Symfony Process (safer than exec).
     *
     * Route: GET /api/admin/export/pdf?shop_id=...
     */
    public function exportPdf(Request $request)
    {
        $payloadResponse = $this->data($request);
        $payload = $payloadResponse->getData(true)['data'] ?? null;

        if (!$payload) {
            return response()->json(['success' => false, 'message' => 'No data'], 404);
        }

        $shopId = $payload['meta']['shop_id'] ?? 'all';
        $filename = "full-data-export-{$shopId}-" . now()->toDateString() . ".pdf";

        $tmpDir = storage_path('app/export_pdf');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $inputPath = $tmpDir . '/payload-' . uniqid('', true) . '.json';
        $outputPath = $tmpDir . '/export-' . uniqid('', true) . '.pdf';

        file_put_contents($inputPath, json_encode($payload, JSON_UNESCAPED_UNICODE));

        $nodeScript = base_path('pdf-service/generateFullDataExportPdf.js');
        if (!file_exists($nodeScript)) {
            @unlink($inputPath);
            return response()->json(['success' => false, 'message' => 'PDF generator script missing'], 500);
        }

        try {
            $process = Process::fromShellCommandline(
                'node ' . $nodeScript . ' ' . $inputPath . ' ' . $outputPath
            );
            $process->setTimeout(120);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        } catch (ProcessFailedException $e) {
            Log::error('Puppeteer export failed', [
                'shop_id' => $shopId,
                'error' => $e->getMessage(),
                'output' => $process->getOutput() ?? null,
            ]);
            @unlink($inputPath);
            return response()->json([
                'success' => false,
                'message' => 'PDF generation failed. Please try again later.'
            ], 500);
        }

        @unlink($inputPath);

        if (!file_exists($outputPath)) {
            Log::error('PDF file not created despite successful process', [
                'shop_id' => $shopId,
                'output_path' => $outputPath,
            ]);
            return response()->json(['success' => false, 'message' => 'PDF generation failed'], 500);
        }

        return response()->download($outputPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }
}

