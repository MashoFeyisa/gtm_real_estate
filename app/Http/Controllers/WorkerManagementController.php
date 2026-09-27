<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentFeedback;
use App\Models\BlogPost;
use App\Models\Order;
use App\Models\Property;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkerManagementController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $users = User::latest()->get();
        $properties = Property::latest()->get();
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

        $overviewStats = [
            'totalProperties' => $properties->count(),
            'activeListings' => $properties->where('is_active', true)->whereNotIn('status', ['draft', 'archived'])->count(),
            'pendingReviews' => $properties->where('status', 'draft')->count(),
            'pendingAgentRequests' => $unreadAdminRequests->count(),
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
            'trashedTestimonials'
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
            'site_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'contact_address' => ['required', 'string', 'max:255'],
        ]);

        SiteSetting::set('site_name', $validated['site_name']);
        SiteSetting::set('contact_email', $validated['contact_email']);
        SiteSetting::set('contact_phone', $validated['contact_phone']);
        SiteSetting::set('contact_address', $validated['contact_address']);

        if ($request->hasFile('site_logo')) {
            $previousLogo = SiteSetting::get('site_logo');

            if ($previousLogo && str_starts_with($previousLogo, 'branding/') && Storage::disk('public')->exists($previousLogo)) {
                Storage::disk('public')->delete($previousLogo);
            }

            SiteSetting::set('site_logo', $request->file('site_logo')->store('branding', 'public'));
        }

        return redirect()->route('dashboard', ['section' => 'settings'])
            ->with('success', 'Site branding and contact details updated successfully.');
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
            'password' => ['nullable', 'string', 'min:6'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $agent = Agent::create([
            ...collect($validated)->except(['photo', 'password'])->all(),
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
            'password' => ['nullable', 'string', 'min:6'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = collect($validated)->except(['photo', 'password'])->all();
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
     * Create or update the linked login account for an agent.
     */
    protected function syncAgentAccount(Agent $agent, ?string $password = null): void
    {
        $account = $agent->account;

        if (! $account) {
            $account = User::create([
                'name' => $agent->name,
                'email' => $agent->email,
                'password' => $password ?? Str::random(12),
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
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,user,agent'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

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
            'password' => ['nullable', 'string', 'min:6'],
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
            'password' => ['nullable', 'string', 'min:6'],
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

    public function storeTestimonial(Request $request)
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

        AgentFeedback::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? 'client@example.com',
            'agent_id' => $validated['agent_id'] ?? null,
            'rating' => $validated['rating'],
            'message' => $validated['message'],
            'is_approved' => (bool) ($validated['is_approved'] ?? true),
        ]);

        return redirect()->route('dashboard', ['section' => 'testimonials'])->with('success', 'Client testimonial added successfully.');
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
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'social_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'related_posts' => ['nullable', 'string', 'max:255'],
            ...$this->jobFieldRules(),
        ]);

        $post = BlogPost::create([
            ...$this->prepareBlogPostData($validated, $user),
            'image_path' => $this->storeUpload($request, 'image'),
            'social_image_path' => $this->storeUpload($request, 'social_image'),
        ]);

        $message = match ($validated['type']) {
            'listing' => 'Your listing was posted successfully.',
            'project' => 'Your project was posted successfully.',
            'job' => 'Your organization job opening was posted successfully.',
            default => 'Your '.$validated['type'].' was posted successfully.',
        };

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
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'social_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'related_posts' => ['nullable', 'string', 'max:255'],
            ...$this->jobFieldRules(),
        ]);

        $data = $this->prepareBlogPostData($validated, $user, $post);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeUpload($request, 'image');
        }

        if ($request->hasFile('social_image')) {
            $data['social_image_path'] = $this->storeUpload($request, 'social_image');
        }

        $post->update($data);

        return redirect()->route('dashboard')->with('success', 'The '.$post->type.' post was updated successfully.');
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

        $post->delete();

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
}
