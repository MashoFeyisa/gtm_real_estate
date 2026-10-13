<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyReport;
use App\Models\RentSchedule;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CompanyDashboardController extends Controller
{
    /**
     * Display the GTM Real Estate Company Executive Dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessCompanyDashboard()) {
            abort(403, 'Unauthorized access to GTM Real Estate Company Dashboard.');
        }

        // 1. Branches
        $branches = Branch::query()
            ->with(['agents', 'properties'])
            ->latest()
            ->paginate(12, ['*'], 'branches_page')
            ->withQueryString();

        // 2. Agents
        $agents = Agent::query()
            ->with(['user', 'branch', 'properties'])
            ->latest()
            ->paginate(12, ['*'], 'agents_page')
            ->withQueryString();

        // 3. Listings
        $listings = Listing::query()
            ->with(['property.images'])
            ->latest()
            ->paginate(12, ['*'], 'listings_page')
            ->withQueryString();

        // 4. Properties & Projects
        $properties = Property::query()
            ->with(['images', 'agent.user', 'branch'])
            ->latest()
            ->paginate(12, ['*'], 'properties_page')
            ->withQueryString();
        $projects = Project::query()
            ->with(['units', 'milestones', 'buildings'])
            ->latest()
            ->get();

        // 5. Clients CRM
        $clients = User::query()
            ->whereIn('role', ['client', 'buyer', 'tenant', 'user'])
            ->withCount(['orders', 'propertyLikes', 'propertyFavorites'])
            ->latest()
            ->paginate(12, ['*'], 'clients_page')
            ->withQueryString();

        // 6. Deals & Transactions
        $orders = Order::query()
            ->with(['property', 'agent'])
            ->latest()
            ->paginate(12, ['*'], 'orders_page')
            ->withQueryString();
        $leases = Lease::query()
            ->with(['property', 'agent', 'schedules.payment'])
            ->latest()
            ->paginate(12, ['*'], 'leases_page')
            ->withQueryString();
        $rentSchedules = RentSchedule::query()
            ->with(['lease.property'])
            ->latest()
            ->get();
        $commissions = Commission::query()
            ->with(['agent', 'order', 'property'])
            ->latest()
            ->paginate(12, ['*'], 'commissions_page')
            ->withQueryString();
        $payments = Payment::query()
            ->with(['property', 'user', 'verifier'])
            ->latest()
            ->take(50)
            ->get();

        // 7. Intelligence & Reports
        $reports = PropertyReport::query()
            ->with(['property', 'user', 'resolver'])
            ->latest()
            ->paginate(12, ['*'], 'reports_page')
            ->withQueryString();

        $companyKpis = [
            'totalBranches' => Branch::count(),
            'activeBranches' => Branch::where('status', 'active')->count(),
            'totalAgents' => Agent::count(),
            'verifiedAgents' => Agent::where('license_verified', true)->count(),
            'totalListings' => Listing::count(),
            'totalProperties' => Property::count(),
            'totalClients' => User::whereIn('role', ['client', 'buyer', 'tenant', 'user'])->count(),
            'totalOrders' => Order::count(),
            'portfolioValuation' => (float) Property::sum('price'),
            'grossSalesVolume' => (float) Order::query()
                ->leftJoin('properties', 'orders.property_id', '=', 'properties.id')
                ->sum(DB::raw('COALESCE(orders.offer_amount, properties.price, 0)')),
            'rentCollected' => (float) RentSchedule::where('status', 'paid')->sum('amount_paid'),
            'commissionsPaid' => (float) Commission::where('status', 'paid')->sum('commission_amount'),
            'pendingSlips' => Payment::where('status', 'pending')->count(),
        ];

        $siteBrand = [
            'name' => SiteSetting::get('site_name', 'GTM Real Estate Company'),
            'logoUrl' => SiteSetting::get('site_logo') ?: asset('images/luxury/hero-skyline.jpg'),
        ];

        // Company listing studio data
        $propertyCategories = PropertyCategory::query()->ordered()->get();
        $companyPropertyToEdit = $request->query('edit_property')
            ? Property::query()->whereKey($request->query('edit_property'))->first()
            : null;

        return view('company.dashboard', compact(
            'user',
            'branches',
            'agents',
            'listings',
            'propertyCategories',
            'companyPropertyToEdit',
            'properties',
            'projects',
            'clients',
            'orders',
            'leases',
            'rentSchedules',
            'commissions',
            'payments',
            'reports',
            'companyKpis',
            'siteBrand'
        ));
    }

    /**
     * Store a new branch office.
     */
    public function storeBranch(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard()) {
            abort(403, 'Unauthorized access to GTM Real Estate Company Dashboard.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'sub_city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'manager_name' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'is_headquarters' => ['nullable', 'boolean'],
        ]);

        $validated['subcity'] = $validated['subcity'] ?? $validated['sub_city'] ?? null;
        unset($validated['sub_city']);

        $validated['is_headquarters'] = $request->boolean('is_headquarters');

        if ($validated['is_headquarters']) {
            Branch::query()->where('is_headquarters', true)->update(['is_headquarters' => false]);
        }

        Branch::create($validated);

        return redirect()->route('company.dashboard', ['section' => 'branches'])->with('success', 'Branch office created successfully.');
    }

    /**
     * Update an existing branch office.
     */
    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard()) {
            abort(403, 'Unauthorized access to GTM Real Estate Company Dashboard.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code,'.$branch->id],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'sub_city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'manager_name' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'is_headquarters' => ['nullable', 'boolean'],
        ]);

        $validated['subcity'] = $validated['subcity'] ?? $validated['sub_city'] ?? null;
        unset($validated['sub_city']);

        $validated['is_headquarters'] = $request->boolean('is_headquarters');

        if ($validated['is_headquarters']) {
            Branch::query()->where('id', '!=', $branch->id)->where('is_headquarters', true)->update(['is_headquarters' => false]);
        }

        $branch->update($validated);

        return redirect()->route('company.dashboard', ['section' => 'branches'])->with('success', 'Branch office updated successfully.');
    }

    /**
     * Delete a branch office.
     */
    public function deleteBranch(Request $request, Branch $branch): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard()) {
            abort(403, 'Unauthorized access to GTM Real Estate Company Dashboard.');
        }

        $branch->delete();

        return redirect()->route('company.dashboard', ['section' => 'branches'])->with('success', 'Branch office removed successfully.');
    }

    /**
     * Register a new client from the company dashboard.
     */
    public function storeClient(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard()) {
            abort(403, 'Unauthorized access to GTM Real Estate Company Dashboard.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'in:client,buyer,tenant,user'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('company.dashboard', ['section' => 'clients'])->with('success', 'Client registered successfully in CRM.');
    }

    /**
     * Update an existing client from the company dashboard.
     */
    public function updateClient(Request $request, User $client): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard()) {
            abort(403, 'Unauthorized access to GTM Real Estate Company Dashboard.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$client->id],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'in:client,buyer,tenant,user'],
        ]);

        $clientData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (filled($validated['password'] ?? null)) {
            $clientData['password'] = Hash::make($validated['password']);
        }

        $client->update($clientData);

        return redirect()->route('company.dashboard', ['section' => 'clients'])->with('success', 'Client updated successfully.');
    }

    /**
     * Delete a client from the company dashboard.
     */
    public function deleteClient(Request $request, User $client): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard()) {
            abort(403, 'Unauthorized access to GTM Real Estate Company Dashboard.');
        }

        $client->delete();

        return redirect()->route('company.dashboard', ['section' => 'clients'])->with('success', 'Client removed successfully from CRM.');
    }
}
