<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Campaign;
use App\Models\Commission;
use App\Models\FinancialLedger;
use App\Models\Lease;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AnalyticsService
{
    /**
     * Parse date range filters.
     *
     * @return array{period: string, start: ?Carbon, end: ?Carbon, label: string}
     */
    public function resolveDateRange(string $period = 'all', ?string $from = null, ?string $to = null): array
    {
        $period = strtolower(trim($period));
        $start = null;
        $end = null;
        $label = 'All Time';

        switch ($period) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                $label = 'Today ('.$start->format('M d, Y').')';
                break;

            case 'week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                $label = 'This Week ('.$start->format('M d').' - '.$end->format('M d, Y').')';
                break;

            case 'month':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                $label = 'This Month ('.$start->format('F Y').')';
                break;

            case 'year':
                $start = now()->startOfYear();
                $end = now()->endOfYear();
                $label = 'This Year ('.$start->format('Y').')';
                break;

            case 'custom':
                if ($from && $to) {
                    try {
                        $start = Carbon::parse($from)->startOfDay();
                        $end = Carbon::parse($to)->endOfDay();
                        if ($start->gt($end)) {
                            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                        }
                        $label = $start->format('M d, Y').' - '.$end->format('M d, Y');
                    } catch (\Throwable) {
                        $start = null;
                        $end = null;
                        $period = 'all';
                        $label = 'All Time';
                    }
                } else {
                    $period = 'all';
                    $label = 'All Time';
                }
                break;

            default:
                $period = 'all';
                $label = 'All Time';
                break;
        }

        return [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'label' => $label,
        ];
    }

    /**
     * Calculate KPI metrics.
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array<string, mixed>
     */
    public function getKpiMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $isFiltered = $start !== null && $end !== null;

        // Total Users
        $totalUsersQuery = User::query();
        if ($isFiltered) {
            $totalUsers = (clone $totalUsersQuery)->whereBetween('created_at', [$start, $end])->count();
        } else {
            $totalUsers = $totalUsersQuery->count();
        }
        $allUsersCount = User::count();

        // Total Agents
        $totalAgentsQuery = Agent::query();
        if ($isFiltered) {
            $totalAgents = (clone $totalAgentsQuery)->whereBetween('created_at', [$start, $end])->count();
        } else {
            $totalAgents = (clone $totalAgentsQuery)->where('is_active', true)->count();
        }
        $allAgentsCount = Agent::where('is_active', true)->count();

        // Total Properties
        $propertiesQuery = Property::query();
        if ($isFiltered) {
            $totalProperties = (clone $propertiesQuery)->whereBetween('created_at', [$start, $end])->count();
        } else {
            $totalProperties = $propertiesQuery->count();
        }
        $allPropertiesCount = Property::count();

        // Available Properties
        $availableQuery = Property::query()->where(function ($q) {
            $q->whereIn('status', ['available', 'published'])
                ->orWhere('is_available', true);
        })->whereNotIn('status', ['sold', 'rented', 'draft', 'archived']);

        if ($isFiltered) {
            $availableProperties = (clone $availableQuery)->whereBetween('created_at', [$start, $end])->count();
        } else {
            $availableProperties = $availableQuery->count();
        }

        // Rented Properties (status = rented or active leases count)
        $rentedQuery = Property::query()->where('status', 'rented');
        if ($isFiltered) {
            $rentedProperties = (clone $rentedQuery)->whereBetween('updated_at', [$start, $end])->count();
            if ($rentedProperties === 0) {
                $rentedProperties = Lease::whereBetween('created_at', [$start, $end])->count();
            }
        } else {
            $rentedProperties = $rentedQuery->count();
            if ($rentedProperties === 0) {
                $rentedProperties = Lease::count();
            }
        }

        // Sold Properties
        $soldQuery = Property::query()->where('status', 'sold');
        if ($isFiltered) {
            $soldProperties = (clone $soldQuery)->whereBetween('updated_at', [$start, $end])->count();
            if ($soldProperties === 0) {
                $soldProperties = Order::where('type', 'sale')
                    ->whereIn('status', ['accepted', 'completed'])
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
            }
        } else {
            $soldProperties = $soldQuery->count();
            if ($soldProperties === 0) {
                $soldProperties = Order::where('type', 'sale')
                    ->whereIn('status', ['accepted', 'completed'])
                    ->count();
            }
        }

        // Total Revenue (Payments completed/confirmed)
        $revenueQuery = Payment::query()->whereIn('status', ['completed', 'confirmed']);
        if ($isFiltered) {
            $totalRevenue = (float) (clone $revenueQuery)->whereBetween('created_at', [$start, $end])->sum('amount');
        } else {
            $totalRevenue = (float) $revenueQuery->sum('amount');
        }

        // Total Commission
        $commissionQuery = Commission::query();
        if ($isFiltered) {
            $totalCommission = (float) (clone $commissionQuery)->whereBetween('created_at', [$start, $end])->sum('commission_amount');
        } else {
            $totalCommission = (float) $commissionQuery->sum('commission_amount');
        }

        return [
            'total_users' => $totalUsers,
            'all_users_count' => $allUsersCount,
            'total_agents' => $totalAgents,
            'all_agents_count' => $allAgentsCount,
            'total_properties' => $totalProperties,
            'all_properties_count' => $allPropertiesCount,
            'available_properties' => $availableProperties,
            'rented_properties' => $rentedProperties,
            'sold_properties' => $soldProperties,
            'total_revenue' => $totalRevenue,
            'total_commission' => $totalCommission,
        ];
    }

    /**
     * Line Chart: Monthly Property Sales, Rentals, and Revenue/Commission.
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array{categories: array<string>, sales: array<int>, rentals: array<int>, revenue: array<float>, commission: array<float>}
     */
    public function getMonthlyTrends(array $dateRange): array
    {
        $intervals = $this->buildIntervalBuckets($dateRange);

        $categories = [];
        $salesData = [];
        $rentalsData = [];
        $revenueData = [];
        $commissionData = [];

        foreach ($intervals as $bucket) {
            $bStart = $bucket['start'];
            $bEnd = $bucket['end'];
            $categories[] = $bucket['label'];

            // Sales count in this interval
            $salesCount = Property::query()
                ->where('status', 'sold')
                ->whereBetween('updated_at', [$bStart, $bEnd])
                ->count();

            $orderSalesCount = Order::query()
                ->where('type', 'sale')
                ->whereIn('status', ['accepted', 'completed'])
                ->whereBetween('created_at', [$bStart, $bEnd])
                ->count();

            $salesData[] = max($salesCount, $orderSalesCount);

            // Rentals count in this interval
            $rentalsCount = Property::query()
                ->where('status', 'rented')
                ->whereBetween('updated_at', [$bStart, $bEnd])
                ->count();

            $leasesCount = Lease::query()
                ->whereBetween('created_at', [$bStart, $bEnd])
                ->count();

            $rentalsData[] = max($rentalsCount, $leasesCount);

            // Revenue in this interval
            $rev = (float) Payment::query()
                ->whereIn('status', ['completed', 'confirmed'])
                ->whereBetween('created_at', [$bStart, $bEnd])
                ->sum('amount');
            $revenueData[] = round($rev, 2);

            // Commission in this interval
            $comm = (float) Commission::query()
                ->whereBetween('created_at', [$bStart, $bEnd])
                ->sum('commission_amount');
            $commissionData[] = round($comm, 2);
        }

        return [
            'categories' => $categories,
            'sales' => $salesData,
            'rentals' => $rentalsData,
            'revenue' => $revenueData,
            'commission' => $commissionData,
        ];
    }

    /**
     * Bar Chart: Agent Performance (properties sold and rented by each agent, sorted by performance).
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array{agents: array<string>, sold: array<int>, rented: array<int>, totals: array<int>, details: array<array<string, mixed>>}
     */
    public function getAgentPerformance(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $isFiltered = $start !== null && $end !== null;

        $agents = Agent::query()
            ->with(['account', 'branch'])
            ->get();

        $performance = $agents->map(function (Agent $agent) use ($start, $end, $isFiltered) {
            // Sold by agent
            $soldPropQuery = Property::where('agent_id', $agent->id)->where('status', 'sold');
            $soldOrderQuery = Order::where('agent_id', $agent->id)->where('type', 'sale')->whereIn('status', ['accepted', 'completed']);

            if ($isFiltered) {
                $soldCount = $soldPropQuery->whereBetween('updated_at', [$start, $end])->count();
                $soldOrderCount = $soldOrderQuery->whereBetween('created_at', [$start, $end])->count();
            } else {
                $soldCount = $soldPropQuery->count();
                $soldOrderCount = $soldOrderQuery->count();
            }
            $totalSold = max($soldCount, $soldOrderCount);

            // Rented by agent
            $rentedPropQuery = Property::where('agent_id', $agent->id)->where('status', 'rented');
            $leaseQuery = Lease::where('agent_id', $agent->id);
            $rentOrderQuery = Order::where('agent_id', $agent->id)->where('type', 'rent')->whereIn('status', ['accepted', 'completed']);

            if ($isFiltered) {
                $rentedCount = $rentedPropQuery->whereBetween('updated_at', [$start, $end])->count();
                $leaseCount = $leaseQuery->whereBetween('created_at', [$start, $end])->count();
                $rentOrderCount = $rentOrderQuery->whereBetween('created_at', [$start, $end])->count();
            } else {
                $rentedCount = $rentedPropQuery->count();
                $leaseCount = $leaseQuery->count();
                $rentOrderCount = $rentOrderQuery->count();
            }
            $totalRented = max($rentedCount, $leaseCount, $rentOrderCount);

            // Total assigned properties
            $propQuery = Property::where('agent_id', $agent->id);
            if ($isFiltered) {
                $propertiesCount = $propQuery->whereBetween('created_at', [$start, $end])->count();
            } else {
                $propertiesCount = $propQuery->count();
            }

            $totalDeals = $totalSold + $totalRented;

            return [
                'id' => $agent->id,
                'name' => $agent->name ?: ('Agent #'.$agent->id),
                'email' => $agent->email ?? ($agent->account?->email ?? ''),
                'branch' => $agent->branch?->name ?? 'Headquarters',
                'properties_count' => $propertiesCount,
                'sold' => $totalSold,
                'rented' => $totalRented,
                'total_deals' => $totalDeals,
            ];
        });

        // Sort agents by performance (total deals desc, then properties count desc, then name asc)
        $sorted = $performance->sort(function ($a, $b) {
            if ($a['total_deals'] !== $b['total_deals']) {
                return $b['total_deals'] <=> $a['total_deals'];
            }
            if ($a['properties_count'] !== $b['properties_count']) {
                return $b['properties_count'] <=> $a['properties_count'];
            }

            return strcmp($a['name'], $b['name']);
        })->values();

        return [
            'agents' => $sorted->pluck('name')->toArray(),
            'sold' => $sorted->pluck('sold')->toArray(),
            'rented' => $sorted->pluck('rented')->toArray(),
            'totals' => $sorted->pluck('total_deals')->toArray(),
            'details' => $sorted->toArray(),
        ];
    }

    /**
     * Donut Chart: Property Status Breakdown (Available, Rented, Sold) with Percentages & Totals.
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array{series: array<int>, labels: array<string>, totals: int, percentages: array<string, float>}
     */
    public function getPropertyStatusBreakdown(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $isFiltered = $start !== null && $end !== null;

        $availableQuery = Property::query()->where(function ($q) {
            $q->whereIn('status', ['available', 'published'])
                ->orWhere('is_available', true);
        })->whereNotIn('status', ['sold', 'rented', 'draft', 'archived']);

        $rentedQuery = Property::query()->where('status', 'rented');
        $soldQuery = Property::query()->where('status', 'sold');

        if ($isFiltered) {
            $available = $availableQuery->whereBetween('created_at', [$start, $end])->count();
            $rented = $rentedQuery->whereBetween('updated_at', [$start, $end])->count();
            if ($rented === 0) {
                $rented = Lease::whereBetween('created_at', [$start, $end])->count();
            }
            $sold = $soldQuery->whereBetween('updated_at', [$start, $end])->count();
            if ($sold === 0) {
                $sold = Order::where('type', 'sale')->whereIn('status', ['accepted', 'completed'])->whereBetween('created_at', [$start, $end])->count();
            }
        } else {
            $available = $availableQuery->count();
            $rented = $rentedQuery->count();
            if ($rented === 0) {
                $rented = Lease::count();
            }
            $sold = $soldQuery->count();
            if ($sold === 0) {
                $sold = Order::where('type', 'sale')->whereIn('status', ['accepted', 'completed'])->count();
            }
        }

        $total = $available + $rented + $sold;

        $percentages = [
            'available' => $total > 0 ? round(($available / $total) * 100, 1) : 0.0,
            'rented' => $total > 0 ? round(($rented / $total) * 100, 1) : 0.0,
            'sold' => $total > 0 ? round(($sold / $total) * 100, 1) : 0.0,
        ];

        return [
            'series' => [$available, $rented, $sold],
            'labels' => ['Available', 'Rented', 'Sold'],
            'counts' => [
                'available' => $available,
                'rented' => $rented,
                'sold' => $sold,
            ],
            'totals' => $total,
            'percentages' => $percentages,
        ];
    }

    /**
     * Stacked Bar Chart: Compare Rent vs Sale transactions by month.
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array{categories: array<string>, sale_deals: array<int>, rent_deals: array<int>}
     */
    public function getRentVsSaleTransactions(array $dateRange): array
    {
        $intervals = $this->buildIntervalBuckets($dateRange);

        $categories = [];
        $saleDeals = [];
        $rentDeals = [];

        foreach ($intervals as $bucket) {
            $bStart = $bucket['start'];
            $bEnd = $bucket['end'];
            $categories[] = $bucket['label'];

            // Sales in this interval
            $salesCount = Property::query()
                ->where('status', 'sold')
                ->whereBetween('updated_at', [$bStart, $bEnd])
                ->count();
            $orderSalesCount = Order::query()
                ->where('type', 'sale')
                ->whereIn('status', ['accepted', 'completed'])
                ->whereBetween('created_at', [$bStart, $bEnd])
                ->count();
            $saleDeals[] = max($salesCount, $orderSalesCount);

            // Rents in this interval
            $rentCount = Property::query()
                ->where('status', 'rented')
                ->whereBetween('updated_at', [$bStart, $bEnd])
                ->count();
            $leaseCount = Lease::query()
                ->whereBetween('created_at', [$bStart, $bEnd])
                ->count();
            $rentOrderCount = Order::query()
                ->where('type', 'rent')
                ->whereIn('status', ['accepted', 'completed'])
                ->whereBetween('created_at', [$bStart, $bEnd])
                ->count();
            $rentDeals[] = max($rentCount, $leaseCount, $rentOrderCount);
        }

        return [
            'categories' => $categories,
            'sale_deals' => $saleDeals,
            'rent_deals' => $rentDeals,
        ];
    }

    /**
     * Location Chart: Number of properties by city/location.
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array{cities: array<string>, counts: array<int>, raw: array<array{city: string, count: int}>}
     */
    public function getPropertiesByLocation(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $isFiltered = $start !== null && $end !== null;

        $query = Property::query();
        if ($isFiltered) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $properties = $query->select('city')->get();

        $cityCounts = [];
        foreach ($properties as $prop) {
            $city = $this->normalizeCityName($prop->city);
            $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
        }

        // Sort descending by count
        arsort($cityCounts);

        $cities = array_keys($cityCounts);
        $counts = array_values($cityCounts);

        $raw = [];
        foreach ($cityCounts as $city => $cnt) {
            $raw[] = [
                'city' => $city,
                'count' => $cnt,
            ];
        }

        return [
            'cities' => $cities,
            'counts' => $counts,
            'raw' => $raw,
        ];
    }

    /**
     * Advanced Analytics: Property Price vs Property Area using a Scatter Chart.
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array{sale_points: array<array{x: float, y: float, name: string, city: string, type: string}>, rent_points: array<array{x: float, y: float, name: string, city: string, type: string}>, total_points: int}
     */
    public function getPriceVsAreaScatter(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $isFiltered = $start !== null && $end !== null;

        $query = Property::query()
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->whereNotNull('area')
            ->where('area', '>', 0);

        if ($isFiltered) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $properties = $query->get(['id', 'title', 'price', 'area', 'type', 'city']);

        $salePoints = [];
        $rentPoints = [];

        foreach ($properties as $p) {
            $point = [
                'x' => (float) $p->area,
                'y' => (float) $p->price,
                'name' => $p->title ?: ('Property #'.$p->id),
                'city' => $this->normalizeCityName($p->city),
                'type' => ucfirst((string) ($p->type ?: 'Property')),
            ];

            if (strtolower((string) $p->type) === 'sale') {
                $salePoints[] = $point;
            } else {
                $rentPoints[] = $point;
            }
        }

        return [
            'sale_points' => $salePoints,
            'rent_points' => $rentPoints,
            'total_points' => count($salePoints) + count($rentPoints),
        ];
    }

    /**
     * Helper to normalize city names.
     */
    protected function normalizeCityName(?string $city): string
    {
        if (empty($city)) {
            return 'Other / Central';
        }

        $clean = trim(strtolower($city));
        $clean = str_replace(['ethiopia', 'ethiopian', ','], '', $clean);
        $clean = trim($clean);

        if (empty($clean)) {
            return 'Other / Central';
        }

        return ucwords($clean);
    }

    /**
     * Build appropriate time bucket intervals based on date range.
     *
     * @param  array{period: string, start: ?Carbon, end: ?Carbon, label: string}  $dateRange
     * @return array<array{label: string, start: Carbon, end: Carbon}>
     */
    protected function buildIntervalBuckets(array $dateRange): array
    {
        $period = $dateRange['period'];
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $buckets = [];

        if ($period === 'today') {
            // 4-hour intervals throughout the day
            $nowStart = now()->startOfDay();
            for ($hour = 0; $hour < 24; $hour += 4) {
                $bStart = $nowStart->copy()->addHours($hour);
                $bEnd = $bStart->copy()->addHours(3)->endOfHour();
                $buckets[] = [
                    'label' => sprintf('%02d:00 - %02d:00', $hour, $hour + 4),
                    'start' => $bStart,
                    'end' => $bEnd,
                ];
            }

            return $buckets;
        }

        if ($period === 'week') {
            // Days of the current week (7 days)
            $wStart = now()->startOfWeek();
            for ($d = 0; $d < 7; $d++) {
                $day = $wStart->copy()->addDays($d);
                $buckets[] = [
                    'label' => $day->format('D, M j'),
                    'start' => $day->copy()->startOfDay(),
                    'end' => $day->copy()->endOfDay(),
                ];
            }

            return $buckets;
        }

        if ($period === 'month') {
            // Weeks or 5-day intervals of the current month
            $mStart = now()->startOfMonth();
            $mEnd = now()->endOfMonth();
            $cursor = $mStart->copy();
            $weekNum = 1;

            while ($cursor->lte($mEnd)) {
                $weekEnd = $cursor->copy()->endOfWeek();
                if ($weekEnd->gt($mEnd)) {
                    $weekEnd = $mEnd->copy();
                }

                $buckets[] = [
                    'label' => 'Wk '.$weekNum.' ('.$cursor->format('M j').'-'.$weekEnd->format('j').')',
                    'start' => $cursor->copy()->startOfDay(),
                    'end' => $weekEnd->copy()->endOfDay(),
                ];

                $cursor = $weekEnd->copy()->addDay();
                $weekNum++;
            }

            return $buckets;
        }

        if ($period === 'custom' && $start && $end) {
            $diffDays = $start->diffInDays($end);

            if ($diffDays <= 1) {
                for ($hour = 0; $hour < 24; $hour += 4) {
                    $bStart = $start->copy()->addHours($hour);
                    $bEnd = $bStart->copy()->addHours(3)->endOfHour();
                    $buckets[] = [
                        'label' => sprintf('%02d:00', $hour),
                        'start' => $bStart,
                        'end' => $bEnd,
                    ];
                }

                return $buckets;
            }

            if ($diffDays <= 14) {
                // Daily buckets
                $cursor = $start->copy();
                while ($cursor->lte($end)) {
                    $buckets[] = [
                        'label' => $cursor->format('M d'),
                        'start' => $cursor->copy()->startOfDay(),
                        'end' => $cursor->copy()->endOfDay(),
                    ];
                    $cursor->addDay();
                }

                return $buckets;
            }

            if ($diffDays <= 90) {
                // Weekly buckets
                $cursor = $start->copy();
                while ($cursor->lte($end)) {
                    $next = $cursor->copy()->addDays(6);
                    if ($next->gt($end)) {
                        $next = $end->copy();
                    }
                    $buckets[] = [
                        'label' => $cursor->format('M d').' - '.$next->format('M d'),
                        'start' => $cursor->copy()->startOfDay(),
                        'end' => $next->copy()->endOfDay(),
                    ];
                    $cursor = $next->copy()->addDay();
                }

                return $buckets;
            }

            // Otherwise monthly buckets
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $bEnd = $cursor->copy()->endOfMonth();
                if ($bEnd->gt($end)) {
                    $bEnd = $end->copy();
                }
                $buckets[] = [
                    'label' => $cursor->format('M Y'),
                    'start' => $cursor->copy()->startOfDay(),
                    'end' => $bEnd->copy()->endOfDay(),
                ];
                $cursor->addMonth()->startOfMonth();
            }

            return $buckets;
        }

        // Default 'all' or 'year': Rolling 12 months (or months of this year)
        if ($period === 'year') {
            for ($m = 1; $m <= 12; $m++) {
                $mDate = Carbon::create(now()->year, $m, 1);
                $buckets[] = [
                    'label' => $mDate->format('M Y'),
                    'start' => $mDate->copy()->startOfMonth(),
                    'end' => $mDate->copy()->endOfMonth(),
                ];
            }

            return $buckets;
        }

        // 'all': Last 12 months up to current month
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $buckets[] = [
                'label' => $month->format('M Y'),
                'start' => $month->copy()->startOfMonth(),
                'end' => $month->copy()->endOfMonth(),
            ];
        }

        return $buckets;
    }

    /**
     * Get chart dataset for Admin Overview section.
     *
     * @return array<string, mixed>
     */
    public function getAdminOverviewChartData(): array
    {
        $range = $this->resolveDateRange('all');

        return [
            'monthly_trends' => $this->getMonthlyTrends($range),
            'property_status' => $this->getPropertyStatusBreakdown($range),
            'agent_performance' => $this->getAgentPerformance($range),
            'locations' => $this->getPropertiesByLocation($range),
            'rent_vs_sale' => $this->getRentVsSaleTransactions($range),
        ];
    }

    /**
     * Get chart dataset for Campaign Desk Overview.
     *
     * @return array<string, mixed>
     */
    public function getCampaignChartData(): array
    {
        $statuses = [
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'published' => 'Published',
            'draft' => 'Draft',
            'archived' => 'Archived',
        ];

        $statusCounts = [];
        $statusLabels = [];
        $totalCampaigns = 0;

        foreach ($statuses as $key => $label) {
            $count = Campaign::where('status', $key)->count();
            $statusCounts[] = $count;
            $statusLabels[] = $label;
            $totalCampaigns += $count;
        }

        // Top Campaigns by reach / leads
        $topCampaigns = Campaign::withCount('leads')
            ->orderByDesc('views_count')
            ->limit(7)
            ->get();

        $campaignTitles = [];
        $campaignViews = [];
        $campaignLeads = [];

        foreach ($topCampaigns as $c) {
            $campaignTitles[] = Str::limit($c->title, 20);
            $campaignViews[] = (int) $c->views_count;
            $campaignLeads[] = (int) $c->leads_count;
        }

        return [
            'total_campaigns' => $totalCampaigns,
            'status_donut' => [
                'series' => $statusCounts,
                'labels' => $statusLabels,
                'has_data' => $totalCampaigns > 0,
            ],
            'performance_bar' => [
                'titles' => $campaignTitles,
                'views' => $campaignViews,
                'leads' => $campaignLeads,
                'has_data' => count($topCampaigns) > 0,
            ],
        ];
    }

    /**
     * Get chart dataset for Finance Office Overview.
     *
     * @return array<string, mixed>
     */
    public function getFinanceChartData(): array
    {
        // Monthly Cash Inflow vs Outflow (last 6 months)
        $months = [];
        $inflows = [];
        $outflows = [];

        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $start = $m->copy()->startOfMonth();
            $end = $m->copy()->endOfMonth();

            $months[] = $m->format('M Y');

            $in = (float) FinancialLedger::where('entry_type', 'credit')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount');
            $out = (float) FinancialLedger::where('entry_type', 'debit')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount');

            $inflows[] = round($in, 2);
            $outflows[] = round($out, 2);
        }

        // Revenue Breakdown by Categories
        $categories = [
            'rental_revenue' => 'Rental Revenue',
            'earnest_deposit' => 'Earnest Deposits',
            'booking_revenue' => 'Stay Bookings',
            'platform_fee' => 'Platform Fees',
            'developer_deposit' => 'Developer Deposits',
        ];

        $categorySeries = [];
        $categoryLabels = [];
        $totalRevenue = 0;

        foreach ($categories as $catKey => $catLabel) {
            $sum = (float) FinancialLedger::where('entry_type', 'credit')
                ->where('category', $catKey)
                ->sum('amount');
            $categorySeries[] = round($sum, 2);
            $categoryLabels[] = $catLabel;
            $totalRevenue += $sum;
        }

        // Rent Collection Breakdown: Paid vs Overdue vs Upcoming
        $paidRent = (float) RentSchedule::where('status', 'paid')->sum('amount_paid');
        $overdueRent = (float) RentSchedule::where('status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->sum('amount_due');
        $upcomingRent = (float) RentSchedule::where('status', '!=', 'paid')
            ->where('due_date', '>=', now()->toDateString())
            ->sum('amount_due');
        $totalRentTarget = $paidRent + $overdueRent + $upcomingRent;

        return [
            'cashflow' => [
                'months' => $months,
                'inflows' => $inflows,
                'outflows' => $outflows,
                'has_data' => array_sum($inflows) > 0 || array_sum($outflows) > 0,
            ],
            'categories_donut' => [
                'series' => $categorySeries,
                'labels' => $categoryLabels,
                'total' => round($totalRevenue, 2),
                'has_data' => $totalRevenue > 0,
            ],
            'rent_collection' => [
                'series' => [round($paidRent, 2), round($overdueRent, 2), round($upcomingRent, 2)],
                'labels' => ['Collected Paid', 'Overdue Rent', 'Upcoming Rent'],
                'total' => round($totalRentTarget, 2),
                'has_data' => $totalRentTarget > 0,
            ],
        ];
    }

    /**
     * Get chart dataset for Agent Portal Overview.
     *
     * @return array<string, mixed>
     */
    public function getAgentChartData(Agent $agent): array
    {
        // 1. Agent Property Status Breakdown
        $available = $agent->properties()->whereIn('status', ['available', 'published'])->count();
        $rented = $agent->properties()->where('status', 'rented')->count();
        if ($rented === 0) {
            $rented = $agent->leases()->count();
        }
        $sold = $agent->properties()->where('status', 'sold')->count();
        if ($sold === 0) {
            $sold = $agent->orders()->where('type', 'sale')->whereIn('status', ['accepted', 'completed'])->count();
        }
        $draft = $agent->properties()->whereIn('status', ['draft', 'archived'])->count();

        $propTotal = $available + $rented + $sold + $draft;

        // 2. Agent Monthly Activity (Orders & Viewings last 6 months)
        $months = [];
        $ordersCount = [];
        $viewingsCount = [];

        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $start = $m->copy()->startOfMonth();
            $end = $m->copy()->endOfMonth();

            $months[] = $m->format('M Y');
            $ordersCount[] = $agent->orders()->whereBetween('created_at', [$start, $end])->count();
            $viewingsCount[] = $agent->viewings()->whereBetween('created_at', [$start, $end])->count();
        }

        // 3. Agent Commissions: Paid vs Pending
        $commPaid = (float) Commission::where(function ($q) use ($agent) {
            $q->where('agent_id', $agent->id)
                ->orWhere('listing_agent_id', $agent->id)
                ->orWhere('selling_agent_id', $agent->id)
                ->orWhere('referral_agent_id', $agent->id);
        })->where('status', 'paid')->sum('commission_amount');

        $commPending = (float) Commission::where(function ($q) use ($agent) {
            $q->where('agent_id', $agent->id)
                ->orWhere('listing_agent_id', $agent->id)
                ->orWhere('selling_agent_id', $agent->id)
                ->orWhere('referral_agent_id', $agent->id);
        })->where('status', '!=', 'paid')->sum('commission_amount');

        $totalComm = $commPaid + $commPending;

        return [
            'property_status' => [
                'series' => [$available, $rented, $sold, $draft],
                'labels' => ['Available', 'Rented', 'Sold', 'Draft'],
                'total' => $propTotal,
                'has_data' => $propTotal > 0,
            ],
            'monthly_activity' => [
                'months' => $months,
                'orders' => $ordersCount,
                'viewings' => $viewingsCount,
                'has_data' => array_sum($ordersCount) > 0 || array_sum($viewingsCount) > 0,
            ],
            'commission_breakdown' => [
                'series' => [round($commPaid, 2), round($commPending, 2)],
                'labels' => ['Paid Commissions', 'Pending Commissions'],
                'total' => round($totalComm, 2),
                'has_data' => $totalComm > 0,
            ],
        ];
    }
}
