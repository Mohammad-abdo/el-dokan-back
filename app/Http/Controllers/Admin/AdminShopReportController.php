<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\ShopReportService;
use App\Exports\ShopReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use ArPHP\I18N\Arabic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AdminShopReportController extends Controller
{
    public function __construct(private ShopReportService $reportService)
    {
    }

    /**
     * Generate a shop report.
     * POST /api/admin/shops/reports/generate
     */
    public function generate(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|integer|exists:shops,id',
            'report_type' => 'required|in:full,custom',
            'sections' => 'required_if:report_type,custom|array',
            'sections.*' => 'in:overview,products,wallet,ordersFromReps,visits,representatives,companyOrders,branches,documents',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'format' => 'required|in:pdf,excel,json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $shop = Shop::with([
            'user',
            'companyPlan',
            'financial',
            'branches',
            'documents',
            'representatives.user',
        ])->findOrFail($request->shop_id);

        $from = $request->date_from;
        $to = $request->date_to;
        $format = $request->format;

        if ($request->report_type === 'full') {
            $report = $this->reportService->generateFullReport($shop, $from, $to);
        } else {
            $report = $this->reportService->generateCustomReport(
                $shop,
                $request->sections ?? [],
                $from,
                $to
            );
        }

        return match ($format) {
            'json' => $this->jsonResponse($report),
            'pdf' => $this->pdfResponse($report, $shop),
            'excel' => $this->excelResponse($report, $shop),
        };
    }

    private function jsonResponse(array $report): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    private function pdfResponse(array $report, Shop $shop): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        // DomPDF sometimes breaks Arabic ligatures unless reshaped to connected glyphs.
        $report = $this->reshapeArabicForPdf($report);

        $pdf = Pdf::loadView('reports.shop-report', ['report' => $report])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                // Shop/product images may be remote (http://...).
                // DomPDF needs this to allow fetching remote resources.
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        $shopName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $shop->name ?? 'shop');
        $filename = "shop-report-{$shopName}-{$report['period']['from']}-to-{$report['period']['to']}.pdf";

        return $pdf->download($filename);
    }

    private function reshapeArabicForPdf(mixed $value): mixed
    {
        $arabic = new Arabic();

        $transform = function (mixed $v) use (&$transform, $arabic): mixed {
            if (is_string($v)) {
                try {
                    // Shape only Arabic text to avoid breaking English/numbers.
                    return $arabic->isArabic($v) ? $arabic->utf8Glyphs($v, 50, true, true) : $v;
                } catch (\Throwable $e) {
                    return $v;
                }
            }

            if (is_array($v)) {
                foreach ($v as $k => $item) {
                    $v[$k] = $transform($item);
                }
                return $v;
            }

            return $v;
        };

        return $transform($value);
    }

    private function excelResponse(array $report, Shop $shop): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $shopName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $shop->name ?? 'shop');
        $filename = "shop-report-{$shopName}-{$report['period']['from']}-to-{$report['period']['to']}.xlsx";

        return Excel::download(new ShopReportExport($report), $filename);
    }
}

