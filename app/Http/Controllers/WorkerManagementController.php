<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\BlogPost;
use App\Models\Property;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkerManagementController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $workers = Worker::with('latestAttendance')->latest()->get();
        $users = User::latest()->get();
        $properties = Property::latest()->get();

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
        $attendanceSummary = $this->attendanceSummary();

        return view('dashboard', compact('workers', 'users', 'properties', 'posts', 'attendanceSummary'));
    }

    public function attendance()
    {
        $attendanceSummary = $this->attendanceSummary();
        $workers = Worker::with('latestAttendance')->get();
        $records = $workers->map(function (Worker $worker) {
            return (object) [
                'worker' => $worker,
                'status' => $worker->latestAttendance?->status ?? 'pending',
                'recorded_at' => $worker->latestAttendance?->recorded_at,
            ];
        })->sortByDesc(function ($record) {
            return $record->recorded_at ? $record->recorded_at->timestamp : 0;
        })->values();

        return view('attendance', compact('records', 'attendanceSummary'));
    }

    public function storeWorker(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:workers,email'],
            'department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        Worker::create($validated);

        return redirect()->route('dashboard')->with('success', 'Worker added successfully.');
    }

    public function recordAttendance(Worker $worker)
    {
        if (! auth()->check()) {
            abort(403, 'Please login to record attendance.');
        }

        $now = Carbon::now('Africa/Addis_Ababa');
        $status = $this->resolveStatus($now);

        AttendanceRecord::create([
            'worker_id' => $worker->id,
            'status' => $status,
            'recorded_at' => $now,
            'check_in_time' => $now->format('H:i:s'),
        ]);

        return back()->with('success', $worker->name.' was marked as '.$status.'.');
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
            'role' => ['required', 'in:admin,user'],
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
            'role' => ['required', 'in:admin,user'],
        ]);

        $password = $validated['password'] ?? null;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            ...(filled($password) ? ['password' => Hash::make($password)] : []),
        ]);

        return redirect()->route('dashboard')->with('success', 'User updated successfully.');
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
            'type' => ['required', 'in:blog,news,listing,project'],
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
            'type' => ['required', 'in:blog,news,listing,project'],
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
        ];
    }

    protected function storeUpload(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store('blog-posts', 'public');
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

    protected function resolveStatus(Carbon $now): string
    {
        $presentCutoff = Carbon::createFromTime(14, 30, 0, 'Africa/Addis_Ababa');
        $lateCutoff = Carbon::createFromTime(16, 30, 0, 'Africa/Addis_Ababa');

        if ($now->lte($presentCutoff)) {
            return 'present';
        }

        if ($now->gt($presentCutoff) && $now->lt($lateCutoff)) {
            return 'late';
        }

        return 'absent';
    }

    protected function attendanceSummary(): array
    {
        $records = AttendanceRecord::whereDate('recorded_at', now('Africa/Addis_Ababa')->toDateString())
            ->get();

        return [
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'absent' => $records->where('status', 'absent')->count(),
        ];
    }
}
