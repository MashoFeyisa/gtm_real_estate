<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'dashboard_access_enabled', 'media_access_enabled', 'photo_path', 'portal_bg_path', 'assigned_property_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'active',
        'dashboard_access_enabled' => true,
        'media_access_enabled' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'dashboard_access_enabled' => 'boolean',
            'media_access_enabled' => 'boolean',
        ];
    }

    /**
     * Public URL of the user's uploaded profile photo, if they have one.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/'.$this->photo_path);
        }

        return null;
    }

    /**
     * Public URL of the user's portal banner background, falling back to skyline hero.
     */
    public function getPortalBgUrlAttribute(): string
    {
        if ($this->portal_bg_path) {
            if (str_starts_with($this->portal_bg_path, 'http') || str_starts_with($this->portal_bg_path, 'images/')) {
                return asset($this->portal_bg_path);
            }
            if (Storage::disk('public')->exists($this->portal_bg_path)) {
                return asset('storage/'.$this->portal_bg_path);
            }
        }

        return asset('images/luxury/hero-skyline.jpg');
    }

    /**
     * Up to two letter initials, used when the user has no profile photo.
     */
    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim((string) $this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? '') === 'admin';
    }

    public function isFinanceOfficer(): bool
    {
        return ($this->role ?? '') === 'finance_officer';
    }

    public function isReceptionist(): bool
    {
        return ($this->role ?? '') === 'receptionist';
    }

    public function isDeveloper(): bool
    {
        return ($this->role ?? '') === 'developer';
    }

    public function isVerificationCompany(): bool
    {
        return ($this->role ?? '') === 'verification_company';
    }

    public function isCampaignOfficer(): bool
    {
        return ($this->role ?? '') === 'campaign_officer';
    }

    public function canManageCampaigns(): bool
    {
        if ($this->isCampaignOfficer()) {
            return true;
        }

        return $this->permissionOverrides()
            ->whereHas('permission', fn ($q) => $q->whereIn('key', ['campaigns.manage', 'campaigns.approve']))
            ->where('granted', true)
            ->exists()
            || $this->userPermissions()
                ->whereHas('permission', fn ($q) => $q->whereIn('key', ['campaigns.manage', 'campaigns.approve']))
                ->where('granted', true)
                ->exists();
    }

    public function isCompany(): bool
    {
        return in_array($this->role ?? '', ['company', 'company_admin', 'branch_manager', 'real_estate_company'], true);
    }

    public function canAccessCompanyDashboard(): bool
    {
        if ($this->isAdmin() || $this->isCompany()) {
            return true;
        }

        $catalog = $this->permissionCatalog();
        if (! empty($catalog)) {
            return $this->hasAnyPermission(['agents.create', 'agents.update', 'branches.create', 'branches.view']);
        }

        return false;
    }

    public function hasRole(string $role): bool
    {
        if ($this->role === $role) {
            return true;
        }

        return $this->roles()
            ->where(fn ($query) => $query->where('roles.slug', $role)->orWhere('roles.name', $role))
            ->exists();
    }

    public function isPropertyManager(): bool
    {
        return $this->isAdmin() || $this->role === 'property_manager' || $this->hasRole('property_manager');
    }

    public function canAccessPropertyManagerDashboard(): bool
    {
        if ($this->isAdmin() || $this->isPropertyManager()) {
            return true;
        }

        $catalog = $this->permissionCatalog();
        if (! empty($catalog)) {
            return $this->hasAnyPermission([
                'buildings.manage',
                'units.manage',
                'tenants.manage',
                'housekeeping.manage',
                'units.create',
                'buildings.create',
            ]);
        }

        return $this->hasPermission('units.create')
            || $this->hasPermission('buildings.create');
    }

    public function getDashboardUrlAttribute(): string
    {
        if ($this->isAdmin() || $this->role === 'super_admin') {
            return route('dashboard');
        }

        return match ($this->role) {
            'buyer' => route('buyer.dashboard'),
            'tenant', 'renter' => route('tenant.dashboard'),
            'guest', 'traveler' => route('guest.dashboard'),
            'investor' => route('investor.dashboard'),
            'seller', 'landlord', 'property_owner' => route('seller.dashboard'),
            'host' => route('host.dashboard'),
            'agent' => route('agent.portal'),
            'company_admin', 'branch_manager', 'company', 'real_estate_company' => route('company.dashboard'),
            'developer' => route('developer.portal'),
            'property_manager' => route('property-manager.dashboard'),
            'professional', 'service_provider' => route('professional.dashboard'),
            'cleaner', 'housekeeping' => route('cleaner.dashboard'),
            'verification_officer' => route('verification-officer.dashboard'),
            'moderator' => route('moderator.dashboard'),
            'finance_officer' => route('finance.index'),
            'customer_support' => route('customer-support.dashboard'),
            'receptionist' => route('receptionist.dashboard'),
            'verification_company' => route('verification_company.portal'),
            'campaign_officer' => route('campaign.dashboard'),
            default => $this->agentProfile
                ? route('agent.portal')
                : (in_array($this->role ?? '', config('personas.generic_portal_roles', []), true)
                    ? route('role.portal')
                    : route('client.portal')),
        };
    }

    public function canAccessReceptionDesk(): bool
    {
        return $this->isAdmin() || $this->isReceptionist();
    }

    /**
     * Roles allowed to operate the Financial Audit & Tax, Payments & Ledger desk.
     */
    public function isFinanceStaff(): bool
    {
        return $this->isAdmin() || $this->isFinanceOfficer() || $this->isCompany();
    }

    /**
     * Roles allowed to register new lease agreements (Finance Officers are excluded).
     */
    public function canCreateLease(): bool
    {
        return $this->isAdmin() || (bool) $this->agentProfile;
    }

    public function canCreateContent(): bool
    {
        return $this->isAdmin();
    }

    public function canManageWorkers(): bool
    {
        return $this->isAdmin();
    }

    public function agentProfile(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function ensureAgentProfile(): Agent
    {
        $profile = $this->agentProfile;
        if (! $profile) {
            $profile = Agent::firstOrCreate(
                ['user_id' => $this->id],
                [
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone ?? '0911000000',
                    'is_active' => true,
                ]
            );
            $this->unsetRelation('agentProfile');
        }

        return $profile;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class)->latest();
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class)->latest();
    }

    public function cleaningTasks(): HasMany
    {
        return $this->hasMany(CleaningTask::class, 'cleaner_user_id')->latest();
    }

    public function professionalRequests(): HasMany
    {
        return $this->hasMany(ProfessionalServiceRequest::class, 'professional_user_id')->latest();
    }

    public function investorPortfolios(): HasMany
    {
        return $this->hasMany(InvestorPortfolio::class)->latest();
    }

    /**
     * Per-user permission overrides (granted beyond, or denied against, the
     * role defaults).
     */
    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(UserPermissionOverride::class);
    }

    /**
     * Determine whether the user holds a permission, honouring per-user
     * overrides, user_permissions, user_roles, and role defaults.
     */
    public function hasPermission(string $permissionKey): bool
    {
        if ($this->status === 'suspended') {
            return false;
        }

        if ($permissionKey === 'dashboard.view' && ! $this->dashboard_access_enabled) {
            return false;
        }

        if (in_array($permissionKey, ['media.view', 'photos.view', 'media.upload', 'photos.create'], true) && ! $this->media_access_enabled) {
            return false;
        }

        // Admins hold every permission by definition; RBAC grants constrain delegated roles.
        if ($this->isAdmin()) {
            return true;
        }

        $catalog = $this->permissionCatalog();

        $aliasMap = [
            'property.view' => 'properties.view',
            'property.create' => 'properties.create',
            'property.update' => 'properties.update',
            'property.delete' => 'properties.delete',
            'property.approve' => 'properties.approve',
            'property.verify' => 'properties.verify',
            'booking.view' => 'bookings.view',
            'booking.create' => 'bookings.create',
            'booking.cancel' => 'bookings.cancel',
            'payment.view' => 'payments.view',
            'payment.create' => 'payments.create',
            'document.view' => 'documents.view',
            'media.view' => 'photos.view',
            'media.upload' => 'photos.create',
        ];

        $keysToCheck = [$permissionKey];
        if (isset($aliasMap[$permissionKey])) {
            $keysToCheck[] = $aliasMap[$permissionKey];
        }
        $reverseMap = array_flip($aliasMap);
        if (isset($reverseMap[$permissionKey])) {
            $keysToCheck[] = $reverseMap[$permissionKey];
        }

        // If none of the keys exist in catalog, fail open (legacy behavior)
        $knownKeys = array_filter($keysToCheck, fn ($k) => array_key_exists($k, $catalog));
        if (empty($knownKeys)) {
            return true;
        }

        foreach ($knownKeys as $k) {
            if ($this->checkSingleCatalogKey($k, $catalog)) {
                return true;
            }
        }

        return false;
    }

    protected function checkSingleCatalogKey(string $key, array $catalog): bool
    {
        $permissionId = $catalog[$key];

        // Direct user permissions / overrides
        $userPerm = $this->userPermissions()->where('permission_id', $permissionId)->first();
        if ($userPerm) {
            return (bool) $userPerm->granted;
        }

        $override = $this->permissionOverrides()->where('permission_id', $permissionId)->first();
        if ($override) {
            return (bool) $override->granted;
        }

        // Check user_roles table
        $userRoleIds = $this->roles()->pluck('roles.id')->all();
        if (! empty($userRoleIds) && RolePermission::query()->whereIn('role_id', $userRoleIds)->where('permission_id', $permissionId)->exists()) {
            return true;
        }

        // Role permissions by role string
        if ($this->role && RolePermission::query()->where('role', $this->role)->where('permission_id', $permissionId)->exists()) {
            return true;
        }

        // Persona default configuration fallback
        $roleConfig = collect(config('personas.personas', []))->first(function ($p) {
            return ($p['role'] ?? null) === $this->role || in_array($this->role, $p['legacy_roles'] ?? [], true);
        });
        if ($roleConfig && isset($roleConfig['permissions'][$key])) {
            return true;
        }

        return false;
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    public function hasAnyPermission(array $permissionKeys): bool
    {
        foreach ($permissionKeys as $permissionKey) {
            if ($this->hasPermission($permissionKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Every catalog permission key the user currently holds.
     *
     * @return list<string>
     */
    public function grantedPermissionKeys(): array
    {
        $catalog = $this->permissionCatalog();

        if ($catalog === []) {
            return [];
        }

        if ($this->isAdmin()) {
            return array_keys($catalog);
        }

        $roleGrantIds = array_flip(RolePermission::query()->where('role', $this->role)->pluck('permission_id')->all());
        $overrides = $this->permissionOverrides()->get()->keyBy('permission_id');

        $granted = [];

        foreach ($catalog as $key => $permissionId) {
            $override = $overrides->get($permissionId);

            $isGranted = $override instanceof UserPermissionOverride
                ? $override->granted
                : array_key_exists($permissionId, $roleGrantIds);

            if ($isGranted) {
                $granted[] = $key;
            }
        }

        return $granted;
    }

    /**
     * Catalog map of permission key => permission id (cached per instance).
     *
     * @return array<string, int>
     */
    public function permissionCatalog(): array
    {
        if ($this->permissionCatalogCache === null) {
            $this->permissionCatalogCache = Permission::query()->pluck('id', 'key')->all();
        }

        return $this->permissionCatalogCache;
    }

    /**
     * @var array<string, int>|null
     */
    protected ?array $permissionCatalogCache = null;

    /**
     * @var array<string, bool>
     */
    protected array $resolvedPermissionCache = [];

    public function tenantLeases(): HasMany
    {
        return $this->hasMany(Lease::class, 'tenant_user_id')->latest();
    }

    public function propertyOffers(): HasMany
    {
        return $this->hasMany(PropertyOffer::class, 'user_id')->latest();
    }

    public function propertyViewings(): HasMany
    {
        return $this->hasMany(PropertyViewing::class, 'user_id')->latest();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id')->latest();
    }

    public function unitReservations(): HasMany
    {
        return $this->hasMany(UnitReservation::class, 'user_id')->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id')->latest();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'email', 'email')->latest();
    }

    public function likedProperties(): HasMany
    {
        return $this->hasMany(PropertyLike::class);
    }

    public function propertyLikes(): HasMany
    {
        return $this->hasMany(PropertyLike::class);
    }

    public function favoritedProperties(): HasMany
    {
        return $this->hasMany(PropertyFavorite::class);
    }

    public function propertyFavorites(): HasMany
    {
        return $this->hasMany(PropertyFavorite::class);
    }

    public function savedCollections(): HasMany
    {
        return $this->hasMany(SavedCollection::class)->latest();
    }

    public function maintenanceTickets(): HasMany
    {
        return $this->hasMany(MaintenanceTicket::class)->latest();
    }

    public function propertyReviews(): HasMany
    {
        return $this->hasMany(PropertyReview::class)->latest();
    }

    public function propertyReports(): HasMany
    {
        return $this->hasMany(PropertyReport::class)->latest();
    }

    public function assignedProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'assigned_property_id');
    }

    public function frontDeskShifts(): HasMany
    {
        return $this->hasMany(FrontDeskShift::class, 'receptionist_user_id')->latest();
    }

    public function hostedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'host_id')->latest();
    }

    public function isHost(): bool
    {
        return $this->isReceptionist() || $this->role === 'host' || $this->hostedProperties()->exists();
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class)->latest();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }
}
