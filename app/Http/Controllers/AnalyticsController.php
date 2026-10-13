<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Render the admin analytics dashboard view.
     */
    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $period = (string) $request->input('period', 'all');
        $from = $request->input('from');
        $to = $request->input('to');

        $dateRange = $this->analyticsService->resolveDateRange($period, $from, $to);
        $payload = $this->buildAnalyticsPayload($dateRange);

        $siteBrand = [
            'name' => SiteSetting::get('site_name', 'Seyid Real Estate'),
            'logoUrl' => SiteSetting::get('site_logo_url', asset('favicon.ico')),
        ];

        return view('dashboard.analytics', [
            'siteBrand' => $siteBrand,
            'currentPeriod' => $dateRange['period'],
            'dateRangeLabel' => $dateRange['label'],
            'from' => $from,
            'to' => $to,
            'payload' => $payload,
        ]);
    }

    /**
     * Return JSON chart data for dynamic date filtering via AJAX.
     */
    public function chartData(Request $request): JsonResponse
    {
        $this->authorizeAccess($request);

        $period = (string) $request->input('period', 'all');
        $from = $request->input('from');
        $to = $request->input('to');

        $dateRange = $this->analyticsService->resolveDateRange($period, $from, $to);
        $payload = $this->buildAnalyticsPayload($dateRange);

        return response()->json([
            'success' => true,
            'date_range' => [
                'period' => $dateRange['period'],
                'label' => $dateRange['label'],
                'start' => $dateRange['start']?->toDateString(),
                'end' => $dateRange['end']?->toDateString(),
            ],
            'data' => $payload,
        ]);
    }

    /**
     * Check if the authenticated user has access to analytics.
     */
    protected function authorizeAccess(Request $request): void
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // Allow Admin, Company executive, or Finance Officer
        $hasAccess = (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isCompany') && $user->isCompany())
            || (method_exists($user, 'isFinanceOfficer') && $user->isFinanceOfficer())
            || in_array($user->role, ['admin', 'company', 'finance_officer'], true);

        if (! $hasAccess) {
            abort(403, 'Unauthorized access to Analytics Dashboard.');
        }
    }

    /**
     * Aggregate full data payload for given date range.
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array<string, mixed>
     */
    protected function buildAnalyticsPayload(array $dateRange): array
    {
        return [
            'kpis' => $this->analyticsService->getKpiMetrics($dateRange),
            'monthly_trends' => $this->analyticsService->getMonthlyTrends($dateRange),
            'agent_performance' => $this->analyticsService->getAgentPerformance($dateRange),
            'property_status' => $this->analyticsService->getPropertyStatusBreakdown($dateRange),
            'rent_vs_sale' => $this->analyticsService->getRentVsSaleTransactions($dateRange),
            'locations' => $this->analyticsService->getPropertiesByLocation($dateRange),
            'scatter' => $this->analyticsService->getPriceVsAreaScatter($dateRange),
        ];
    }
}
