<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Agent;
use App\Models\AgentFeedback;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\Commission;
use App\Models\FinancialLedger;
use App\Models\Inquiry;
use App\Models\JobApplication;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\ListingType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectUnit;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyOffer;
use App\Models\PropertyReport;
use App\Models\PropertyViewing;
use App\Models\RentSchedule;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UnitReservation;
use App\Models\User;
use App\Services\AgreementPdfService;
use App\Services\AnalyticsService;
use App\Services\CommissionSplitService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class WorkerManagementController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        $catalog = $user ? $user->permissionCatalog() : [];
        $hasAdminPerm = ! empty($catalog) && $user->hasAnyPermission([
            'platform.roles_manage',
            'platform.audit_view',
            'platform.users_manage',
            'platform.settings_manage',
        ]);

        if (! $user || (! $user->isAdmin() && ! $hasAdminPerm)) {
            abort(403, 'Access denied.');
        }

        $users = User::query()
            ->with(['permissionOverrides', 'userPermissions', 'roles', 'agentProfile', 'assignedProperty'])
            ->latest()
            ->paginate(12, ['*'], 'users_page')
            ->withQueryString();
        $properties = Property::with(['agent', 'orders'])->latest()->paginate(12, ['*'], 'properties_page')->withQueryString();
        $agents = Agent::query()->with('account')->orderBy('name')->paginate(12, ['*'], 'agents_page')->withQueryString();
        $orders = Order::query()
            ->with(['property', 'agent', 'commission'])
            ->latest()
            ->paginate(12, ['*'], 'orders_page')
            ->withQueryString();
        $agentRequestsToAdmin = Order::query()
            ->with(['property', 'agent', 'commission'])
            ->where(function ($query) {
                $query->where('type', 'rent')
                    ->orWhereHas('property', function ($propQuery) {
                        $propQuery->where('type', 'rent')
                            ->orWhere('status', 'sold')
                            ->orWhere('status', 'rented');
                    })
                    ->orWhere('status', 'accepted');
            })
            ->whereNotNull('submitted_to_admin_at')
            ->latest('submitted_to_admin_at')
            ->get();
        $unreadAdminRequests = $agentRequestsToAdmin->whereNull('admin_viewed_at');

        $testimonials = AgentFeedback::withTrashed()->with('agent')->latest()->paginate(12, ['*'], 'testimonials_page')->withQueryString();
        $trashedTestimonials = AgentFeedback::onlyTrashed()->with('agent')->latest('deleted_at')->get();

        // Commission data
        $commissions = Commission::query()
            ->with(['agent', 'order', 'property'])
            ->latest()
            ->paginate(12, ['*'], 'commissions_page')
            ->withQueryString();
        $pendingCommissionsTotal = Commission::where('status', 'pending')->sum('commission_amount');
        $paidCommissionsTotal = Commission::where('status', 'paid')->sum('commission_amount');

        $inquiries = Inquiry::query()->with('agent')->latest()->paginate(12, ['*'], 'inquiries_page')->withQueryString();
        $notifications = $user->notifications()->latest()->take(20)->get();

        $overviewStats = [
            'totalProperties' => Property::count(),
            'activeListings' => Property::where('is_active', true)->whereNotIn('status', ['draft', 'archived'])->count(),
            'pendingReviews' => Property::where('status', 'draft')->count(),
            'pendingAgentRequests' => $unreadAdminRequests->count(),
            'pendingCommissions' => Commission::where('status', 'pending')->count(),
            'pendingCommissionsTotal' => $pendingCommissionsTotal,
        ];
        $recentProperties = Property::latest()->take(3)->get();

        $postsQuery = BlogPost::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $postsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $postsQuery->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $postsQuery->where('status', $request->status);
        }

        $posts = $postsQuery->paginate(12, ['*'], 'posts_page')->withQueryString();
        $jobs = BlogPost::query()->where('type', 'job')->latest()->paginate(12, ['*'], 'jobs_page')->withQueryString();
        $jobApplications = JobApplication::query()->with('blogPost')->latest()->paginate(12, ['*'], 'applications_page')->withQueryString();
        $pendingApplicationsCount = JobApplication::where('status', 'pending')->count();

        Booking::completeExpiredReservations();

        $bookings = Booking::query()
            ->with('property')
            ->where('status', '!=', 'completed')
            ->latest()
            ->paginate(12, ['*'], 'bookings_page')
            ->withQueryString();
        $overviewStats['totalBookings'] = Booking::where('status', '!=', 'completed')->count();

        $projects = Project::query()->with(['units', 'milestones', 'buildings'])->latest()->get();
        $unitReservations = UnitReservation::query()->with(['project', 'unit'])->latest()->paginate(12, ['*'], 'reservations_page')->withQueryString();
        $overviewStats['totalProjects'] = Project::count();
        $overviewStats['totalProjectUnits'] = ProjectUnit::count();
        $overviewStats['totalUnitReservations'] = UnitReservation::count();

        // Module 4: Property Viewings & Offers
        $viewings = PropertyViewing::query()->with(['property', 'agent'])->latest()->get();
        $offers = PropertyOffer::query()->with(['property', 'agent', 'negotiations'])->latest()->get();
        $overviewStats['totalViewings'] = PropertyViewing::count();
        $overviewStats['pendingViewings'] = PropertyViewing::where('status', 'requested')->count();
        $overviewStats['totalOffers'] = PropertyOffer::count();
        $overviewStats['activeOffers'] = PropertyOffer::whereIn('status', ['pending', 'countered', 'under_review'])->count();

        // Ethiopian Transaction & Payments (SRS §78–§83)
        $pendingPayments = Payment::query()->where('status', 'pending')->latest()->get();
        $pendingPaymentsCount = Payment::where('status', 'pending')->count();
        $overviewStats['pendingPaymentsCount'] = $pendingPaymentsCount;

        // Rental & Lease Management Engine (SRS §84–§88)
        $leases = Lease::query()->with(['property', 'agent', 'schedules.payment'])->latest()->paginate(12, ['*'], 'leases_page')->withQueryString();
        $rentSchedules = RentSchedule::query()->with(['lease.property'])->latest()->get();
        $activeLeasesCount = Lease::where('status', 'active')->count();
        $overdueRentCount = $rentSchedules->filter(fn ($s) => $s->is_overdue)->count();
        $totalRentCollected = (float) RentSchedule::where('status', 'paid')->sum('amount_paid');
        $overviewStats['totalLeases'] = Lease::count();
        $overviewStats['activeLeases'] = $activeLeasesCount;
        $overviewStats['overdueRentCount'] = $overdueRentCount;
        $overviewStats['totalRentCollected'] = $totalRentCollected;

        // Trust & Safety / Client Incident & Misleading Reports
        $reports = PropertyReport::query()->with(['property', 'user', 'resolver'])->latest()->paginate(12, ['*'], 'reports_page')->withQueryString();
        $pendingReportsCount = PropertyReport::where('status', 'pending')->count();
        $overviewStats['totalReports'] = PropertyReport::count();
        $overviewStats['pendingReportsCount'] = $pendingReportsCount;

        // Enterprise Pillars: Branches, Listings & Clients
        $branches = Branch::with(['agents', 'properties'])->latest()->paginate(12, ['*'], 'branches_page')->withQueryString();
        $listings = Listing::with('property')->latest()->paginate(12, ['*'], 'listings_page')->withQueryString();
        $clients = User::whereIn('role', ['client', 'buyer', 'tenant', 'user'])
            ->withCount(['orders', 'propertyLikes', 'propertyFavorites'])
            ->latest()
            ->paginate(12, ['*'], 'clients_page')
            ->withQueryString();
        $overviewStats['totalBranches'] = Branch::count();
        $overviewStats['activeBranches'] = Branch::where('status', 'active')->count();
        $overviewStats['totalListings'] = Listing::count();
        $overviewStats['totalClients'] = User::whereIn('role', ['client', 'buyer', 'tenant', 'user'])->count();

        $activityLogs = ActivityLog::with('user')->latest()->paginate(12, ['*'], 'logs_page')->withQueryString();
        $campaigns = Campaign::with(['agent.user', 'property', 'reviewer', 'leads'])->latest()->get();
        $overviewStats['totalCampaigns'] = Campaign::count();
        $overviewStats['pendingCampaigns'] = Campaign::where('status', 'submitted')->count();
        $overviewStats['totalActivityLogs'] = ActivityLog::count();

        $adminCharts = app(AnalyticsService::class)->getAdminOverviewChartData();

        // Access control: persona catalog, permission checklist & role grants
        $personas = config('personas.personas', []);
        $allPermissions = Permission::query()->orderBy('sort_order')->get();
        $roleGrantsByPersona = RolePermission::query()->get()
            ->groupBy('role')
            ->map(fn ($grants) => $grants->pluck('permission_id')->all());

        // Database-configurable property taxonomy (categories & listing types)
        $propertyCategories = PropertyCategory::query()->ordered()->get();
        $listingTypes = ListingType::query()->ordered()->get();
        $categoryGroups = PropertyCategory::GROUPS;
        $transactionModes = ListingType::MODES;

        // Actor rosters for the sidebar desks (SRS: Hosts, Companies, Developers)
        $hostUsers = User::query()->where('role', 'host')->latest()->take(15)->get();
        $companyUsers = User::query()->whereIn('role', ['company', 'company_admin', 'real_estate_company'])->latest()->take(15)->get();
        $developerUsers = User::query()->where('role', 'developer')->latest()->take(15)->get();

        $roles = Role::with('permissions')->orderBy('name')->get();
        $auditLogs = AuditLog::with('user')->latest()->paginate(15, ['*'], 'audit_logs_page')->withQueryString();

        return view('dashboard', compact(
            'users',
            'properties',
            'agents',
            'orders',
            'posts',
            'jobs',
            'jobApplications',
            'pendingApplicationsCount',
            'bookings',
            'projects',
            'unitReservations',
            'viewings',
            'offers',
            'overviewStats',
            'recentProperties',
            'agentRequestsToAdmin',
            'unreadAdminRequests',
            'testimonials',
            'trashedTestimonials',
            'commissions',
            'pendingCommissionsTotal',
            'paidCommissionsTotal',
            'inquiries',
            'notifications',
            'pendingPayments',
            'pendingPaymentsCount',
            'leases',
            'rentSchedules',
            'activeLeasesCount',
            'overdueRentCount',
            'totalRentCollected',
            'reports',
            'pendingReportsCount',
            'branches',
            'listings',
            'clients',
            'activityLogs',
            'campaigns',
            'adminCharts',
            'personas',
            'propertyCategories',
            'listingTypes',
            'categoryGroups',
            'transactionModes',
            'hostUsers',
            'companyUsers',
            'developerUsers',
            'allPermissions',
            'roleGrantsByPersona',
            'roles',
            'auditLogs'
        ));
    }

    public function updateSettings(Request $request)
    {
        return app(SiteSettingsController::class)->updateSettings($request);
    }

    public function storeAgent(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:agents,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'tier' => ['nullable', 'string', 'in:junior,senior,principal'],
            'split_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_verified' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $tier = $validated['tier'] ?? 'junior';
        $defaultSplit = app(CommissionSplitService::class)->getTierDetails($tier)['default_split'];

        $agent = Agent::create([
            ...collect($validated)->except(['photo', 'password', 'is_active'])->all(),
            'tier' => $tier,
            'split_percentage' => $validated['split_percentage'] ?? $defaultSplit,
            'is_active' => $request->boolean('is_active', true),
            'password' => $validated['password'] ?? null,
            'photo_path' => $this->storeAgentPhoto($request),
        ]);

        $this->syncAgentAccount($agent, $validated['password'] ?? null);

        return redirect()->route('dashboard', ['section' => 'users'])->with('success', 'Agent created successfully.');
    }

    public function updateAgent(Request $request, Agent $agent)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:agents,email,'.$agent->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'tier' => ['nullable', 'string', 'in:junior,senior,principal'],
            'split_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_verified' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = collect($validated)->except(['photo', 'password', 'is_active'])->all();
        $data['is_active'] = $request->boolean('is_active', true);

        if (filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        if ($request->hasFile('photo')) {
            if ($agent->photo_path && Storage::disk('public')->exists($agent->photo_path)) {
                Storage::disk('public')->delete($agent->photo_path);
            }

            $data['photo_path'] = $this->storeAgentPhoto($request);
        }

        $agent->update($data);
        $this->syncAgentAccount($agent, $validated['password'] ?? null);

        return redirect()->route('dashboard', ['section' => 'users'])->with('success', 'Agent updated successfully.');
    }

    /**
     * Toggle an agent's active permission status.
     */
    public function toggleAgentActive(Agent $agent): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $agent->update([
            'is_active' => ! $agent->is_active,
        ]);

        $status = $agent->is_active ? 'activated' : 'deactivated';

        return redirect()->route('dashboard', ['section' => 'users'])
            ->with('success', "Agent {$agent->name} has been {$status}.");
    }

    /**
     * Create or update the linked login account for an agent.
     */
    protected function syncAgentAccount(Agent $agent, ?string $password = null): void
    {
        $account = $agent->account;

        if (! $account) {
            $account = User::create([
                'name' => $agent->name,
                'email' => $agent->email,
                'password' => $password ?? Str::password(16),
                'role' => 'agent',
            ]);

            $agent->forceFill(['user_id' => $account->id])->save();

            return;
        }

        $account->update([
            'name' => $agent->name,
            'email' => $agent->email,
            ...filled($password) ? ['password' => $password] : [],
        ]);
    }

    public function deleteAgent(Agent $agent)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $agent->delete();

        return redirect()->route('dashboard')->with('success', 'Agent deleted successfully.');
    }

    public function storeUser(Request $request)
    {
        return app(UserManagementController::class)->storeUser($request);
    }

    public function updateUser(Request $request, User $user)
    {
        return app(UserManagementController::class)->updateUser($request, $user);
    }

    public function deleteUser(User $user)
    {
        return app(UserManagementController::class)->deleteUser($user);
    }

    public function updateProfile(Request $request)
    {
        $currentUser = auth()->user();

        if (! $currentUser || ! $currentUser->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$currentUser->id],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (filled($validated['password'] ?? null)) {
            $userData['password'] = $validated['password'];
        }

        if ($request->hasFile('photo')) {
            if ($currentUser->photo_path && Storage::disk('public')->exists($currentUser->photo_path)) {
                Storage::disk('public')->delete($currentUser->photo_path);
            }

            $userData['photo_path'] = $request->file('photo')->store('user-photos', 'public');
        }

        $currentUser->update($userData);

        return redirect()->route('dashboard', ['section' => 'profile'])->with('success', 'Profile updated successfully.');
    }

    public function updateTestimonial(Request $request, AgentFeedback $testimonial)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'agent_id' => ['nullable', 'exists:agents,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'max:2000'],
            'is_approved' => ['nullable', 'boolean'],
        ]);

        $testimonial->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? $testimonial->email,
            'agent_id' => $validated['agent_id'] ?? null,
            'rating' => $validated['rating'],
            'message' => $validated['message'],
            'is_approved' => (bool) ($validated['is_approved'] ?? false),
        ]);

        return redirect()->route('dashboard', ['section' => 'testimonials'])->with('success', 'Client testimonial updated successfully.');
    }

    public function deleteTestimonial(AgentFeedback $testimonial)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $testimonial->delete();

        return redirect()->route('dashboard', ['section' => 'testimonials'])->with('success', 'Client testimonial moved to trash.');
    }

    public function restoreTestimonial(int $id)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $testimonial = AgentFeedback::onlyTrashed()->findOrFail($id);
        $testimonial->restore();

        return redirect()->route('dashboard', ['section' => 'testimonials'])->with('success', 'Client testimonial restored successfully.');
    }

    public function toggleTestimonialApproval(AgentFeedback $testimonial)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $testimonial->update([
            'is_approved' => ! $testimonial->is_approved,
        ]);

        return redirect()->route('dashboard', ['section' => 'testimonials'])->with('success', 'Testimonial approval status updated.');
    }

    public function reviewOrder(Request $request, Order $order)
    {
        $user = auth()->user();

        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'admin_status' => ['required', 'in:approved,rejected,reviewed'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['admin_status'] === 'approved'
            && $order->isSubmittedToAdmin()
            && ! $order->hasCollectedWebsiteCommission()) {
            return redirect()->route('dashboard', ['section' => 'orders'])
                ->with('error', 'Confirm receipt of the website commission before approving this agreement.');
        }

        $order->update([
            'admin_status' => $validated['admin_status'],
            'admin_viewed_at' => now(),
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        return redirect()->route('dashboard', ['section' => 'orders'])->with('success', 'Agent agreement request was updated by admin.');
    }

    public function collectOrderCommission(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        abort_unless($order->isSubmittedToAdmin() && $order->status === 'accepted', 404);

        $validated = $request->validate([
            'collection_reference' => ['required', 'string', 'max:100'],
        ]);

        $collected = DB::transaction(function () use ($order, $user, $validated): bool {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->hasCollectedWebsiteCommission()) {
                return false;
            }

            $commission = $lockedOrder->commission()->lockForUpdate()->firstOrFail();
            $ledgerReference = 'ORDER-WEBSITE-COMMISSION-'.$lockedOrder->id;

            FinancialLedger::create([
                'entry_type' => 'credit',
                'category' => 'website_commission',
                'amount' => $commission->commission_amount,
                'currency' => 'ETB',
                'description' => 'Website commission received for order '.$lockedOrder->id.' ('.$lockedOrder->name.'); receipt '.$validated['collection_reference'],
                'reference' => $ledgerReference,
                'user_id' => $user->id,
            ]);

            $commission->update([
                'admin_note' => 'Website commission received. Receipt reference: '.$validated['collection_reference'],
            ]);

            $lockedOrder->update([
                'admin_status' => 'commission_collected',
                'admin_viewed_at' => now(),
            ]);

            return true;
        });

        if (! $collected) {
            return redirect()->route('dashboard', ['section' => 'orders'])
                ->with('error', 'Website commission has already been recorded for this agreement.');
        }

        return redirect()->route('dashboard', ['section' => 'orders'])
            ->with('success', 'Website commission collected and recorded for '.$order->name.'. You can now approve the agreement.');
    }

    public function storeBlogPost(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->canCreateContent()) {
            abort(403, 'You do not have permission to post blog or news.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:blog,news,listing,project,job'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'content' => ['required', 'string', 'min:10'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
            'image_preset' => ['nullable', 'string', 'in:hero-skyline,luxury-towers,central-plaza,panoramic-park,retail-boulevard'],
            'social_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
            'related_posts' => ['nullable', 'string', 'max:255'],
            ...$this->jobFieldRules(),
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storeUpload($request, 'image');
        } elseif ($request->filled('image_preset')) {
            $imagePath = 'images/luxury/'.$request->input('image_preset').'.jpg';
        }

        $post = BlogPost::create([
            ...$this->prepareBlogPostData($validated, $user),
            'image_path' => $imagePath,
            'social_image_path' => $this->storeUpload($request, 'social_image'),
        ]);

        $message = match ($validated['type']) {
            'listing' => 'Your listing was posted successfully.',
            'project' => 'Your project was posted successfully.',
            'job' => 'Your organization job opening was posted successfully.',
            default => 'Your '.$validated['type'].' was posted successfully.',
        };

        if ($request->filled('section')) {
            return redirect()->route('dashboard', ['section' => $request->input('section')])->with('success', $message);
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function updateBlogPost(Request $request, BlogPost $post)
    {
        $user = auth()->user();

        if (! $user || ! $user->canCreateContent()) {
            abort(403, 'You do not have permission to update blog or news posts.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:blog,news,listing,project,job'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'content' => ['required', 'string', 'min:10'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
            'image_preset' => ['nullable', 'string', 'in:hero-skyline,luxury-towers,central-plaza,panoramic-park,retail-boulevard'],
            'remove_image' => ['nullable', 'boolean'],
            'social_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
            'related_posts' => ['nullable', 'string', 'max:255'],
            ...$this->jobFieldRules(),
        ]);

        $data = $this->prepareBlogPostData($validated, $user, $post);

        if ($request->boolean('remove_image')) {
            if ($post->image_path && Storage::disk('public')->exists($post->image_path) && ! str_starts_with($post->image_path, 'images/')) {
                Storage::disk('public')->delete($post->image_path);
            }
            $data['image_path'] = null;
        } elseif ($request->hasFile('image')) {
            if ($post->image_path && Storage::disk('public')->exists($post->image_path) && ! str_starts_with($post->image_path, 'images/')) {
                Storage::disk('public')->delete($post->image_path);
            }
            $data['image_path'] = $this->storeUpload($request, 'image');
        } elseif ($request->filled('image_preset')) {
            $data['image_path'] = 'images/luxury/'.$request->input('image_preset').'.jpg';
        }

        if ($request->hasFile('social_image')) {
            if ($post->social_image_path && Storage::disk('public')->exists($post->social_image_path) && ! str_starts_with($post->social_image_path, 'images/')) {
                Storage::disk('public')->delete($post->social_image_path);
            }
            $data['social_image_path'] = $this->storeUpload($request, 'social_image');
        }

        $post->update($data);

        $message = 'The '.$post->type.' post was updated successfully.';

        if ($request->filled('section')) {
            return redirect()->route('dashboard', ['section' => $request->input('section')])->with('success', $message);
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function togglePublish(BlogPost $post)
    {
        $user = auth()->user();

        if (! $user || ! $user->canCreateContent()) {
            abort(403, 'You do not have permission to publish blog or news posts.');
        }

        if ($post->status === 'published') {
            $post->update([
                'status' => 'draft',
                'published_at' => null,
            ]);
        } else {
            $post->update([
                'status' => 'published',
                'published_at' => $post->published_at ?? now(),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'The post publication status was updated.');
    }

    public function deleteBlogPost(BlogPost $post)
    {
        $user = auth()->user();

        if (! $user || ! $user->canCreateContent()) {
            abort(403, 'You do not have permission to delete blog or news posts.');
        }

        if ($post->image_path && Storage::disk('public')->exists($post->image_path) && ! str_starts_with($post->image_path, 'images/')) {
            Storage::disk('public')->delete($post->image_path);
        }

        if ($post->social_image_path && Storage::disk('public')->exists($post->social_image_path) && ! str_starts_with($post->social_image_path, 'images/')) {
            Storage::disk('public')->delete($post->social_image_path);
        }

        $post->delete();

        if (request()->filled('section')) {
            return redirect()->route('dashboard', ['section' => request()->input('section')])->with('success', 'The post was deleted successfully.');
        }

        return redirect()->route('dashboard')->with('success', 'The post was deleted successfully.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function jobFieldRules(): array
    {
        return [
            'job_location' => ['nullable', 'string', 'max:255'],
            'job_type' => ['nullable', 'string', 'max:100'],
            'salary_range' => ['nullable', 'string', 'max:100'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'experience_level' => ['nullable', 'string', 'max:100'],
            'education_level' => ['nullable', 'string', 'max:255'],
            'apply_link' => ['nullable', 'string', 'max:2048'],
            'application_deadline' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    protected function prepareBlogPostData(array $validated, User $user, ?BlogPost $post = null): array
    {
        $status = $validated['status'] ?? 'published';
        $slug = $validated['slug'] ?? Str::slug($validated['title']);
        $baseSlug = $slug;
        $counter = 1;

        $query = BlogPost::query();
        if ($post) {
            $query->whereKeyNot($post->id);
        }

        while ($query->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $publishedAt = null;
        if (! empty($validated['published_at'])) {
            $publishedAt = Carbon::parse($validated['published_at']);
        }

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = now();
        }

        if ($status === 'scheduled' && ! $publishedAt) {
            $publishedAt = now()->addDay();
        }

        return [
            'title' => $validated['title'],
            'slug' => $slug,
            'category' => $validated['category'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'author_name' => $validated['author_name'] ?? $user->name,
            'type' => $validated['type'],
            'status' => $status,
            'content' => $validated['content'],
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['content']), 160),
            'seo_title' => $validated['seo_title'] ?? $validated['title'],
            'seo_description' => $validated['seo_description'] ?? Str::limit(strip_tags($validated['content']), 160),
            'user_id' => $user->id,
            'published_at' => $publishedAt,
            'scheduled_for' => $status === 'scheduled' ? ($publishedAt ?? now()->addDay()) : null,
            'related_post_ids' => $this->normalizeRelatedPostIds($validated['related_posts'] ?? null),
            'job_location' => $validated['job_location'] ?? null,
            'job_type' => $validated['job_type'] ?? null,
            'salary_range' => $validated['salary_range'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'experience_level' => $validated['experience_level'] ?? null,
            'education_level' => $validated['education_level'] ?? null,
            'apply_link' => $validated['apply_link'] ?? null,
            'application_deadline' => ! empty($validated['application_deadline'])
                ? Carbon::parse($validated['application_deadline'])->toDateString()
                : null,
        ];
    }

    protected function storeUpload(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store('blog-posts', 'public');
    }

    protected function storeAgentPhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('agent-photos', 'public');
    }

    protected function normalizeRelatedPostIds(?string $relatedPosts): ?string
    {
        if (blank($relatedPosts)) {
            return null;
        }

        $ids = collect(explode(',', $relatedPosts))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->implode(',');

        return $ids !== '' ? $ids : null;
    }

    /**
     * Update a commission record status (pending → paid or waived).
     */
    public function updateCommissionStatus(Request $request, Commission $commission): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,waived'],
            'admin_note' => ['nullable', 'string', 'max:500'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updates = [
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? $commission->admin_note,
            'paid_at' => $validated['status'] === 'paid' ? now() : $commission->paid_at,
        ];

        if (isset($validated['commission_rate'])) {
            $updates['commission_rate'] = (float) $validated['commission_rate'];
            if (! isset($validated['commission_amount'])) {
                $updates['commission_amount'] = round((float) $commission->property_price * (float) $validated['commission_rate'] / 100, 2);
            }
        }

        if (isset($validated['commission_amount'])) {
            $updates['commission_amount'] = (float) $validated['commission_amount'];
        }

        $commission->update($updates);

        if ($validated['status'] === 'paid') {
            FinancialLedger::firstOrCreate([
                'category' => 'agent_commission',
                'reference' => 'COMM-'.$commission->id,
            ], [
                'entry_type' => 'debit',
                'amount' => $commission->commission_amount,
                'currency' => 'ETB',
                'description' => "Commission payout for Agent {$commission->agent?->name} on property #{$commission->property_id}",
                'user_id' => $user->id,
            ]);
        }

        return redirect()->route('dashboard', ['#commissions'])
            ->with('success', 'Commission record updated by Admin.');
    }

    /**
     * Update an agent's default commission rate.
     */
    public function updateAgentCommissionRate(Request $request, Agent $agent): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tier' => ['nullable', 'string', 'in:junior,senior,principal'],
            'split_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_verified' => ['nullable', 'boolean'],
        ]);

        $updateData = [];
        if (isset($validated['commission_rate'])) {
            $updateData['commission_rate'] = $validated['commission_rate'];
        }
        if (isset($validated['tier'])) {
            $updateData['tier'] = $validated['tier'];
            if (! isset($validated['split_percentage'])) {
                $tierDetails = app(CommissionSplitService::class)->getTierDetails($validated['tier']);
                $updateData['split_percentage'] = $tierDetails['default_split'];
            }
        }
        if (isset($validated['split_percentage'])) {
            $updateData['split_percentage'] = $validated['split_percentage'];
        }
        if (isset($validated['license_number'])) {
            $updateData['license_number'] = $validated['license_number'];
        }
        if ($request->has('license_verified')) {
            $updateData['license_verified'] = $request->boolean('license_verified');
        }

        $agent->update($updateData);

        return redirect()->route('dashboard', ['section' => 'users'])
            ->with('success', "{$agent->name}'s commission profile and tier updated successfully.");
    }

    /**
     * Delete an order and its associated agreement file if present.
     */
    public function deleteOrder(Order $order): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        if ($order->agreement_path && Storage::disk('local')->exists($order->agreement_path)) {
            Storage::disk('local')->delete($order->agreement_path);
        }

        $order->delete();

        return redirect()->route('dashboard', ['#orders'])
            ->with('success', 'The order was deleted successfully.');
    }

    /**
     * Delete an agent commission record.
     */
    public function deleteCommission(Commission $commission): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        $commission->delete();

        return redirect()->route('dashboard', ['#commissions'])
            ->with('success', 'The commission record was deleted successfully.');
    }

    /**
     * Delete a client inquiry / message.
     */
    public function deleteInquiry(Inquiry $inquiry): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        $inquiry->delete();

        return redirect()->route('dashboard', ['#inquiries'])
            ->with('success', 'The message was deleted successfully.');
    }

    /**
     * Mark a client inquiry / message as read.
     */
    public function markInquiryRead(Request $request, Inquiry $inquiry): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        if (! $inquiry->read_at) {
            $inquiry->update(['read_at' => now()]);
        }

        $unreadCount = Inquiry::query()->whereNull('read_at')->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
            ]);
        }

        return redirect()->route('dashboard', ['#inquiries'])
            ->with('success', 'The message was marked as read.');
    }

    /**
     * Generate an official agreement PDF for an order directly by Admin.
     */
    public function generateOrderAgreement(Request $request, Order $order, AgreementPdfService $agreements): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        if (! $order->isAgreementApprovedForGeneration()) {
            return redirect()->route('dashboard', ['section' => 'orders'])
                ->with('error', 'Collect the website commission and approve this agreement before generating the PDF.');
        }

        $order->update([
            'status' => 'accepted',
            'admin_status' => 'approved',
            'agreed_at' => now(),
            'admin_viewed_at' => now(),
        ]);

        if ($order->property && ! in_array($order->property->status, ['sold', 'rented'], true)) {
            $order->property->update([
                'status' => $order->isRental() ? 'rented' : 'sold',
                'is_active' => false,
            ]);
        }

        $agreements->generate($order);

        return redirect()->route('dashboard', ['section' => 'orders'])
            ->with('success', 'Agreement generated successfully for '.$order->name.'. You can download the PDF below.');
    }

    /**
     * Sell or rent a property directly by Admin and generate the official agreement.
     */
    public function generatePropertyAgreement(Request $request, Property $property, AgreementPdfService $agreements): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:50'],
            'deal_price' => ['required', 'numeric', 'min:0'],
            'lease_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::create([
            'property_id' => $property->id,
            'agent_id' => $property->agent_id,
            'type' => $property->type === 'rent' ? 'rent' : 'sale',
            'name' => $validated['buyer_name'],
            'email' => $validated['buyer_email'],
            'phone' => $validated['buyer_phone'] ?? null,
            'offer_amount' => $validated['deal_price'],
            'lease_months' => $property->type === 'rent' ? ($validated['lease_months'] ?? 12) : null,
            'message' => $validated['admin_note'] ?? 'Direct agreement issued by Admin.',
            'status' => 'accepted',
            'admin_status' => 'approved',
            'agreed_at' => now(),
            'admin_viewed_at' => now(),
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        $property->update([
            'status' => $property->type === 'rent' ? 'rented' : 'sold',
            'is_active' => false,
        ]);

        if ($request->input('submit_action') === 'edit') {
            return redirect()->route('dashboard.orders.editAgreement', $order)
                ->with('success', 'Property "'.$property->title.'" marked as '.($property->type === 'rent' ? 'rented' : 'sold').'. You can now edit the agreement content before generating the PDF.');
        }

        $agreements->generate($order);

        return redirect()->route('dashboard', ['section' => 'orders'])
            ->with('success', 'Property "'.$property->title.'" marked as '.($property->type === 'rent' ? 'rented' : 'sold').' and agreement generated for '.$validated['buyer_name'].'.');
    }

    /**
     * Show the agreement content editor so Admin can review and edit before generating the PDF.
     */
    public function editAgreementContent(Order $order, AgreementPdfService $agreements): View
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        abort_unless($order->isAgreementApprovedForGeneration(), 403, 'This agreement is not approved for generation.');

        $content = $order->agreement_content ?? $agreements->getEditableContent($order);

        return view('admin.edit-agreement', compact('order', 'content'));
    }

    /**
     * Save the admin-edited agreement content, generate the PDF, and redirect to dashboard.
     */
    public function storeAgreementContent(Request $request, Order $order, AgreementPdfService $agreements): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        abort_unless($order->isAgreementApprovedForGeneration(), 403, 'This agreement is not approved for generation.');

        $validated = $request->validate([
            'seller_name' => ['nullable', 'string', 'max:255'],
            'seller_phone' => ['nullable', 'string', 'max:100'],
            'seller_email' => ['nullable', 'string', 'max:255'],
            'seller_address' => ['nullable', 'string', 'max:500'],
            'buyer_name' => ['nullable', 'string', 'max:255'],
            'buyer_email' => ['nullable', 'string', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:100'],
            'property_title' => ['nullable', 'string', 'max:255'],
            'property_city' => ['nullable', 'string', 'max:255'],
            'property_address' => ['nullable', 'string', 'max:500'],
            'property_category' => ['nullable', 'string', 'max:255'],
            'bedrooms' => ['nullable', 'string', 'max:50'],
            'bathrooms' => ['nullable', 'string', 'max:50'],
            'area' => ['nullable', 'string', 'max:50'],
            'offer_amount' => ['nullable', 'string', 'max:100'],
            'lease_months' => ['nullable', 'string', 'max:10'],
            'special_terms' => ['nullable', 'string', 'max:5000'],
            'agent_note' => ['nullable', 'string', 'max:2000'],
        ]);

        // Store the edited content so regeneration preserves admin edits.
        $order->update([
            'agreement_content' => $validated,
            'status' => 'accepted',
            'admin_status' => 'approved',
            'agreed_at' => now(),
            'admin_viewed_at' => now(),
        ]);

        if ($order->property && ! in_array($order->property->status, ['sold', 'rented'], true)) {
            $order->property->update([
                'status' => $order->isRental() ? 'rented' : 'sold',
                'is_active' => false,
            ]);
        }

        $agreements->generate($order);

        return redirect()->route('dashboard', ['section' => 'orders'])
            ->with('success', 'Agreement content saved and PDF generated for '.$order->name.'.');
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'is_headquarters' => ['nullable'],
        ]);

        $validated['is_headquarters'] = (bool) ($validated['is_headquarters'] ?? false);
        if ($validated['is_headquarters']) {
            Branch::where('is_headquarters', true)->update(['is_headquarters' => false]);
        }

        Branch::create($validated);

        return redirect()->route('dashboard', ['section' => 'branches'])
            ->with('success', 'Branch created successfully.');
    }

    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code,'.$branch->id],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'is_headquarters' => ['nullable'],
        ]);

        $validated['is_headquarters'] = (bool) ($validated['is_headquarters'] ?? false);
        if ($validated['is_headquarters']) {
            Branch::where('id', '!=', $branch->id)->where('is_headquarters', true)->update(['is_headquarters' => false]);
        }

        $branch->update($validated);

        return redirect()->route('dashboard', ['section' => 'branches'])
            ->with('success', 'Branch updated successfully.');
    }

    public function deleteBranch(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()->route('dashboard', ['section' => 'branches'])
            ->with('success', 'Branch deleted successfully.');
    }
}
