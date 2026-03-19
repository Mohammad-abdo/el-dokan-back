<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\DoctorReportService;
use App\Exports\DoctorReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use ArPHP\I18N\Arabic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AdminDoctorReportController extends Controller
{
    public function __construct(private DoctorReportService $reportService) {}

    /**
     * Generate a doctor report.
     * POST /api/admin/doctors/reports/generate
     */
    public function generate(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'doctor_id'   => 'required|integer|exists:doctors,id',
            'report_type' => 'required|in:full,custom',
            'sections'    => 'required_if:report_type,custom|array',
            'sections.*'  => 'in:schedule,prescriptions,bookings,patients,wallet,visits,treatments,medical_centers,ratings',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'format'      => 'required|in:pdf,excel,json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $doctor  = Doctor::with(['user', 'wallet', 'medicalCenters', 'primaryMedicalCenter', 'selectedTreatments'])->findOrFail($request->doctor_id);
        $from    = $request->date_from;
        $to      = $request->date_to;
        $format  = $request->format;

        if ($request->report_type === 'full') {
            $report = $this->reportService->generateFullReport($doctor, $from, $to);
        } else {
            $report = $this->reportService->generateCustomReport($doctor, $request->sections ?? [], $from, $to);
        }

        return match ($format) {
            'json'  => $this->jsonResponse($report),
            'pdf'   => $this->pdfResponse($report, $doctor),
            'excel' => $this->excelResponse($report, $doctor),
        };
    }

    private function jsonResponse(array $report): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $report,
        ]);
    }

    private function pdfResponse(array $report, Doctor $doctor): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        // DomPDF doesn't always apply Arabic shaping (ligatures). Ar-PHP converts Arabic
        // into glyph-joined UTF-8 so letters become connected in the PDF.
        $report = $this->reshapeArabicForPdf($report);

        $pdf = Pdf::loadView('reports.doctor-report', ['report' => $report])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'  => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $doctorName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $doctor->name ?? 'doctor');
        $filename   = "doctor-report-{$doctorName}-{$report['period']['from']}-to-{$report['period']['to']}.pdf";

        return $pdf->download($filename);
    }

    private function reshapeArabicForPdf(mixed $value): mixed
    {
        $arabic = new Arabic();

        $transform = function (mixed $v) use (&$transform, $arabic): mixed {
            if (is_string($v)) {
                // Shape only Arabic text to avoid breaking English/numbers.
                try {
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

    private function excelResponse(array $report, Doctor $doctor): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $doctorName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $doctor->name ?? 'doctor');
        $filename   = "doctor-report-{$doctorName}-{$report['period']['from']}-to-{$report['period']['to']}.xlsx";

        return Excel::download(new DoctorReportExport($report), $filename);
    }
}
