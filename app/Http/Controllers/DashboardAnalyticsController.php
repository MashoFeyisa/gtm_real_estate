<?php

namespace App\Http\Controllers;

use App\Services\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardAnalyticsController extends Controller
{
    public function __construct(
        protected DashboardAnalyticsService $analyticsService
    ) {}

    /**
     * Return JSON data for dashboard analytics charts.
     */
    public function data(Request $request): JsonResponse
    {
        $period = (string) $request->query('period', '30d');
        $data = $this->analyticsService->getDashboardData($request->user(), $period);

        return response()->json($data);
    }
}
