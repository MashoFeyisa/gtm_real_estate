<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\WorkerManagementController;
use App\Models\BlogPost;
use App\Models\Property;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $activeProperties = Property::query()
        ->where('is_active', true)
        ->whereNotIn('status', ['draft', 'archived'])
        ->get();

    $countByCategoryKeyword = function (array $keywords) use ($activeProperties) {
        return $activeProperties->filter(function ($property) use ($keywords) {
            $label = strtolower((string) ($property->property_category ?? $property->category ?? $property->title));

            foreach ($keywords as $keyword) {
                if (str_contains($label, strtolower($keyword))) {
                    return true;
                }
            }

            return false;
        })->count();
    };

    return view('app', [
        'posts' => BlogPost::with('user')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(6)
            ->get(),
        'featuredProperties' => Property::query()
            ->where('is_active', true)
            ->whereNotIn('status', ['draft', 'archived'])
            ->where('featured', true)
            ->latest('updated_at')
            ->limit(3)
            ->get(),
        'latestProperties' => Property::query()
            ->where('is_active', true)
            ->whereNotIn('status', ['draft', 'archived'])
            ->latest('updated_at')
            ->limit(3)
            ->get(),
        'featuredProjects' => BlogPost::with('user')
            ->where('status', 'published')
            ->where('type', 'project')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(3)
            ->get(),
        'listingPosts' => BlogPost::with('user')
            ->where('status', 'published')
            ->where('type', 'listing')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(3)
            ->get(),
        'homeStats' => [
            'homes' => $countByCategoryKeyword(['home', 'house', 'residence']),
            'villas' => $countByCategoryKeyword(['villa', 'villas']),
            'apartments' => $countByCategoryKeyword(['apartment', 'apartment suite', 'flat']),
            'sold' => $activeProperties->where('status', 'sold')->count(),
        ],
    ]);
})->name('home');

Route::get('/app', function () {
    $activeProperties = Property::query()
        ->where('is_active', true)
        ->whereNotIn('status', ['draft', 'archived'])
        ->get();

    $countByCategoryKeyword = function (array $keywords) use ($activeProperties) {
        return $activeProperties->filter(function ($property) use ($keywords) {
            $label = strtolower((string) ($property->property_category ?? $property->category ?? $property->title));

            foreach ($keywords as $keyword) {
                if (str_contains($label, strtolower($keyword))) {
                    return true;
                }
            }

            return false;
        })->count();
    };

    return view('app', [
        'posts' => BlogPost::with('user')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(6)
            ->get(),
        'featuredProperties' => Property::query()
            ->where('is_active', true)
            ->whereNotIn('status', ['draft', 'archived'])
            ->where('featured', true)
            ->latest('updated_at')
            ->limit(3)
            ->get(),
        'latestProperties' => Property::query()
            ->where('is_active', true)
            ->whereNotIn('status', ['draft', 'archived'])
            ->latest('updated_at')
            ->limit(3)
            ->get(),
        'featuredProjects' => BlogPost::with('user')
            ->where('status', 'published')
            ->where('type', 'project')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(3)
            ->get(),
        'listingPosts' => BlogPost::with('user')
            ->where('status', 'published')
            ->where('type', 'listing')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(3)
            ->get(),
        'homeStats' => [
            'homes' => $countByCategoryKeyword(['home', 'house', 'residence']),
            'villas' => $countByCategoryKeyword(['villa', 'villas']),
            'apartments' => $countByCategoryKeyword(['apartment', 'apartment suite', 'flat']),
            'sold' => $activeProperties->where('status', 'sold')->count(),
        ],
    ]);
})->name('app.home');

Route::get('/buy', fn () => view('public.page', ['title' => 'Buy', 'intro' => 'Explore homes and investment opportunities tailored to your budget and goals.', 'page' => 'buy']))->name('buy');
Route::get('/rent', fn () => view('public.page', ['title' => 'Rent', 'intro' => 'Discover flexible rental homes and premium living spaces in prime locations.', 'page' => 'rent']))->name('rent');
Route::get('/land', fn () => view('public.page', ['title' => 'Land', 'intro' => 'Find land for residential, commercial, and future development investment.', 'page' => 'land']))->name('land');
Route::get('/commercial', fn () => view('public.page', ['title' => 'Commercial', 'intro' => 'Invest in strategic commercial spaces built for business growth.', 'page' => 'commercial']))->name('commercial');
Route::get('/projects', fn () => view('public.page', ['title' => 'Projects / Developments', 'intro' => 'Explore our flagship developments and upcoming projects across key districts.', 'page' => 'projects']))->name('projects');
Route::get('/project-details', fn () => view('public.page', ['title' => 'Project Details', 'intro' => 'See the details behind each development, location, and investment outlook.', 'page' => 'project-details']))->name('project-details');
Route::get('/services', fn () => view('public.page', ['title' => 'Services', 'intro' => 'From buying and leasing to project management, we help you move with confidence.', 'page' => 'services']))->name('services');
Route::get('/about', fn () => view('public.page', ['title' => 'About GTP', 'intro' => 'GTP Real Estate is shaping trusted property experiences in Ethiopia with expertise, integrity, and local insight.', 'page' => 'about']))->name('about');
Route::get('/leadership', fn () => view('public.page', ['title' => 'Leadership / Team', 'intro' => 'Meet the experienced leaders and advisors guiding our market reputation.', 'page' => 'leadership']))->name('leadership');
Route::get('/agents', fn () => view('public.page', ['title' => 'Agents', 'intro' => 'Connect with property consultants who understand your local goals and investment priorities.', 'page' => 'agents']))->name('agents');
Route::get('/agent-details', fn () => view('public.page', ['title' => 'Agent Details', 'intro' => 'Learn more about the agent supporting your search and negotiation journey.', 'page' => 'agent-details']))->name('agent-details');
Route::get('/news', function () {
    return view('public.page', [
        'title' => 'News',
        'intro' => 'Read real estate updates, market insights, and local development trends.',
        'page' => 'news',
    ]);
})->name('news');
Route::get('/blogs', function () {
    return view('public.page', [
        'title' => 'Blog',
        'intro' => 'Insights, home tips, and notes from the property market.',
        'page' => 'blog',
    ]);
})->name('blogs');
Route::get('/article-details', fn () => view('public.page', ['title' => 'Article Details', 'intro' => 'A detailed article from our market experts and local analysts.', 'page' => 'article-details']))->name('article-details');
Route::get('/careers', function () {
    $jobs = BlogPost::query()
        ->where('type', 'job')
        ->where('status', 'published')
        ->latest('published_at')
        ->get();

    return view('public.page', [
        'title' => 'Careers',
        'intro' => 'Build your future with a team passionate about real estate and client care.',
        'page' => 'careers',
        'jobs' => $jobs,
    ]);
})->name('careers');
Route::get('/job-details', fn () => view('public.page', ['title' => 'Job Details', 'intro' => 'Explore role responsibilities, requirements, and how you can join our team.', 'page' => 'job-details']))->name('job-details');
Route::get('/contact', fn () => view('public.page', ['title' => 'Contact', 'intro' => 'Ask for a consultation, schedule a visit, or speak with an advisor today.', 'page' => 'contact']))->name('contact');
Route::get('/search-results', fn () => view('public.page', ['title' => 'Search Results', 'intro' => 'Explore listings that match your location, budget, and lifestyle priorities.', 'page' => 'search-results']))->name('search-results');
Route::get('/privacy-policy', fn () => view('public.page', ['title' => 'Privacy Policy', 'intro' => 'Your information is handled with care and transparency across our customer journey.', 'page' => 'privacy-policy']))->name('privacy-policy');
Route::get('/terms', fn () => view('public.page', ['title' => 'Terms', 'intro' => 'Read the terms governing our services, transactions, and customer interactions.', 'page' => 'terms']))->name('terms');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/attendance', [WorkerManagementController::class, 'attendance'])->name('attendance');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WorkerManagementController::class, 'dashboard'])->name('dashboard');
    Route::post('/dashboard/workers', [WorkerManagementController::class, 'storeWorker'])->name('dashboard.workers.store');
    Route::post('/dashboard/workers/{worker}/record-attendance', [WorkerManagementController::class, 'recordAttendance'])->name('workers.recordAttendance');
    Route::post('/dashboard/users', [WorkerManagementController::class, 'storeUser'])->name('dashboard.users.store');
    Route::put('/dashboard/users/{user}', [WorkerManagementController::class, 'updateUser'])->name('dashboard.users.update');
    Route::delete('/dashboard/users/{user}', [WorkerManagementController::class, 'deleteUser'])->name('dashboard.users.delete');
    Route::post('/dashboard/posts', [WorkerManagementController::class, 'storeBlogPost'])->name('dashboard.posts.store');
    Route::put('/dashboard/posts/{post}', [WorkerManagementController::class, 'updateBlogPost'])->name('dashboard.posts.update');
    Route::post('/dashboard/posts/{post}/toggle-publish', [WorkerManagementController::class, 'togglePublish'])->name('dashboard.posts.togglePublish');
    Route::delete('/dashboard/posts/{post}', [WorkerManagementController::class, 'deleteBlogPost'])->name('dashboard.posts.delete');
    Route::post('/dashboard/properties', [PropertyController::class, 'store'])->name('dashboard.properties.store');
    Route::put('/dashboard/properties/{property}', [PropertyController::class, 'update'])->name('dashboard.properties.update');
    Route::post('/dashboard/properties/{property}/toggle-publish', [PropertyController::class, 'togglePublish'])->name('dashboard.properties.togglePublish');
    Route::post('/dashboard/properties/{property}/toggle-sold', [PropertyController::class, 'toggleSold'])->name('dashboard.properties.toggleSold');
    Route::post('/dashboard/properties/{property}/archive', [PropertyController::class, 'archive'])->name('dashboard.properties.archive');
    Route::delete('/dashboard/properties/{property}', [PropertyController::class, 'destroy'])->name('dashboard.properties.destroy');
});

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/villas', [PropertyController::class, 'index'])->defaults('category', 'villa')->name('properties.villas');
Route::get('/properties/homes', [PropertyController::class, 'index'])->defaults('category', 'home')->name('properties.homes');
Route::get('/properties/apartments', [PropertyController::class, 'index'])->defaults('category', 'apartment')->name('properties.apartments');
Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');
