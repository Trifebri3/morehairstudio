<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseResearchService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseResearchController extends Controller
{
    protected DatabaseResearchService $researchService;

    public function __construct(DatabaseResearchService $researchService)
    {
        $this->researchService = $researchService;
    }

    public function index()
    {
        $overview = $this->researchService->getDatabaseOverview();
        $customerAnalytics = $this->researchService->getCustomerAnalytics();
        $bookingAnalytics = $this->researchService->getBookingAnalytics();
        $financialAnalytics = $this->researchService->getFinancialAnalytics();
        $stylistAnalytics = $this->researchService->getStylistAnalytics();
        $serviceAnalytics = $this->researchService->getServiceAnalytics();
        $presetQueries = $this->researchService->getPresetQueries();

        return view('admin.database-research', compact(
            'overview',
            'customerAnalytics',
            'bookingAnalytics',
            'financialAnalytics',
            'stylistAnalytics',
            'serviceAnalytics',
            'presetQueries'
        ));
    }

    public function runQuery(Request $request)
    {
        $request->validate([
            'sql' => 'required|string|max:2000'
        ]);

        $result = $this->researchService->executeReadOnlyQuery($request->sql);

        return response()->json($result);
    }

    public function export(Request $request): StreamedResponse
    {
        $sql = $request->get('sql');
        if (empty($sql)) {
            $sql = "SELECT * FROM bookings ORDER BY id DESC LIMIT 500";
        }

        $result = $this->researchService->executeReadOnlyQuery($sql);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="more_database_research_export_' . date('Y-m-d_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return response()->stream(function () use ($result) {
            $handle = fopen('php://output', 'w');
            if ($result['success'] && !empty($result['columns'])) {
                fputcsv($handle, $result['columns']);
                foreach ($result['rows'] as $row) {
                    fputcsv($handle, array_values($row));
                }
            } else {
                fputcsv($handle, ['Error', $result['message'] ?? 'No data found']);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
