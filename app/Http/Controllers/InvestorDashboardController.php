<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasInvestorPerm = $user?->hasAnyPermission([
            'portfolio.create',
            'yields.view',
            'properties.view',
            'property.view',
        ]);

        if ($user?->status === 'suspended' || ($user?->role !== 'investor' && ! $user?->isAdmin() && ! $hasInvestorPerm)) {
            abort(403, 'Permission denied: Investor portfolio access required.');
        }

        $portfolios = $user->investorPortfolios()->with('property.images')->latest()->get();

        $totalValuation = (float) $portfolios->sum('current_valuation');
        $totalCost = (float) $portfolios->sum('purchase_price');
        $totalMonthlyIncome = (float) $portfolios->sum('monthly_rental_income');
        $totalMonthlyExpenses = (float) $portfolios->sum('monthly_expenses');
        $annualNetIncome = ($totalMonthlyIncome - $totalMonthlyExpenses) * 12;

        $overallGrossYield = $totalValuation > 0
            ? round((($totalMonthlyIncome * 12) / $totalValuation) * 100, 2)
            : 0.0;

        $overallNetYield = $totalValuation > 0
            ? round(($annualNetIncome / $totalValuation) * 100, 2)
            : 0.0;

        // Ethiopian Market Indicators (Real Estate Benchmark Estimates)
        $marketIndicators = [
            [
                'district' => 'Bole (Addis Ababa)',
                'property_type' => 'Luxury Apartments & Commercial',
                'avg_price_sqm' => 125000,
                'cap_rate_estimate' => 8.5,
                'rental_growth' => '+14% YoY',
                'liquidity' => 'High',
            ],
            [
                'district' => 'Kazanchis & CBD (Addis Ababa)',
                'property_type' => 'Diplomatic & Executive Residences',
                'avg_price_sqm' => 110000,
                'cap_rate_estimate' => 9.2,
                'rental_growth' => '+12% YoY',
                'liquidity' => 'High',
            ],
            [
                'district' => 'CMC & Ayat (Addis Ababa)',
                'property_type' => 'Family Gated Communities & Villas',
                'avg_price_sqm' => 78000,
                'cap_rate_estimate' => 7.8,
                'rental_growth' => '+9% YoY',
                'liquidity' => 'Moderate',
            ],
            [
                'district' => 'Hawassa (Lakefront)',
                'property_type' => 'Vacation Rentals & Guesthouses',
                'avg_price_sqm' => 58000,
                'cap_rate_estimate' => 10.4,
                'rental_growth' => '+16% YoY',
                'liquidity' => 'Moderate',
            ],
            [
                'district' => 'Dire Dawa & Harar',
                'property_type' => 'Commercial Trade Corridors',
                'avg_price_sqm' => 45000,
                'cap_rate_estimate' => 8.0,
                'rental_growth' => '+7% YoY',
                'liquidity' => 'Moderate',
            ],
        ];

        return view('dashboards.investor', compact(
            'portfolios',
            'totalValuation',
            'totalCost',
            'totalMonthlyIncome',
            'totalMonthlyExpenses',
            'annualNetIncome',
            'overallGrossYield',
            'overallNetYield',
            'marketIndicators'
        ));
    }

    public function storePortfolio(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user?->status === 'suspended' || ($user?->role !== 'investor' && ! $user?->isAdmin() && ! $user?->hasPermission('properties.view') && ! $user?->hasPermission('property.view'))) {
            abort(403, 'Permission denied: Investor portfolio access required.');
        }

        $validated = $request->validate([
            'asset_name' => ['required', 'string', 'max:255'],
            'asset_type' => ['required', 'in:residential,commercial,mixed,land'],
            'location' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'current_valuation' => ['required', 'numeric', 'min:0'],
            'monthly_rental_income' => ['nullable', 'numeric', 'min:0'],
            'monthly_expenses' => ['nullable', 'numeric', 'min:0'],
            'occupancy_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $portfolio = $user->investorPortfolios()->create($validated);

        AuditLog::log(
            action: 'investor.asset_added',
            entityType: 'InvestorPortfolio',
            entityId: $portfolio->id,
            newValue: $portfolio->toArray(),
            user: $user,
            request: $request
        );

        return back()->with('success', "Asset '{$portfolio->asset_name}' added to investment portfolio.");
    }

    public function storeAsset(Request $request): RedirectResponse
    {
        return $this->storePortfolio($request);
    }
}
