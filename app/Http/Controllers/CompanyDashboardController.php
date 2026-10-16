<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\CompanyWhiteLabel;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyOffer;
use App\Models\PropertyReport;
use App\Models\RentSchedule;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        if (! $user || ! $user->canAccessCompanyDashboard() || $user->isAdmin()) {
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
        $companyWhiteLabel = CompanyWhiteLabel::where('user_id', $user->id)->first();
        $bankAccounts = BankAccount::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('dashboards.company', compact(
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
            'siteBrand',
            'companyWhiteLabel',
            'bankAccounts'
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

    /**
     * Store a new corporate agent.
     */
    public function storeAgent(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard() || $user->isAdmin()) {
            abort(403);
        }
        if (! $user->hasPermission('agents.create')) {
            abort(403, 'Permission denied: agents.create');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $agentUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password'] ?? 'AgentPass123!'),
            'role' => 'agent',
        ]);

        $agent = Agent::create([
            'user_id' => $agentUser->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            // `agents.commission_rate` is the company's internal split share
            // (the form labels it "commission split ratios", default 70%), not
            // the platform commission rate. It must not be replaced by the
            // platform default rate - that would silently rewrite the company's
            // share. The platform commission rate lives in FinanceSettings.
            'commission_rate' => $validated['commission_rate'] ?? 70.0,
            'license_number' => $validated['license_number'] ?? null,
            'is_active' => true,
            'license_verified' => true,
        ]);

        AuditLog::log(action: 'agent.created', entity: $agent, reason: "Company created agent {$agent->name} (#{$agent->id})", user: $user);

        return redirect()->route('company.dashboard', ['section' => 'agents'])->with('success', "Agent {$agent->name} added successfully.");
    }

    /**
     * Update an existing corporate agent.
     */
    public function updateAgent(Request $request, Agent $agent): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard() || $user->isAdmin()) {
            abort(403);
        }
        if (! $user->hasPermission('agents.update')) {
            abort(403, 'Permission denied: agents.update');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$agent->user_id],
            'phone' => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
        ]);

        $agent->update($validated);
        if ($agent->user) {
            $agent->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);
        }

        AuditLog::log(action: 'agent.updated', entity: $agent, reason: "Company updated agent {$agent->name} (#{$agent->id})", user: $user);

        return redirect()->route('company.dashboard', ['section' => 'agents'])->with('success', "Agent {$agent->name} updated successfully.");
    }

    /**
     * Toggle agent active status.
     */
    public function toggleAgentActive(Request $request, Agent $agent): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard() || $user->isAdmin()) {
            abort(403);
        }
        if (! $user->hasPermission('agents.update')) {
            abort(403, 'Permission denied: agents.update');
        }

        $agent->update(['is_active' => ! $agent->is_active]);

        AuditLog::log(action: 'agent.toggled', entity: $agent, reason: "Company toggled active status for agent {$agent->name} (#{$agent->id})", user: $user);

        return redirect()->route('company.dashboard', ['section' => 'agents'])->with('success', "Agent {$agent->name} status changed.");
    }

    /**
     * Delete an agent.
     */
    public function deleteAgent(Request $request, Agent $agent): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard() || $user->isAdmin()) {
            abort(403);
        }
        if (! $user->hasPermission('agents.delete')) {
            abort(403, 'Permission denied: agents.delete');
        }

        AuditLog::log(action: 'agent.deleted', entity: $agent, reason: "Company removed agent {$agent->name} (#{$agent->id})", user: $user);
        $agent->delete();

        return redirect()->route('company.dashboard', ['section' => 'agents'])->with('success', 'Agent removed successfully.');
    }

    /**
     * Reassign a client to another agent or branch.
     */
    public function reassignClient(Request $request, User $client): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard() || $user->isAdmin()) {
            abort(403);
        }
        if (! $user->hasPermission('clients.update')) {
            abort(403, 'Permission denied: clients.update');
        }

        $validated = $request->validate([
            'agent_id' => ['nullable', 'exists:agents,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        if (! empty($validated['agent_id'])) {
            Inquiry::where('email', $client->email)->orWhere('user_id', $client->id)->update(['agent_id' => $validated['agent_id']]);
            Order::where('email', $client->email)->orWhere('user_id', $client->id)->update(['agent_id' => $validated['agent_id']]);
        }

        AuditLog::log(action: 'client.reassigned', entity: $client, reason: "Reassigned client {$client->name} (#{$client->id}) to agent {$validated['agent_id']}", user: $user);

        return redirect()->route('company.dashboard', ['section' => 'clients'])->with('success', "Client {$client->name} reassigned successfully.");
    }

    /**
     * Merge duplicate property listings.
     */
    public function mergeListings(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard() || $user->isAdmin()) {
            abort(403);
        }
        if (! $user->hasPermission('properties.merge')) {
            abort(403, 'Permission denied: properties.merge');
        }

        $validated = $request->validate([
            'primary_property_id' => ['required', 'exists:properties,id'],
            'duplicate_property_id' => ['required', 'exists:properties,id', 'different:primary_property_id'],
        ]);

        $primary = Property::findOrFail($validated['primary_property_id']);
        $duplicate = Property::findOrFail($validated['duplicate_property_id']);

        DB::transaction(function () use ($primary, $duplicate, $user) {
            Inquiry::where('property_id', $duplicate->id)->update(['property_id' => $primary->id]);
            PropertyOffer::where('property_id', $duplicate->id)->update(['property_id' => $primary->id]);
            Order::where('property_id', $duplicate->id)->update(['property_id' => $primary->id]);

            $duplicate->update([
                'status' => 'archived',
                'is_active' => false,
                'title' => $duplicate->title.' [Merged into #'.$primary->id.']',
            ]);

            AuditLog::log(action: 'property.merged', entity: $primary, reason: "Merged duplicate property #{$duplicate->id} into primary #{$primary->id}", user: $user);
        });

        return redirect()->route('company.dashboard', ['section' => 'listings'])->with('success', "Property #{$duplicate->id} successfully merged into #{$primary->id}.");
    }

    /**
     * Update company white label subscription tier.
     */
    public function updateSubscription(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessCompanyDashboard() || $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'subscription_tier' => ['required', 'in:standard,premium,enterprise'],
        ]);

        $whiteLabel = CompanyWhiteLabel::firstOrNew(['user_id' => $user->id]);
        if (! $whiteLabel->exists) {
            $slug = Str::slug($user->name ?: 'company').'-'.$user->id;
            $whiteLabel->company_name = $user->name ?: 'Corporate Real Estate';
            $whiteLabel->brand_name = $user->name ?: 'Corporate Real Estate';
            $whiteLabel->subdomain = $slug;
        }
        $whiteLabel->subscription_tier = $validated['subscription_tier'];
        $whiteLabel->subscription_expires_at = now()->addYear();
        $whiteLabel->save();

        AuditLog::log(action: 'company.subscription_updated', entity: $whiteLabel, reason: "Company updated subscription tier to {$validated['subscription_tier']}", user: $user);

        return redirect()->route('company.dashboard', ['section' => 'subscription'])->with('success', 'Company subscription updated to '.ucfirst($validated['subscription_tier']).'.');
    }
}
