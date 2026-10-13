<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Booking;
use App\Models\GuestServiceRequest;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyOffer;
use App\Models\PropertyReport;
use App\Models\PropertyReview;
use App\Models\PropertyViewing;
use App\Models\RentSchedule;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Role-specific desk for personas without a dedicated dashboard.
 *
 * Renders the persona's own sidebar sections (from config('personas.personas')
 * `sections`), each gated by the user's granted permissions and backed by the
 * platform data that persona works with.
 */
class RolePortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $persona = $this->resolvePersona($user);

        $isUniversalPortalRole = $persona !== null && ($persona['portal'] ?? null) === 'role.portal';
        $hasCustomOverrides = $user->permissionOverrides()->where('granted', true)->exists()
            || $user->userPermissions()->where('granted', true)->exists();

        abort_unless($isUniversalPortalRole || $user->isAdmin() || $hasCustomOverrides, 403);

        if (! $persona) {
            $persona = [
                'key' => 'universal',
                'label' => ucwords(str_replace('_', ' ', $user->role ?? 'User')),
                'description' => 'Universal capability desk.',
                'role' => $user->role,
                'portal' => 'role.portal',
                'permissions' => [],
            ];
        }

        $personaSections = collect($persona['sections'] ?? []);

        // Include any sections matching permissions the user actually holds
        $extraSections = collect(config('personas.personas', []))
            ->flatMap(fn (array $p) => $p['sections'] ?? [])
            ->unique('key')
            ->filter(fn (array $section) => collect($section['permissions'] ?? [])
                ->contains(fn (string $permission) => $user->hasPermission($permission)));

        $sections = $personaSections->concat($extraSections)
            ->unique('key')
            ->filter(fn (array $section) => collect($section['permissions'] ?? [])
                ->contains(fn (string $permission) => $user->hasPermission($permission)))
            ->values();

        $sectionData = [];
        foreach ($sections as $section) {
            $sectionData[$section['key']] = $this->loadSectionData($persona['key'], $section['key'], $user);
        }

        return view('persona.desk', [
            'persona' => $persona,
            'sections' => $sections,
            'sectionData' => $sectionData,
            'openQueueCount' => collect($sectionData)->sum('count'),
            'grantedCount' => count($user->grantedPermissionKeys()),
        ]);
    }

    /**
     * Resolve the catalog persona for the user's role, following legacy roles.
     */
    private function resolvePersona(User $user): ?array
    {
        return collect(config('personas.personas', []))
            ->first(fn (array $persona) => ($persona['role'] ?? null) === $user->role
                || in_array($user->role ?? '', $persona['legacy_roles'] ?? [], true));
    }

    /**
     * Load the queue/count/columns for one desk section. Sections are scoped to
     * the user's own records where the schema links them (leases, offers,
     * bookings, documents); otherwise they show the platform-wide queue that
     * this role works through.
     *
     * @return array{count: int, columns: array<int, array{key: string, label: string}>, rows: Collection}
     */
    private function loadSectionData(string $personaKey, string $sectionKey, User $user): array
    {
        $landlordPropertyIds = fn () => Lease::query()->where('landlord_user_id', $user->id)->pluck('property_id');
        $landlordLeaseIds = fn () => Lease::query()->where('landlord_user_id', $user->id)->pluck('id');

        [$query, $columns] = match ($sectionKey) {
            'listings_review' => [
                Listing::query()->whereIn('status', ['pending_review', 'draft']),
                [['key' => 'listing_code', 'label' => 'Code'], ['key' => 'title', 'label' => 'Listing'], ['key' => 'listing_type', 'label' => 'Type'], ['key' => 'price', 'label' => 'Price'], ['key' => 'status', 'label' => 'Status']],
            ],
            'suspicious_ads' => [
                Listing::query()->whereIn('status', ['pending_review', 'paused']),
                [['key' => 'listing_code', 'label' => 'Code'], ['key' => 'title', 'label' => 'Listing'], ['key' => 'is_featured', 'label' => 'Featured'], ['key' => 'status', 'label' => 'Status']],
            ],
            'reports', 'disputes' => [
                PropertyReport::query()->with('property'),
                [['key' => 'report_number', 'label' => 'Report'], ['key' => 'subject', 'label' => 'Subject'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'reason', 'label' => 'Reason'], ['key' => 'status', 'label' => 'Status']],
            ],
            'content' => [
                PropertyReview::query()->with('property'),
                [['key' => 'review_title', 'label' => 'Review'], ['key' => 'rating', 'label' => 'Rating'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'status', 'label' => 'Status']],
            ],
            'tickets' => [
                Inquiry::query(),
                [['key' => 'name', 'label' => 'From'], ['key' => 'email', 'label' => 'Email'], ['key' => 'subject', 'label' => 'Subject'], ['key' => 'status', 'label' => 'Status'], ['key' => 'created_at', 'label' => 'Received']],
            ],
            'payment_issues' => [
                Payment::query(),
                [['key' => 'transaction_number', 'label' => 'Transaction'], ['key' => 'payer_name', 'label' => 'Payer'], ['key' => 'payment_type', 'label' => 'Type'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'status', 'label' => 'Status']],
            ],
            'properties' => match ($personaKey) {
                'landlord' => [
                    Property::query()->whereIn('id', $landlordPropertyIds()),
                    [['key' => 'title', 'label' => 'Property'], ['key' => 'city', 'label' => 'City'], ['key' => 'type', 'label' => 'Type'], ['key' => 'price', 'label' => 'Price'], ['key' => 'verification_status', 'label' => 'Verification']],
                ],
                default => [
                    Property::query()->whereIn('id', PropertyDocument::query()->where('user_id', $user->id)->pluck('property_id')),
                    [['key' => 'title', 'label' => 'Property'], ['key' => 'city', 'label' => 'City'], ['key' => 'type', 'label' => 'Type'], ['key' => 'price', 'label' => 'Price'], ['key' => 'verification_status', 'label' => 'Verification']],
                ],
            },
            'tenants' => [
                Lease::query()->where('landlord_user_id', $user->id),
                [['key' => 'tenant_name', 'label' => 'Tenant'], ['key' => 'tenant_email', 'label' => 'Email'], ['key' => 'tenant_phone', 'label' => 'Phone'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'status', 'label' => 'Status']],
            ],
            'leases' => [
                Lease::query()->where('landlord_user_id', $user->id)->with('property'),
                [['key' => 'lease_code', 'label' => 'Lease'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'start_date', 'label' => 'Start'], ['key' => 'end_date', 'label' => 'End'], ['key' => 'monthly_rent', 'label' => 'Monthly Rent'], ['key' => 'status', 'label' => 'Status']],
            ],
            'rent' => [
                RentSchedule::query()->whereIn('lease_id', $landlordLeaseIds())->with('lease'),
                [['key' => 'lease.lease_code', 'label' => 'Lease'], ['key' => 'installment_number', 'label' => '#'], ['key' => 'period_label', 'label' => 'Period'], ['key' => 'due_date', 'label' => 'Due'], ['key' => 'amount_due', 'label' => 'Amount Due'], ['key' => 'status', 'label' => 'Status']],
            ],
            'maintenance' => $personaKey === 'landlord'
                ? [
                    MaintenanceTicket::query()->whereIn('property_id', $landlordPropertyIds())->with('property'),
                    [['key' => 'ticket_number', 'label' => 'Ticket'], ['key' => 'title', 'label' => 'Issue'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'priority', 'label' => 'Priority'], ['key' => 'status', 'label' => 'Status']],
                ]
                : [
                    MaintenanceTicket::query()->with('property'),
                    [['key' => 'ticket_number', 'label' => 'Ticket'], ['key' => 'title', 'label' => 'Issue'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'priority', 'label' => 'Priority'], ['key' => 'status', 'label' => 'Status']],
                ],
            'buildings_units' => [
                Project::query()->withCount(['buildings', 'units']),
                [['key' => 'project_code', 'label' => 'Code'], ['key' => 'name', 'label' => 'Project'], ['key' => 'city', 'label' => 'City'], ['key' => 'buildings_count', 'label' => 'Buildings'], ['key' => 'units_count', 'label' => 'Units'], ['key' => 'construction_status', 'label' => 'Status']],
            ],
            'guests' => [
                Booking::query(),
                [['key' => 'guest_name', 'label' => 'Guest'], ['key' => 'guest_email', 'label' => 'Email'], ['key' => 'guest_phone', 'label' => 'Phone'], ['key' => 'check_in_date', 'label' => 'Check-in'], ['key' => 'status', 'label' => 'Status']],
            ],
            'bookings' => $personaKey === 'property_owner'
                ? [
                    Booking::query()->where('user_id', $user->id)->with('property'),
                    [['key' => 'booking_reference', 'label' => 'Booking'], ['key' => 'guest_name', 'label' => 'Guest'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'check_in_date', 'label' => 'Check-in'], ['key' => 'total_amount', 'label' => 'Total'], ['key' => 'status', 'label' => 'Status']],
                ]
                : [
                    Booking::query()->with('property'),
                    [['key' => 'booking_reference', 'label' => 'Booking'], ['key' => 'guest_name', 'label' => 'Guest'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'check_in_date', 'label' => 'Check-in'], ['key' => 'total_amount', 'label' => 'Total'], ['key' => 'status', 'label' => 'Status']],
                ],
            'housekeeping' => [
                GuestServiceRequest::query()->with('property'),
                [['key' => 'service_type', 'label' => 'Service'], ['key' => 'guest_name', 'label' => 'Guest'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'priority', 'label' => 'Priority'], ['key' => 'status', 'label' => 'Status']],
            ],
            'jobs' => [
                GuestServiceRequest::query()->where('status', 'pending')->with('property'),
                [['key' => 'service_type', 'label' => 'Job'], ['key' => 'description', 'label' => 'Details'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'priority', 'label' => 'Priority'], ['key' => 'created_at', 'label' => 'Requested']],
            ],
            'documents', 'property_documents' => [
                PropertyDocument::query()->with('property'),
                [['key' => 'document_type', 'label' => 'Document'], ['key' => 'title', 'label' => 'Title'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'status', 'label' => 'Status'], ['key' => 'created_at', 'label' => 'Submitted']],
            ],
            'payments' => [
                Payment::query(),
                [['key' => 'transaction_number', 'label' => 'Transaction'], ['key' => 'payer_name', 'label' => 'Payer'], ['key' => 'payment_type', 'label' => 'Type'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'status', 'label' => 'Status']],
            ],
            'identity_verifications' => [
                User::query(),
                [['key' => 'name', 'label' => 'User'], ['key' => 'email', 'label' => 'Email'], ['key' => 'role', 'label' => 'Role'], ['key' => 'created_at', 'label' => 'Registered']],
            ],
            'company_verifications' => [
                Agent::query()->where('license_verified', false),
                [['key' => 'name', 'label' => 'Company'], ['key' => 'email', 'label' => 'Email'], ['key' => 'phone', 'label' => 'Phone'], ['key' => 'license_verified', 'label' => 'License Verified'], ['key' => 'created_at', 'label' => 'Applied']],
            ],
            'developer_verifications' => [
                Project::query(),
                [['key' => 'project_code', 'label' => 'Code'], ['key' => 'name', 'label' => 'Project'], ['key' => 'developer_name', 'label' => 'Developer'], ['key' => 'developer_verified', 'label' => 'Verified'], ['key' => 'construction_status', 'label' => 'Status']],
            ],
            'offers' => [
                PropertyOffer::query()->where('user_id', $user->id)->with('property'),
                [['key' => 'offer_reference', 'label' => 'Offer'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'offered_price', 'label' => 'Offered'], ['key' => 'status', 'label' => 'Status'], ['key' => 'created_at', 'label' => 'Sent']],
            ],
            'viewings' => [
                PropertyViewing::query()->where('user_id', $user->id)->with('property'),
                [['key' => 'viewing_reference', 'label' => 'Viewing'], ['key' => 'property.title', 'label' => 'Property'], ['key' => 'viewing_date', 'label' => 'Date'], ['key' => 'status', 'label' => 'Status']],
            ],
            'inquiries' => [
                Inquiry::query(),
                [['key' => 'name', 'label' => 'From'], ['key' => 'email', 'label' => 'Email'], ['key' => 'subject', 'label' => 'Subject'], ['key' => 'status', 'label' => 'Status'], ['key' => 'created_at', 'label' => 'Received']],
            ],
            default => [null, []],
        };

        if ($query === null) {
            return ['count' => 0, 'columns' => [], 'rows' => collect()];
        }

        $actions = match ($sectionKey) {
            'listings_review', 'suspicious_ads' => $personaKey === 'moderator' ? ['listing_moderation' => true] : [],
            'reports' => $personaKey === 'moderator' ? ['resolve_report' => true] : [],
            'content' => $personaKey === 'moderator' ? ['review_moderation' => true] : [],
            'property_documents' => $personaKey === 'verification_officer' ? ['document_decision' => true] : [],
            'company_verifications' => $personaKey === 'verification_officer' ? ['company_verification' => true] : [],
            'developer_verifications' => $personaKey === 'verification_officer' ? ['developer_verification' => true] : [],
            'maintenance' => in_array($personaKey, ['landlord', 'property_manager'], true) ? ['maintenance_manage' => true] : [],
            'housekeeping' => $personaKey === 'property_manager' ? ['housekeeping_manage' => true] : [],
            'rent' => $personaKey === 'landlord' ? ['rent_payment' => true] : [],
            'jobs' => $personaKey === 'service_provider' ? ['service_request_response' => true] : [],
            'tickets' => $personaKey === 'customer_support' ? ['ticket_triage' => true] : [],
            'disputes' => $personaKey === 'customer_support' ? ['resolve_report' => true] : [],
            'bookings' => $personaKey === 'property_owner' ? ['booking_status' => true] : [],
            default => [],
        };

        $sectionData = [
            'count' => (clone $query)->count(),
            'columns' => $columns,
            'rows' => $query->latest()->take(15)->get(),
            'actions' => $actions,
        ];

        if ($sectionKey === 'maintenance' && in_array($personaKey, ['landlord', 'property_manager'], true)) {
            $sectionData['createForm'] = ['label' => 'File Maintenance Ticket', 'route' => route('maintenance.store')];
            $sectionData['propertyOptions'] = $personaKey === 'landlord'
                ? Property::query()->whereIn('id', Lease::query()->where('landlord_user_id', $user->id)->pluck('property_id'))->orderBy('title')->get()
                : Property::query()->where('is_active', true)->orderBy('title')->limit(100)->get();
        }

        return $sectionData;
    }
}
