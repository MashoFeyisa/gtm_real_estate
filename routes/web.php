<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentPortalController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerDashboardController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CategoryManagementController;
use App\Http\Controllers\CleanerDashboardController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\CompanyListingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CorporateTenancyController;
use App\Http\Controllers\CustomerSupportDashboardController;
use App\Http\Controllers\DeveloperPortalController;
use App\Http\Controllers\DevelopmentController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\FinancialReportingController;
use App\Http\Controllers\GuestDashboardController;
use App\Http\Controllers\HostDashboardController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InvestorDashboardController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\LeaseManagementController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MediaAccessController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\ModeratorDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfessionalDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyComparisonController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyInteractionController;
use App\Http\Controllers\PropertyManagerDashboardController;
use App\Http\Controllers\PropertyReportController;
use App\Http\Controllers\PropertyReviewController;
use App\Http\Controllers\PropertyVerificationController;
use App\Http\Controllers\ReceptionistDashboardController;
use App\Http\Controllers\RentScheduleController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\RolePortalController;
use App\Http\Controllers\SavedCollectionController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\StayBookingController;
use App\Http\Controllers\StayCalendarSyncController;
use App\Http\Controllers\SubcityBoundaryController;
use App\Http\Controllers\TenantDashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VerificationCompanyController;
use App\Http\Controllers\VerificationDeskController;
use App\Http\Controllers\VerificationOfficerDashboardController;
use App\Http\Controllers\ViewingOfferController;
use App\Http\Controllers\WorkerManagementController;
use App\Models\Agent;
use App\Models\AgentFeedback;
use App\Models\BlogPost;
use App\Models\Project;
use App\Models\Property;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
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
            ->activeNotExpired()
            ->latest('published_at')
            ->limit(12)
            ->get(),
        'featuredProperties' => Property::query()
            ->with(['agent', 'images'])
            ->publicVisible()
            ->where('featured', true)
            ->whereNotIn('status', ['sold', 'rented'])
            ->latest('updated_at')
            ->limit(12)
            ->get(),
        'latestProperties' => Property::query()
            ->with(['agent', 'images'])
            ->publicVisible()
            ->whereNotIn('status', ['sold', 'rented'])
            ->latest('updated_at')
            ->limit(12)
            ->get(),
        'featuredDevelopments' => Project::query()
            ->with(['buildings', 'units'])
            ->where('status', 'published')
            ->latest()
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
            'happy_buyers' => (int) SiteSetting::get('happy_buyers_base_count', 0) + User::whereIn('role', ['client', 'buyer'])->count(),
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
            ->activeNotExpired()
            ->latest('published_at')
            ->limit(12)
            ->get(),
        'featuredProperties' => Property::query()
            ->with(['agent', 'images'])
            ->publicVisible()
            ->where('featured', true)
            ->whereNotIn('status', ['sold', 'rented'])
            ->latest('updated_at')
            ->limit(12)
            ->get(),
        'latestProperties' => Property::query()
            ->with(['agent', 'images'])
            ->publicVisible()
            ->whereNotIn('status', ['sold', 'rented'])
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
            'happy_buyers' => (int) SiteSetting::get('happy_buyers_base_count', 0) + User::whereIn('role', ['client', 'buyer'])->count(),
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
Route::get('/developments', [DevelopmentController::class, 'index'])->name('developments.index');
Route::get('/developments/{slug}', [DevelopmentController::class, 'show'])->name('developments.show');
Route::post('/developments/{project}/units/{unit}/reserve', [DevelopmentController::class, 'reserveUnit'])->name('developments.reserve-unit');
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
        ->activeNotExpired()
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
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/session/keep-alive', [AuthController::class, 'keepAlive'])->name('session.keep-alive');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// Account Password Recovery
Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:6,1')->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:6,1')->name('password.update');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter/subscribe', fn () => back()->with('newsletter_success', 'Thank you for subscribing.'))->name('newsletter.subscribe');
Route::post('/properties/{property}/orders', [OrderController::class, 'store'])->name('orders.store');
Route::post('/careers/apply', [JobApplicationController::class, 'store'])->name('job-applications.store');

Route::middleware('auth')->group(function () {
    Route::get('/client-portal', [ClientPortalController::class, 'index'])->name('client.portal');
    Route::get('/persona-portal', [RolePortalController::class, 'index'])->name('role.portal');
    Route::get('/orders/{order}/agreement', [OrderController::class, 'downloadAgreement'])->name('orders.agreement');
    Route::post('/properties/{property}/like', [PropertyInteractionController::class, 'toggleLike'])->name('properties.like');
    Route::post('/properties/{property}/favorite', [PropertyInteractionController::class, 'toggleFavorite'])->name('properties.favorite');
    Route::get('/dashboard', [WorkerManagementController::class, 'dashboard'])->name('dashboard');

    // Rent installment collection & housekeeping queue actions
    Route::post('/rent-schedules/{rentSchedule}/record-payment', [RentScheduleController::class, 'recordPayment'])->name('rent-schedules.record-payment');
    Route::put('/housekeeping-requests/{guestServiceRequest}/status', [HousekeepingController::class, 'updateStatus'])->name('housekeeping-requests.update-status');
    Route::put('/inquiries/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('inquiries.update-status');

    // Verification decisions for the verification officer desk (SRS §26, §27)
    Route::post('/verification/companies/{agent}/decision', [VerificationDeskController::class, 'companyDecision'])->name('verification.companies.decision');
    Route::post('/verification/developers/{project}/decision', [VerificationDeskController::class, 'developerDecision'])->name('verification.developers.decision');

    // Listing moderation workflow & content moderation decisions
    Route::post('/moderation/listings/{listing}/decision', [ModerationController::class, 'listingDecision'])->name('moderation.listings.decision');
    Route::post('/moderation/reviews/{propertyReview}/decision', [ModerationController::class, 'reviewDecision'])->name('moderation.reviews.decision');

    // Database-configurable property categories & listing types (SRS: Category Management)
    Route::post('/admin/categories', [CategoryManagementController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/admin/categories/{propertyCategory}', [CategoryManagementController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/admin/categories/{propertyCategory}', [CategoryManagementController::class, 'destroyCategory'])->name('admin.categories.destroy');
    Route::post('/admin/listing-types', [CategoryManagementController::class, 'storeListingType'])->name('admin.listing-types.store');
    Route::put('/admin/listing-types/{listingType}', [CategoryManagementController::class, 'updateListingType'])->name('admin.listing-types.update');
    Route::delete('/admin/listing-types/{listingType}', [CategoryManagementController::class, 'destroyListingType'])->name('admin.listing-types.destroy');
    Route::post('/dashboard/roles', [RoleManagementController::class, 'storeRole'])->name('dashboard.roles.store');
    Route::put('/dashboard/roles/{role}', [RoleManagementController::class, 'updateRole'])->name('dashboard.roles.update');
    Route::post('/dashboard/roles/permissions', [RoleManagementController::class, 'updateRolePermissions'])->name('dashboard.roles.permissions.update');
    Route::post('/dashboard/users/{targetUser}/permissions', [RoleManagementController::class, 'updateUserPermissions'])->name('dashboard.users.permissions.update');
    Route::post('/dashboard/users/{targetUser}/toggle-dashboard', [RoleManagementController::class, 'toggleDashboardAccess'])->name('dashboard.users.toggle-dashboard');
    Route::post('/dashboard/users/{targetUser}/toggle-media', [RoleManagementController::class, 'toggleMediaAccess'])->name('dashboard.users.toggle-media');
    Route::post('/dashboard/users/{targetUser}/status', [RoleManagementController::class, 'updateUserStatus'])->name('dashboard.users.status');
    Route::get('/dashboard/analytics', [AnalyticsController::class, 'index'])->name('dashboard.analytics');
    Route::get('/dashboard/analytics/data', [AnalyticsController::class, 'chartData'])->name('dashboard.analytics.data');

    // Seyid Real Estate Company Actor Dashboard & Operations
    Route::get('/company', [CompanyDashboardController::class, 'index'])->name('company.dashboard');
    Route::post('/company/branches', [CompanyDashboardController::class, 'storeBranch'])->name('company.branches.store');
    Route::put('/company/branches/{branch}', [CompanyDashboardController::class, 'updateBranch'])->name('company.branches.update');
    Route::delete('/company/branches/{branch}', [CompanyDashboardController::class, 'deleteBranch'])->name('company.branches.destroy');
    Route::post('/company/clients', [CompanyDashboardController::class, 'storeClient'])->name('company.clients.store');
    Route::put('/company/clients/{client}', [CompanyDashboardController::class, 'updateClient'])->name('company.clients.update');
    Route::delete('/company/clients/{client}', [CompanyDashboardController::class, 'deleteClient'])->name('company.clients.destroy');

    // Company listing management (SRS: create, edit, publish, suspend, status, delete)
    Route::get('/company/listings/{property}/edit-form', [CompanyListingController::class, 'editForm'])->name('company.listings.edit-form');
    Route::post('/company/listings', [CompanyListingController::class, 'store'])->name('company.listings.store');
    Route::put('/company/listings/{property}', [CompanyListingController::class, 'update'])->name('company.listings.update');
    Route::delete('/company/listings/{property}', [CompanyListingController::class, 'destroy'])->name('company.listings.destroy');
    Route::post('/company/listings/{property}/publish', [CompanyListingController::class, 'togglePublish'])->name('company.listings.publish');
    Route::post('/company/listings/{property}/status/{status}', [CompanyListingController::class, 'setStatus'])->name('company.listings.status');

    Route::post('/dashboard/branches', [WorkerManagementController::class, 'storeBranch'])->name('dashboard.branches.store');
    Route::put('/dashboard/branches/{branch}', [WorkerManagementController::class, 'updateBranch'])->name('dashboard.branches.update');
    Route::delete('/dashboard/branches/{branch}', [WorkerManagementController::class, 'deleteBranch'])->name('dashboard.branches.destroy');

    Route::post('/dashboard/agents', [WorkerManagementController::class, 'storeAgent'])->name('dashboard.agents.store');
    Route::put('/dashboard/agents/{agent}', [WorkerManagementController::class, 'updateAgent'])->name('dashboard.agents.update');
    Route::delete('/dashboard/agents/{agent}', [WorkerManagementController::class, 'deleteAgent'])->name('dashboard.agents.destroy');
    Route::post('/dashboard/users', [UserManagementController::class, 'storeUser'])->name('dashboard.users.store');
    Route::put('/dashboard/users/{user}', [UserManagementController::class, 'updateUser'])->name('dashboard.users.update');
    Route::delete('/dashboard/users/{user}', [UserManagementController::class, 'deleteUser'])->name('dashboard.users.delete');
    Route::post('/dashboard/posts', [WorkerManagementController::class, 'storeBlogPost'])->name('dashboard.posts.store');
    Route::put('/dashboard/posts/{post}', [WorkerManagementController::class, 'updateBlogPost'])->name('dashboard.posts.update');
    Route::post('/dashboard/posts/{post}/toggle-publish', [WorkerManagementController::class, 'togglePublish'])->name('dashboard.posts.togglePublish');
    Route::delete('/dashboard/posts/{post}', [WorkerManagementController::class, 'deleteBlogPost'])->name('dashboard.posts.delete');
    Route::post('/dashboard/settings', [SiteSettingsController::class, 'updateSettings'])->name('dashboard.settings.update');
    Route::post('/dashboard/profile', [WorkerManagementController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/dashboard/testimonials/{testimonial}', [WorkerManagementController::class, 'updateTestimonial'])->name('dashboard.testimonials.update');
    Route::delete('/dashboard/testimonials/{testimonial}', [WorkerManagementController::class, 'deleteTestimonial'])->name('dashboard.testimonials.destroy');
    Route::post('/dashboard/testimonials/{id}/restore', [WorkerManagementController::class, 'restoreTestimonial'])->name('dashboard.testimonials.restore');
    Route::post('/dashboard/testimonials/{testimonial}/toggle-approval', [WorkerManagementController::class, 'toggleTestimonialApproval'])->name('dashboard.testimonials.toggleApproval');
    Route::post('/dashboard/orders/{order}/review', [WorkerManagementController::class, 'reviewOrder'])->name('dashboard.orders.review');
    Route::post('/dashboard/orders/{order}/collect-commission', [WorkerManagementController::class, 'collectOrderCommission'])->name('dashboard.orders.collectCommission');
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
    Route::delete('/dashboard/properties/{property}/images/{image}', [PropertyController::class, 'deleteImage'])->name('dashboard.properties.images.destroy');
    Route::post('/dashboard/properties/{property}/images/{image}/cover', [PropertyController::class, 'setCoverImage'])->name('dashboard.properties.images.cover');
    Route::post('/dashboard/properties/{property}/preset-cover', [PropertyController::class, 'setCoverPreset'])->name('dashboard.properties.preset-cover');
    Route::delete('/dashboard/properties/{property}', [PropertyController::class, 'destroy'])->name('dashboard.properties.destroy');
    // Job applications management
    Route::get('/dashboard/job-applications/{application}/download', [JobApplicationController::class, 'download'])->name('dashboard.job-applications.download');
    Route::patch('/dashboard/job-applications/{application}/status', [JobApplicationController::class, 'updateStatus'])->name('dashboard.job-applications.status');
    Route::delete('/dashboard/job-applications/{application}', [JobApplicationController::class, 'destroy'])->name('dashboard.job-applications.destroy');
});

Route::middleware(['auth', 'agent'])->group(function () {
    Route::get('/agent-portal', [AgentPortalController::class, 'index'])->name('agent.portal');
    Route::patch('/agent-portal/orders/{order}/status', [AgentPortalController::class, 'updateOrderStatus'])->name('agent.orders.status');
    Route::post('/agent-portal/orders/{order}/generate-agreement', [AgentPortalController::class, 'generateAgreement'])->name('agent.orders.generateAgreement');
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
Route::get('/properties/map', [PropertyController::class, 'index'])->defaults('view', 'map')->name('properties.map');
Route::get('/properties/villas', [PropertyController::class, 'index'])->defaults('category', 'villa')->name('properties.villas');
Route::get('/properties/homes', [PropertyController::class, 'index'])->defaults('category', 'home')->name('properties.homes');
Route::get('/properties/apartments', [PropertyController::class, 'index'])->defaults('category', 'apartment')->name('properties.apartments');
Route::get('/properties/guest-houses', [PropertyController::class, 'index'])->defaults('category', 'guest_house')->name('properties.guest_houses');
Route::get('/properties/condominiums', [PropertyController::class, 'index'])->defaults('category', 'condominium')->name('properties.condominiums');
Route::get('/properties/hotels', [PropertyController::class, 'index'])->defaults('category', 'hotel')->name('properties.hotels');

// Property Side-by-Side Comparison Matrix
Route::get('/properties/compare', [PropertyComparisonController::class, 'index'])->name('properties.compare');
Route::post('/properties/compare/clear', [PropertyComparisonController::class, 'clear'])->name('properties.compare.clear');
Route::post('/properties/{property}/compare', [PropertyComparisonController::class, 'toggle'])->name('properties.compare.toggle');

Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');

// Property Reviews, Trust & Safety Reports & Maintenance
Route::post('/properties/{property}/report', [PropertyReportController::class, 'store'])->name('properties.report');
Route::post('/properties/{property}/reviews', [PropertyReviewController::class, 'store'])->middleware('auth')->name('properties.reviews.store');
Route::post('/properties/{property}/maintenance', [MaintenanceController::class, 'store'])->middleware('auth')->name('properties.maintenance.store');

// Module 2: Airbnb-Style Stay Platform & Booking Engine
Route::get('/stays', [StayBookingController::class, 'index'])->name('stays.index');
Route::post('/properties/{property}/stays/calculate', [StayBookingController::class, 'calculate'])->name('stays.calculate');
Route::post('/properties/{property}/stays/book', [StayBookingController::class, 'book'])->name('stays.book');

// Module 4: Property Viewing Appointments & Formal Offer / Multi-Round Negotiation Engine
Route::post('/properties/{property}/viewings', [ViewingOfferController::class, 'scheduleViewing'])->name('properties.viewings.store');
Route::post('/properties/{property}/offers', [ViewingOfferController::class, 'submitOffer'])->name('properties.offers.store');

Route::middleware('auth')->group(function () {
    Route::put('/bookings/{booking}/status', [StayBookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::post('/bookings/{booking}/pay', [StayBookingController::class, 'pay'])->name('bookings.pay');
    Route::delete('/bookings/{booking}', [StayBookingController::class, 'destroy'])->name('bookings.destroy');
    Route::post('/developments', [DevelopmentController::class, 'store'])->name('developments.store');
    Route::put('/developments/{project}', [DevelopmentController::class, 'update'])->name('developments.update');
    Route::delete('/developments/{project}', [DevelopmentController::class, 'destroy'])->name('developments.destroy');
    Route::post('/developments/{project}/units', [DevelopmentController::class, 'storeUnit'])->name('developments.units.store');
    Route::delete('/developments/units/{unit}', [DevelopmentController::class, 'destroyUnit'])->name('developments.units.destroy');
    Route::put('/developments/units/{unit}/status', [DevelopmentController::class, 'updateUnitStatus'])->name('developments.update-unit-status');
    Route::put('/developments/{project}/milestones/{milestone}', [DevelopmentController::class, 'updateMilestone'])->name('developments.update-milestone');

    // Module 4: Viewing Management & Offer Negotiation Actions
    Route::put('/viewings/{viewing}/status', [ViewingOfferController::class, 'updateViewingStatus'])->name('viewings.update-status');
    Route::post('/offers/{offer}/counter', [ViewingOfferController::class, 'counterOffer'])->name('offers.counter');
    Route::put('/offers/{offer}/accept', [ViewingOfferController::class, 'acceptOffer'])->name('offers.accept');
    Route::put('/offers/{offer}/reject', [ViewingOfferController::class, 'rejectOffer'])->name('offers.reject');
    Route::post('/offers/{offer}/convert-to-deal', [ViewingOfferController::class, 'convertToDeal'])->name('offers.convert-to-deal');

    // Property Verification & Legal Document Management (SRS §22–§29, §177)
    Route::post('/properties/{property}/documents', [PropertyVerificationController::class, 'uploadDocument'])->name('properties.documents.upload');
    Route::get('/properties/{property}/documents/{document}/download', [PropertyVerificationController::class, 'downloadDocument'])->name('properties.documents.download');
    Route::delete('/properties/{property}/documents/{document}', [PropertyVerificationController::class, 'deleteDocument'])->name('properties.documents.destroy');
    Route::put('/properties/{property}/verification', [PropertyVerificationController::class, 'updateVerification'])->name('properties.verification.update');
    Route::match(['put', 'patch'], '/properties/{property}/documents/{document}/status', [PropertyVerificationController::class, 'updateDocumentStatus'])->name('properties.documents.update-status');

    // Ethiopian Transaction & Payment Engine (SRS §78–§83)
    Route::put('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::put('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/payments/{payment}/slip', [PaymentController::class, 'viewSlip'])->name('payments.slip');

    // Rental & Lease Management Engine (SRS §84–§88)
    Route::post('/leases', [LeaseManagementController::class, 'store'])->name('leases.store');
    Route::get('/leases/{lease}', [LeaseManagementController::class, 'show'])->name('leases.show');
    Route::get('/leases/{lease}/agreement', [LeaseManagementController::class, 'downloadAgreement'])->name('leases.agreement');
    Route::put('/leases/{lease}/status', [LeaseManagementController::class, 'updateStatus'])->name('leases.update-status');
    Route::post('/rent-schedules/{schedule}/pay', [LeaseManagementController::class, 'recordInstallmentPayment'])->name('rent-schedules.pay');
    Route::post('/leases/process-schedules', [LeaseManagementController::class, 'processSchedules'])->name('leases.process-schedules');

    // Ethiopian In-App Notifications (SRS §106–§115)
    Route::post('/notifications/{id}/read', function (Request $request, string $id) {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    })->name('notifications.read');

    Route::post('/notifications/mark-all-read', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    })->name('notifications.mark-all-read');

    // Financial Reporting, Commission Settlement & Tax Desk (SRS §126–§135)
    Route::get('/finance', [FinancialReportingController::class, 'index'])->name('finance.index');
    Route::post('/commissions/{commission}/settle', [FinancialReportingController::class, 'settleCommission'])->name('commissions.settle');
    Route::get('/commissions/{commission}/voucher', [FinancialReportingController::class, 'voucher'])->name('commissions.voucher');
    Route::get('/finance/export-csv', [FinancialReportingController::class, 'exportCsv'])->name('finance.export-csv');
    Route::get('/finance/statement', [FinancialReportingController::class, 'statement'])->name('finance.statement');
    Route::post('/finance/shifts/{shift}/verify', [FinancialReportingController::class, 'verifyShift'])->name('finance.shifts.verify');

    // Verification Company desk: document verification jobs & property certifications
    Route::get('/verification-company', [VerificationCompanyController::class, 'index'])->name('verification_company.portal');

    // Developer Projects, Buildings, Units & Construction Desk
    Route::get('/developer-portal', [DeveloperPortalController::class, 'index'])->name('developer.portal');

    // Hotel & Guesthouse Front Desk Receptionist & Host Desk
    Route::get('/receptionist', [ReceptionistDashboardController::class, 'index'])->name('receptionist.dashboard');
    Route::post('/receptionist/stays', [ReceptionistDashboardController::class, 'storeStayListing'])->name('receptionist.stays.store');
    Route::put('/receptionist/stays/{property}', [ReceptionistDashboardController::class, 'updateStayListing'])->name('receptionist.stays.update');
    Route::put('/receptionist/stays/{property}/availability', [ReceptionistDashboardController::class, 'updateAvailability'])->name('receptionist.stays.availability');
    Route::post('/receptionist/bookings/{booking}/accept', [ReceptionistDashboardController::class, 'acceptBooking'])->name('receptionist.bookings.accept');
    Route::post('/receptionist/stays/{property}/cleaning', [ReceptionistDashboardController::class, 'storeCleaningRequest'])->name('receptionist.cleaning.store');
    Route::post('/receptionist/reviews/{review}/reply', [ReceptionistDashboardController::class, 'replyToReview'])->name('receptionist.reviews.reply');
    Route::post('/receptionist/check-in/{booking}', [ReceptionistDashboardController::class, 'checkIn'])->name('receptionist.check-in');
    Route::post('/receptionist/check-out/{booking}', [ReceptionistDashboardController::class, 'checkOut'])->name('receptionist.check-out');
    Route::post('/receptionist/walk-in', [ReceptionistDashboardController::class, 'walkIn'])->name('receptionist.walk-in');
    Route::post('/receptionist/service-requests', [ReceptionistDashboardController::class, 'storeServiceRequest'])->name('receptionist.service-requests.store');
    Route::patch('/receptionist/service-requests/{serviceRequest}', [ReceptionistDashboardController::class, 'updateServiceRequest'])->name('receptionist.service-requests.update');
    Route::post('/receptionist/shifts', [ReceptionistDashboardController::class, 'storeShift'])->name('receptionist.shifts.store');
    Route::get('/receptionist/bookings/{booking}/folio', [ReceptionistDashboardController::class, 'folio'])->name('receptionist.bookings.folio');
    Route::patch('/receptionist/inquiries/{inquiry}', [ReceptionistDashboardController::class, 'replyToInquiry'])->name('receptionist.inquiries.reply');
    Route::patch('/receptionist/maintenance/{ticket}', [ReceptionistDashboardController::class, 'updateMaintenanceStatus'])->name('receptionist.maintenance.update');

    // Reviews Moderation, Incident Reporting, Maintenance & Saved Collections
    Route::put('/reviews/{review}/toggle', [PropertyReviewController::class, 'toggleStatus'])->name('reviews.toggle');
    Route::post('/reports', [PropertyReportController::class, 'storeDirect'])->name('reports.store-direct');
    Route::match(['put', 'patch'], '/reports/{report}', [PropertyReportController::class, 'update'])->name('reports.update');
    Route::delete('/reports/{report}', [PropertyReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('/maintenance', [MaintenanceController::class, 'storeGeneral'])->name('maintenance.store');
    Route::put('/maintenance/{ticket}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.update-status');
    Route::post('/collections', [SavedCollectionController::class, 'store'])->name('collections.store');
    Route::post('/collections/{collection}/properties/{property}', [SavedCollectionController::class, 'toggleProperty'])->name('collections.toggleProperty');
    Route::delete('/collections/{collection}', [SavedCollectionController::class, 'destroy'])->name('collections.destroy');

    // Marketing Campaigns
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::patch('/campaigns/{campaign}/status', [CampaignController::class, 'updateStatus'])->name('campaigns.update-status');

    // ── Dedicated Per-User Dashboards & Workflows (All 17 Roles) ───────────
    Route::get('/dashboard/buyer', [BuyerDashboardController::class, 'index'])->name('buyer.dashboard');
    Route::post('/buyer/saved-searches', [BuyerDashboardController::class, 'storeSavedSearch'])->name('buyer.saved-searches.store');
    Route::delete('/buyer/saved-searches/{savedSearch}', [BuyerDashboardController::class, 'destroySavedSearch'])->name('buyer.saved-searches.destroy');

    Route::get('/dashboard/tenant', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');
    Route::get('/dashboard/guest', [GuestDashboardController::class, 'index'])->name('guest.dashboard');

    Route::get('/dashboard/investor', [InvestorDashboardController::class, 'index'])->name('investor.dashboard');
    Route::post('/investor/portfolios', [InvestorDashboardController::class, 'storePortfolio'])->name('investor.portfolios.store');

    Route::get('/dashboard/seller', [SellerDashboardController::class, 'index'])->name('seller.dashboard');
    Route::get('/dashboard/host', [HostDashboardController::class, 'index'])->name('host.dashboard');
    Route::post('/host/reviews/{review}/reply', [HostDashboardController::class, 'replyReview'])->name('host.reviews.reply');
    Route::get('/dashboard/property-manager', [PropertyManagerDashboardController::class, 'index'])->name('property-manager.dashboard');
    Route::post('/property-manager/cleaning/dispatch', [PropertyManagerDashboardController::class, 'dispatchCleaning'])->name('property_manager.cleaning.dispatch');

    Route::get('/dashboard/professional', [ProfessionalDashboardController::class, 'index'])->name('professional.dashboard');
    Route::post('/professional/requests/{serviceRequest}/status', [ProfessionalDashboardController::class, 'updateStatus'])->name('professional.requests.status');
    Route::post('/professional/requests/{serviceRequest}/report', [ProfessionalDashboardController::class, 'uploadReport'])->name('professional.requests.report');
    Route::patch('/professional/requests/{requestItem}', [ProfessionalDashboardController::class, 'updateRequestStatus'])->name('professional.requests.update');

    Route::get('/dashboard/cleaner', [CleanerDashboardController::class, 'index'])->name('cleaner.dashboard');
    Route::patch('/cleaner/tasks/{task}', [CleanerDashboardController::class, 'updateStatus'])->name('cleaner.tasks.update-status');

    Route::get('/dashboard/verification-officer', [VerificationOfficerDashboardController::class, 'index'])->name('verification-officer.dashboard');
    Route::patch('/verification-officer/documents/{document}', [VerificationOfficerDashboardController::class, 'documentDecision'])->name('verification-officer.documents.decision');
    Route::patch('/verification-officer/companies/{agent}', [VerificationOfficerDashboardController::class, 'companyDecision'])->name('verification-officer.companies.decision');
    Route::patch('/verification-officer/developers/{project}', [VerificationOfficerDashboardController::class, 'developerDecision'])->name('verification-officer.developers.decision');

    Route::get('/dashboard/moderator', [ModeratorDashboardController::class, 'index'])->name('moderator.dashboard');
    Route::patch('/moderator/listings/{listing}', [ModeratorDashboardController::class, 'listingDecision'])->name('moderator.listings.decision');
    Route::patch('/moderator/reports/{report}', [ModeratorDashboardController::class, 'reportDecision'])->name('moderator.reports.decision');
    Route::patch('/moderator/reviews/{review}', [ModeratorDashboardController::class, 'reviewDecision'])->name('moderator.reviews.decision');

    Route::get('/dashboard/customer-support', [CustomerSupportDashboardController::class, 'index'])->name('customer-support.dashboard');
    Route::patch('/customer-support/tickets/{ticket}', [CustomerSupportDashboardController::class, 'ticketReply'])->name('customer-support.tickets.reply');

    Route::get('/dashboard/campaign', [CampaignController::class, 'index'])->name('campaign.dashboard');
});

// Public Marketing Campaign Lead Interactivity
Route::post('/campaigns/{campaign}/leads', [CampaignController::class, 'recordLead'])->name('campaigns.leads.store');

// Public Payment Submission & Receipt Routes
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/payments/{transactionNumber}/receipt', [PaymentController::class, 'receipt'])->middleware('auth')->name('payments.receipt');

// Permission-Gated Media & Document Access (Phase 4)
Route::get('/media/properties/{image}', [MediaAccessController::class, 'servePropertyImage'])->name('media.property-image');
Route::get('/media/avatars/{targetUser}', [MediaAccessController::class, 'serveAvatar'])->name('media.avatar');
Route::get('/media/documents/{document}', [MediaAccessController::class, 'serveDocument'])->name('media.document');

// Multilingual Language Switcher (English, Amharic, Afan Oromo)
Route::get('/locale/{lang}', function (string $lang, Request $request) {
    $supported = ['en', 'am', 'om'];
    if (in_array($lang, $supported, true)) {
        session(['locale' => $lang]);
        cookie()->queue(cookie('app_locale', $lang, 60 * 24 * 365, null, null, false, false));
    }

    if ($request->wantsJson()) {
        return response()->json(['success' => true, 'locale' => $lang]);
    }

    return back();
})->name('locale.switch');

// GIS Subcity Polygon Boundaries GeoJSON Export
Route::get('/api/geo/subcities/boundaries', [SubcityBoundaryController::class, 'index'])->name('geo.subcities.boundaries');

// Stay iCal Calendar Synchronization
Route::get('/stays/{property}/calendar.ics', [StayCalendarSyncController::class, 'export'])->name('stays.calendar.ics');
Route::middleware('auth')->group(function () {
    Route::post('/host/properties/{property}/calendar-feeds', [StayCalendarSyncController::class, 'storeFeed'])->name('host.calendar-feeds.store');
    Route::post('/host/calendar-feeds/{feed}/sync', [StayCalendarSyncController::class, 'syncFeed'])->name('host.calendar-feeds.sync');
    Route::delete('/host/calendar-feeds/{feed}', [StayCalendarSyncController::class, 'destroyFeed'])->name('host.calendar-feeds.destroy');
});

// Ethiopian Cultural Experiences & Tour Marketplace
Route::get('/experiences', [ExperienceController::class, 'index'])->name('experiences.index');
Route::get('/experiences/{experience:slug}', [ExperienceController::class, 'show'])->name('experiences.show');
Route::post('/experiences/{experience}/book', [ExperienceController::class, 'book'])->name('experiences.book');

// Corporate Tenancy & Workforce Housing Management
Route::middleware('auth')->group(function () {
    Route::get('/corporate-portal', [CorporateTenancyController::class, 'portal'])->name('corporate.portal');
    Route::post('/corporate-portal/employees', [CorporateTenancyController::class, 'storeEmployee'])->name('corporate.employees.store');
    Route::post('/corporate-portal/assign-lease', [CorporateTenancyController::class, 'assignLease'])->name('corporate.assign-lease');
    Route::get('/corporate-portal/{tenant}/statement', [CorporateTenancyController::class, 'billingStatement'])->name('corporate.billing-statement');

    // Developer Milestone Escrow Disbursements
    Route::post('/projects/{project}/escrow-disbursement', [CorporateTenancyController::class, 'requestEscrowDisbursement'])->name('developer.escrow.request');
    Route::post('/escrow-disbursements/{disbursement}/approve', [CorporateTenancyController::class, 'approveEscrowDisbursement'])->name('developer.escrow.approve');
});
