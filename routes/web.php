<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentPortalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\WorkerManagementController;
use App\Models\Agent;
use App\Models\AgentFeedback;
use App\Models\BlogPost;
use App\Models\NewsletterSubscriber;
use App\Models\Property;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $activeProperties = Property::query()->publicVisible()->get();

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

    return view('home', [
        'posts' => BlogPost::with('user')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(12)
            ->get(),
        'featuredProperties' => Property::query()
            ->with(['agent', 'images'])
            ->publicVisible()
            ->where('featured', true)
            ->latest('updated_at')
            ->limit(12)
            ->get(),
        'latestProperties' => Property::query()
            ->with(['agent', 'images'])
            ->publicVisible()
            ->latest('updated_at')
            ->limit(12)
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
            'happy_buyers' => (int) SiteSetting::get('happy_buyers_base_count', 0) + NewsletterSubscriber::count(),
        ],
        'agents' => Agent::query()->active()->orderBy('name')->get(),
        'testimonials' => AgentFeedback::query()
            ->with('agent')
            ->where('is_approved', true)
            ->latest()
            ->limit(12)
            ->get(),
    ]);
})->name('home');

Route::get('/app', function () {
    $activeProperties = Property::query()->publicVisible()->get();

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

    return view('home', [
        'posts' => BlogPost::with('user')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(12)
            ->get(),
        'featuredProperties' => Property::query()
            ->with(['agent', 'images'])
            ->publicVisible()
            ->where('featured', true)
            ->latest('updated_at')
            ->limit(12)
            ->get(),
        'latestProperties' => Property::query()
            ->with(['agent', 'images'])
            ->publicVisible()
            ->latest('updated_at')
            ->limit(12)
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
            'happy_buyers' => (int) SiteSetting::get('happy_buyers_base_count', 0) + NewsletterSubscriber::count(),
        ],
        'agents' => Agent::query()->active()->orderBy('name')->get(),
        'testimonials' => AgentFeedback::query()
            ->with('agent')
            ->where('is_approved', true)
            ->latest()
            ->limit(12)
            ->get(),
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
Route::get('/agents', [AgentController::class, 'index'])->name('agents');
Route::get('/agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
Route::post('/agents/{agent}/feedback', [AgentController::class, 'storeFeedback'])->name('agents.feedback');
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
$careersHandler = function () {
    $jobs = BlogPost::query()
        ->where('type', 'job')
        ->where('status', 'published')
        ->latest('published_at')
        ->get();

    return view('public.page', [
        'title' => 'Careers & Jobs',
        'intro' => 'Build your future with a team passionate about real estate and client care.',
        'page' => 'careers',
        'jobs' => $jobs,
    ]);
};

Route::get('/careers', $careersHandler)->name('careers');
Route::get('/jobs', $careersHandler)->name('jobs');
Route::get('/job', $careersHandler)->name('job');
Route::get('/job-details', $careersHandler)->name('job-details');
Route::get('/contact', fn () => view('public.page', ['title' => 'Contact', 'intro' => 'Ask for a consultation, schedule a visit, or speak with an advisor today.', 'page' => 'contact']))->name('contact');
Route::get('/search-results', fn () => view('public.page', ['title' => 'Search Results', 'intro' => 'Explore listings that match your location, budget, and lifestyle priorities.', 'page' => 'search-results']))->name('search-results');
Route::get('/privacy-policy', fn () => view('public.page', ['title' => 'Privacy Policy', 'intro' => 'Your information is handled with care and transparency across our customer journey.', 'page' => 'privacy-policy']))->name('privacy-policy');
Route::get('/terms', fn () => view('public.page', ['title' => 'Terms', 'intro' => 'Read the terms governing our services, transactions, and customer interactions.', 'page' => 'terms']))->name('terms');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/properties/{property}/orders', [OrderController::class, 'store'])->name('orders.store');

Route::middleware('auth')->group(function () {
    Route::get('/orders/{order}/agreement', [OrderController::class, 'downloadAgreement'])->name('orders.agreement');
    Route::get('/dashboard', [WorkerManagementController::class, 'dashboard'])->name('dashboard');
    Route::post('/dashboard/agents', [WorkerManagementController::class, 'storeAgent'])->name('dashboard.agents.store');
    Route::put('/dashboard/agents/{agent}', [WorkerManagementController::class, 'updateAgent'])->name('dashboard.agents.update');
    Route::delete('/dashboard/agents/{agent}', [WorkerManagementController::class, 'deleteAgent'])->name('dashboard.agents.destroy');
    Route::post('/dashboard/users', [WorkerManagementController::class, 'storeUser'])->name('dashboard.users.store');
    Route::put('/dashboard/users/{user}', [WorkerManagementController::class, 'updateUser'])->name('dashboard.users.update');
    Route::delete('/dashboard/users/{user}', [WorkerManagementController::class, 'deleteUser'])->name('dashboard.users.delete');
    Route::post('/dashboard/posts', [WorkerManagementController::class, 'storeBlogPost'])->name('dashboard.posts.store');
    Route::put('/dashboard/posts/{post}', [WorkerManagementController::class, 'updateBlogPost'])->name('dashboard.posts.update');
    Route::post('/dashboard/posts/{post}/toggle-publish', [WorkerManagementController::class, 'togglePublish'])->name('dashboard.posts.togglePublish');
    Route::delete('/dashboard/posts/{post}', [WorkerManagementController::class, 'deleteBlogPost'])->name('dashboard.posts.delete');
    Route::post('/dashboard/settings', [WorkerManagementController::class, 'updateSettings'])->name('dashboard.settings.update');
    Route::post('/dashboard/profile', [WorkerManagementController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::put('/dashboard/testimonials/{testimonial}', [WorkerManagementController::class, 'updateTestimonial'])->name('dashboard.testimonials.update');
    Route::delete('/dashboard/testimonials/{testimonial}', [WorkerManagementController::class, 'deleteTestimonial'])->name('dashboard.testimonials.destroy');
    Route::post('/dashboard/testimonials/{id}/restore', [WorkerManagementController::class, 'restoreTestimonial'])->name('dashboard.testimonials.restore');
    Route::post('/dashboard/testimonials/{testimonial}/toggle-approval', [WorkerManagementController::class, 'toggleTestimonialApproval'])->name('dashboard.testimonials.toggleApproval');
    Route::post('/dashboard/orders/{order}/review', [WorkerManagementController::class, 'reviewOrder'])->name('dashboard.orders.review');
    Route::post('/dashboard/orders/{order}/generate-agreement', [WorkerManagementController::class, 'generateOrderAgreement'])->name('dashboard.orders.generateAgreement');
    Route::get('/dashboard/orders/{order}/edit-agreement', [WorkerManagementController::class, 'editAgreementContent'])->name('dashboard.orders.editAgreement');
    Route::post('/dashboard/orders/{order}/store-agreement-content', [WorkerManagementController::class, 'storeAgreementContent'])->name('dashboard.orders.storeAgreementContent');
    Route::delete('/dashboard/orders/{order}', [WorkerManagementController::class, 'deleteOrder'])->name('dashboard.orders.destroy');
    Route::delete('/dashboard/inquiries/{inquiry}', [WorkerManagementController::class, 'deleteInquiry'])->name('dashboard.inquiries.destroy');
    Route::post('/dashboard/inquiries/{inquiry}/read', [WorkerManagementController::class, 'markInquiryRead'])->name('dashboard.inquiries.read');
    Route::post('/dashboard/properties', [PropertyController::class, 'store'])->name('dashboard.properties.store');
    Route::put('/dashboard/properties/{property}', [PropertyController::class, 'update'])->name('dashboard.properties.update');
    Route::post('/dashboard/properties/{property}/generate-agreement', [WorkerManagementController::class, 'generatePropertyAgreement'])->name('dashboard.properties.generateAgreement');
    Route::post('/dashboard/properties/{property}/toggle-publish', [PropertyController::class, 'togglePublish'])->name('dashboard.properties.togglePublish');
    Route::post('/dashboard/properties/{property}/toggle-sold', [PropertyController::class, 'toggleSold'])->name('dashboard.properties.toggleSold');
    Route::post('/dashboard/properties/{property}/archive', [PropertyController::class, 'archive'])->name('dashboard.properties.archive');
    // Commission management
    Route::patch('/dashboard/commissions/{commission}/status', [WorkerManagementController::class, 'updateCommissionStatus'])->name('dashboard.commissions.status');
    Route::delete('/dashboard/commissions/{commission}', [WorkerManagementController::class, 'deleteCommission'])->name('dashboard.commissions.destroy');
    Route::put('/dashboard/agents/{agent}/commission-rate', [WorkerManagementController::class, 'updateAgentCommissionRate'])->name('dashboard.agents.commissionRate');
    Route::post('/dashboard/agents/{agent}/toggle-active', [WorkerManagementController::class, 'toggleAgentActive'])->name('dashboard.agents.toggleActive');
    Route::delete('/dashboard/subscribers/{subscriber}', [NewsletterController::class, 'destroy'])->name('dashboard.subscribers.destroy');
    Route::delete('/dashboard/properties/{property}/images/{image}', [PropertyController::class, 'deleteImage'])->name('dashboard.properties.images.destroy');
    Route::post('/dashboard/properties/{property}/images/{image}/cover', [PropertyController::class, 'setCoverImage'])->name('dashboard.properties.images.cover');
    Route::post('/dashboard/properties/{property}/preset-cover', [PropertyController::class, 'setCoverPreset'])->name('dashboard.properties.preset-cover');
    Route::delete('/dashboard/properties/{property}', [PropertyController::class, 'destroy'])->name('dashboard.properties.destroy');
});

Route::middleware('agent')->group(function () {
    Route::get('/agent-portal', [AgentPortalController::class, 'index'])->name('agent.portal');
    Route::patch('/agent-portal/orders/{order}/status', [AgentPortalController::class, 'updateOrderStatus'])->name('agent.orders.status');
    Route::delete('/agent-portal/orders/{order}', [AgentPortalController::class, 'deleteOrder'])->name('agent.orders.destroy');
    Route::put('/agent-portal/profile', [AgentPortalController::class, 'updateProfile'])->name('agent.profile.update');
    Route::post('/agent-portal/inquiries/{inquiry}/read', [AgentPortalController::class, 'markInquiryRead'])->name('agent.inquiries.read');
    Route::delete('/agent-portal/inquiries/{inquiry}', [AgentPortalController::class, 'deleteInquiry'])->name('agent.inquiries.destroy');

    // Agent-owned listing management
    Route::post('/agent-portal/properties', [AgentPortalController::class, 'storeProperty'])->name('agent.properties.store');
    Route::put('/agent-portal/properties/{property}', [AgentPortalController::class, 'updateProperty'])->name('agent.properties.update');
    Route::delete('/agent-portal/properties/{property}/images/{image}', [AgentPortalController::class, 'deletePropertyImage'])->name('agent.properties.images.destroy');
    Route::post('/agent-portal/properties/{property}/images/{image}/cover', [AgentPortalController::class, 'setPropertyCoverImage'])->name('agent.properties.images.cover');
    Route::post('/agent-portal/properties/{property}/preset-cover', [AgentPortalController::class, 'setPropertyCoverPreset'])->name('agent.properties.preset-cover');
    Route::post('/agent-portal/properties/{property}/toggle-publish', [AgentPortalController::class, 'togglePropertyPublish'])->name('agent.properties.togglePublish');
    Route::post('/agent-portal/properties/{property}/toggle-sold', [AgentPortalController::class, 'togglePropertySold'])->name('agent.properties.toggleSold');
    Route::post('/agent-portal/properties/{property}/archive', [AgentPortalController::class, 'archiveProperty'])->name('agent.properties.archive');
    Route::delete('/agent-portal/properties/{property}', [AgentPortalController::class, 'destroyProperty'])->name('agent.properties.destroy');
});

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/villas', [PropertyController::class, 'index'])->defaults('category', 'villa')->name('properties.villas');
Route::get('/properties/homes', [PropertyController::class, 'index'])->defaults('category', 'home')->name('properties.homes');
Route::get('/properties/apartments', [PropertyController::class, 'index'])->defaults('category', 'apartment')->name('properties.apartments');
Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');
