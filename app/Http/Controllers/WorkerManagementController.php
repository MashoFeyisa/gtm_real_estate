<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentFeedback;
use App\Models\BlogPost;
use App\Models\Commission;
use App\Models\Inquiry;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Property;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AgreementPdfService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class WorkerManagementController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $users = User::latest()->get();
        $properties = Property::with(['agent', 'orders'])->latest()->get();
        $agents = Agent::query()->with('account')->orderBy('name')->get();
        $orders = Order::query()->with(['property', 'agent'])->latest()->get();
        $agentRequestsToAdmin = Order::query()
            ->with(['property', 'agent'])
            ->whereNotNull('submitted_to_admin_at')
            ->latest('submitted_to_admin_at')
            ->get();
        $unreadAdminRequests = $agentRequestsToAdmin->whereNull('admin_viewed_at');

        $testimonials = AgentFeedback::withTrashed()->with('agent')->latest()->get();
        $trashedTestimonials = AgentFeedback::onlyTrashed()->with('agent')->latest('deleted_at')->get();

        // Commission data
        $commissions = Commission::query()
            ->with(['agent', 'order', 'property'])
            ->latest()
            ->get();
        $pendingCommissionsTotal = $commissions->where('status', 'pending')->sum('commission_amount');
        $paidCommissionsTotal = $commissions->where('status', 'paid')->sum('commission_amount');

        $inquiries = Inquiry::query()->with('agent')->latest()->get();
        $subscribers = NewsletterSubscriber::latest()->get();

        $overviewStats = [
            'totalProperties' => $properties->count(),
            'activeListings' => $properties->where('is_active', true)->whereNotIn('status', ['draft', 'archived'])->count(),
            'pendingReviews' => $properties->where('status', 'draft')->count(),
            'pendingAgentRequests' => $unreadAdminRequests->count(),
            'pendingCommissions' => $commissions->where('status', 'pending')->count(),
            'pendingCommissionsTotal' => $pendingCommissionsTotal,
            'totalSubscribers' => $subscribers->count(),
        ];
        $recentProperties = $properties->take(3);

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

        $posts = $postsQuery->get();
        $jobs = BlogPost::query()->where('type', 'job')->latest()->get();

        return view('dashboard', compact(
            'users',
            'properties',
            'agents',
            'orders',
            'posts',
            'jobs',
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
            'subscribers'
        ));
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_logo' => $request->hasFile('site_logo') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'site_logo_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'contact_address' => ['required', 'string', 'max:255'],
            'happy_buyers_base_count' => ['nullable', 'integer', 'min:0'],
            // Static Landing Page Images (Files or URLs)
            'hero_image' => $request->hasFile('hero_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'hero_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'panorama_image' => $request->hasFile('panorama_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'panorama_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'project_1_image' => $request->hasFile('project_1_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'project_1_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'project_2_image' => $request->hasFile('project_2_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'project_2_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'project_3_image' => $request->hasFile('project_3_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'project_3_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'about_image' => $request->hasFile('about_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'about_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            // Hero & Search
            'hero_badge' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:1500'],
            'hero_primary_button_text' => ['nullable', 'string', 'max:100'],
            'hero_secondary_button_text' => ['nullable', 'string', 'max:100'],
            'hero_careers_link_text' => ['nullable', 'string', 'max:100'],
            'hero_featured_badge' => ['nullable', 'string', 'max:100'],
            'hero_stat_1_label' => ['nullable', 'string', 'max:100'],
            'hero_stat_2_label' => ['nullable', 'string', 'max:100'],
            'hero_stat_3_value' => ['nullable', 'string', 'max:100'],
            'hero_stat_3_label' => ['nullable', 'string', 'max:100'],
            'filter_category_label' => ['nullable', 'string', 'max:100'],
            'filter_type_label' => ['nullable', 'string', 'max:100'],
            'filter_location_label' => ['nullable', 'string', 'max:100'],
            'filter_button_text' => ['nullable', 'string', 'max:100'],
            // Counter
            'counter_1_value' => ['nullable', 'string', 'max:100'],
            'counter_1_label' => ['nullable', 'string', 'max:100'],
            'counter_2_label' => ['nullable', 'string', 'max:100'],
            'counter_3_label' => ['nullable', 'string', 'max:100'],
            'counter_4_value' => ['nullable', 'string', 'max:100'],
            'counter_4_label' => ['nullable', 'string', 'max:100'],
            // Amenities
            'amenities_badge' => ['nullable', 'string', 'max:100'],
            'amenities_title' => ['nullable', 'string', 'max:255'],
            'amenities_description' => ['nullable', 'string', 'max:1500'],
            'amenity_1_title' => ['nullable', 'string', 'max:255'],
            'amenity_1_description' => ['nullable', 'string', 'max:1000'],
            'amenity_2_title' => ['nullable', 'string', 'max:255'],
            'amenity_2_description' => ['nullable', 'string', 'max:1000'],
            'amenity_3_title' => ['nullable', 'string', 'max:255'],
            'amenity_3_description' => ['nullable', 'string', 'max:1000'],
            'amenity_4_title' => ['nullable', 'string', 'max:255'],
            'amenity_4_description' => ['nullable', 'string', 'max:1000'],
            'amenity_5_title' => ['nullable', 'string', 'max:255'],
            'amenity_5_description' => ['nullable', 'string', 'max:1000'],
            'amenity_6_title' => ['nullable', 'string', 'max:255'],
            'amenity_6_description' => ['nullable', 'string', 'max:1000'],
            'amenity_7_title' => ['nullable', 'string', 'max:255'],
            'amenity_7_description' => ['nullable', 'string', 'max:1000'],
            'amenity_8_title' => ['nullable', 'string', 'max:255'],
            'amenity_8_description' => ['nullable', 'string', 'max:1000'],
            // Featured Properties
            'featured_properties_badge' => ['nullable', 'string', 'max:100'],
            'featured_properties_title' => ['nullable', 'string', 'max:255'],
            'featured_properties_link_text' => ['nullable', 'string', 'max:100'],
            // Panorama
            'panorama_badge' => ['nullable', 'string', 'max:100'],
            'panorama_title' => ['nullable', 'string', 'max:255'],
            'panorama_description' => ['nullable', 'string', 'max:1500'],
            'panorama_primary_button_text' => ['nullable', 'string', 'max:100'],
            'panorama_secondary_button_text' => ['nullable', 'string', 'max:100'],
            // Developments
            'developments_badge' => ['nullable', 'string', 'max:100'],
            'developments_title' => ['nullable', 'string', 'max:255'],
            'developments_link_text' => ['nullable', 'string', 'max:100'],
            'developments_link_url' => ['nullable', 'string', 'max:255'],
            'project_1_tag' => ['nullable', 'string', 'max:100'],
            'project_1_title' => ['nullable', 'string', 'max:255'],
            'project_1_description' => ['nullable', 'string', 'max:1000'],
            'project_2_tag' => ['nullable', 'string', 'max:100'],
            'project_2_title' => ['nullable', 'string', 'max:255'],
            'project_2_description' => ['nullable', 'string', 'max:1000'],
            'project_3_tag' => ['nullable', 'string', 'max:100'],
            'project_3_title' => ['nullable', 'string', 'max:255'],
            'project_3_description' => ['nullable', 'string', 'max:1000'],
            // Latest Properties
            'latest_properties_badge' => ['nullable', 'string', 'max:100'],
            'latest_properties_title' => ['nullable', 'string', 'max:255'],
            'latest_properties_link_text' => ['nullable', 'string', 'max:100'],
            // About
            'about_badge' => ['nullable', 'string', 'max:100'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_description' => ['nullable', 'string', 'max:2000'],
            'about_pillar_1_title' => ['nullable', 'string', 'max:100'],
            'about_pillar_1_desc' => ['nullable', 'string', 'max:100'],
            'about_pillar_2_title' => ['nullable', 'string', 'max:100'],
            'about_pillar_2_desc' => ['nullable', 'string', 'max:100'],
            'about_image_subtitle' => ['nullable', 'string', 'max:100'],
            'about_image_title' => ['nullable', 'string', 'max:255'],
            // Blog / News
            'news_badge' => ['nullable', 'string', 'max:100'],
            'news_title' => ['nullable', 'string', 'max:255'],
            'news_link_text' => ['nullable', 'string', 'max:100'],
            // Testimonials
            'testimonials_badge' => ['nullable', 'string', 'max:100'],
            'testimonials_title' => ['nullable', 'string', 'max:255'],
            // Agents
            'agents_badge' => ['nullable', 'string', 'max:100'],
            'agents_title' => ['nullable', 'string', 'max:255'],
            'agents_link_text' => ['nullable', 'string', 'max:100'],
            // Contact
            'contact_badge' => ['nullable', 'string', 'max:100'],
            'contact_title' => ['nullable', 'string', 'max:255'],
            'contact_description' => ['nullable', 'string', 'max:1500'],
            'contact_box_title' => ['nullable', 'string', 'max:255'],
            'contact_box_description' => ['nullable', 'string', 'max:1500'],
            'contact_benefit_1' => ['nullable', 'string', 'max:255'],
            'contact_benefit_2' => ['nullable', 'string', 'max:255'],
            'contact_benefit_3' => ['nullable', 'string', 'max:255'],
            'contact_submit_text' => ['nullable', 'string', 'max:100'],
            // Newsletter
            'newsletter_badge' => ['nullable', 'string', 'max:100'],
            'newsletter_title' => ['nullable', 'string', 'max:255'],
            'newsletter_description' => ['nullable', 'string', 'max:1500'],
            'newsletter_placeholder' => ['nullable', 'string', 'max:100'],
            'newsletter_button_text' => ['nullable', 'string', 'max:100'],
            // Concierge
            'concierge_button_text' => ['nullable', 'string', 'max:100'],
        ]);

        // Persist basic site settings
        SiteSetting::set('site_name', $validated['site_name']);
        SiteSetting::set('contact_email', $validated['contact_email']);
        SiteSetting::set('contact_phone', $validated['contact_phone']);
        SiteSetting::set('contact_address', $validated['contact_address']);

        if ($request->has('happy_buyers_base_count')) {
            SiteSetting::set('happy_buyers_base_count', (int) ($validated['happy_buyers_base_count'] ?? 0));
        }

        // Persist all landing page content controlled from the dashboard
        $allConfigurableKeys = [
            'hero_badge', 'hero_title', 'hero_description', 'hero_primary_button_text', 'hero_secondary_button_text',
            'hero_careers_link_text', 'hero_featured_badge', 'hero_stat_1_label', 'hero_stat_2_label',
            'hero_stat_3_value', 'hero_stat_3_label', 'filter_category_label', 'filter_type_label',
            'filter_location_label', 'filter_button_text',
            'counter_1_value', 'counter_1_label', 'counter_2_label', 'counter_3_label',
            'counter_4_value', 'counter_4_label',
            'amenities_badge', 'amenities_title', 'amenities_description',
            'amenity_1_title', 'amenity_1_description', 'amenity_2_title', 'amenity_2_description',
            'amenity_3_title', 'amenity_3_description', 'amenity_4_title', 'amenity_4_description',
            'amenity_5_title', 'amenity_5_description', 'amenity_6_title', 'amenity_6_description',
            'amenity_7_title', 'amenity_7_description', 'amenity_8_title', 'amenity_8_description',
            'featured_properties_badge', 'featured_properties_title', 'featured_properties_link_text',
            'panorama_badge', 'panorama_title', 'panorama_description',
            'panorama_primary_button_text', 'panorama_secondary_button_text',
            'developments_badge', 'developments_title', 'developments_link_text', 'developments_link_url',
            'project_1_tag', 'project_1_title', 'project_1_description',
            'project_2_tag', 'project_2_title', 'project_2_description',
            'project_3_tag', 'project_3_title', 'project_3_description',
            'latest_properties_badge', 'latest_properties_title', 'latest_properties_link_text',
            'about_badge', 'about_title', 'about_description',
            'about_pillar_1_title', 'about_pillar_1_desc', 'about_pillar_2_title', 'about_pillar_2_desc',
            'about_image_subtitle', 'about_image_title',
            'news_badge', 'news_title', 'news_link_text',
            'testimonials_badge', 'testimonials_title',
            'agents_badge', 'agents_title', 'agents_link_text',
            'contact_badge', 'contact_title', 'contact_description',
            'contact_box_title', 'contact_box_description',
            'contact_benefit_1', 'contact_benefit_2', 'contact_benefit_3', 'contact_submit_text',
            'newsletter_badge', 'newsletter_title', 'newsletter_description',
            'newsletter_placeholder', 'newsletter_button_text',
            'concierge_button_text',
        ];

        foreach ($allConfigurableKeys as $key) {
            if ($request->has($key)) {
                SiteSetting::set($key, $validated[$key] ?? null);
            }
        }

        // Handle logo upload or URL
        $logoFile = $request->file('site_logo_file') ?? $request->file('site_logo');
        if ($logoFile) {
            $previousLogo = SiteSetting::get('site_logo');

            // Delete the old logo if it exists in the images directory
            if ($previousLogo && str_starts_with($previousLogo, 'images/') && Storage::disk('public')->exists($previousLogo)) {
                Storage::disk('public')->delete($previousLogo);
            }

            // Store the new logo in the images folder on the public disk
            $newPath = $logoFile->store('images', 'public');
            SiteSetting::set('site_logo', $newPath);
        } elseif ($request->has('site_logo') && ! $request->hasFile('site_logo')) {
            $stringLogo = $request->input('site_logo');
            if (filled($stringLogo)) {
                SiteSetting::set('site_logo', trim($stringLogo));
            }
        }

        // Handle static landing page images (uploaded file or custom URL)
        $staticImageKeys = [
            'hero_image',
            'panorama_image',
            'project_1_image',
            'project_2_image',
            'project_3_image',
            'about_image',
        ];

        foreach ($staticImageKeys as $imgKey) {
            $fileInputKey = $imgKey.'_file';
            $uploaded = $request->file($fileInputKey) ?? $request->file($imgKey);

            if ($uploaded) {
                $previousImage = SiteSetting::get($imgKey);
                if ($previousImage && str_starts_with($previousImage, 'site_images/') && Storage::disk('public')->exists($previousImage)) {
                    Storage::disk('public')->delete($previousImage);
                }

                $newPath = $uploaded->store('site_images', 'public');
                SiteSetting::set($imgKey, $newPath);
            } elseif ($request->has($imgKey) && ! $request->hasFile($imgKey)) {
                $stringValue = $request->input($imgKey);
                SiteSetting::set($imgKey, filled($stringValue) ? trim($stringValue) : null);
            }
        }

        return redirect()->route('dashboard', ['section' => 'settings'])
            ->with('success', 'Site settings, content, and images updated successfully.');
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
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $agent = Agent::create([
            ...collect($validated)->except(['photo', 'password', 'is_active'])->all(),
            'is_active' => $request->boolean('is_active', true),
            'password' => $validated['password'] ?? null,
            'photo_path' => $this->storeAgentPhoto($request),
        ]);

        $this->syncAgentAccount($agent, $validated['password'] ?? null);

        return redirect()->route('dashboard', ['section' => 'agents'])->with('success', 'Agent created successfully.');
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

        return redirect()->route('dashboard', ['section' => 'agents'])->with('success', 'Agent updated successfully.');
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

        return redirect()->route('dashboard', ['section' => 'agents'])
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
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'in:admin,agent'],
        ]);

        $createdUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        if ($validated['role'] === 'agent') {
            $agent = Agent::query()->firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'password' => $validated['password'],
                    'user_id' => $createdUser->id,
                ]
            );
            if (! $agent->user_id) {
                $agent->forceFill(['user_id' => $createdUser->id])->save();
            }
        }

        return back()->with('success', 'User created successfully.');
    }

    public function updateUser(Request $request, User $user)
    {
        $currentUser = auth()->user();

        if (! $currentUser || ! $currentUser->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'in:admin,user,agent'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (filled($validated['password'] ?? null)) {
            $userData['password'] = $validated['password'];
        }

        $user->update($userData);

        if ($user->agentProfile) {
            $agentData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            if (filled($validated['password'] ?? null)) {
                $agentData['password'] = $validated['password'];
            }
            $user->agentProfile->update($agentData);
        }

        return redirect()->route('dashboard', ['section' => 'users'])->with('success', 'User updated successfully.');
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
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (filled($validated['password'] ?? null)) {
            $userData['password'] = $validated['password'];
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

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'admin_status' => ['required', 'in:approved,rejected,reviewed'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update([
            'admin_status' => $validated['admin_status'],
            'admin_viewed_at' => now(),
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        return redirect()->route('dashboard', ['section' => 'orders'])->with('success', 'Agent agreement request was updated by admin.');
    }

    public function deleteUser(User $user)
    {
        $currentUser = auth()->user();

        if (! $currentUser || ! $currentUser->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        if ($user->id === $currentUser->id) {
            abort(403, 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('dashboard')->with('success', 'User deleted successfully.');
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
        if (! $user || ! $user->isAdmin()) {
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
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $agent->update(['commission_rate' => $validated['commission_rate']]);

        return redirect()->route('dashboard', ['#agents'])
            ->with('success', $agent->name."'s commission rate updated to {$validated['commission_rate']}%.");
    }

    /**
     * Delete an order and its associated agreement file if present.
     */
    public function deleteOrder(Order $order): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
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
        if (! $user || ! $user->isAdmin()) {
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
        if (! $user || ! $user->isAdmin()) {
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
        if (! $user || ! $user->isAdmin()) {
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
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
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
        if (! $user || ! $user->isAdmin()) {
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
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $content = $order->agreement_content ?? $agreements->getEditableContent($order);

        return view('admin.edit-agreement', compact('order', 'content'));
    }

    /**
     * Save the admin-edited agreement content, generate the PDF, and redirect to dashboard.
     */
    public function storeAgreementContent(Request $request, Order $order, AgreementPdfService $agreements): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

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
}
