<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ConstructionMilestone;
use App\Models\DeveloperPaymentPlan;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use App\Models\UnitReservation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeveloperPortalController extends Controller
{
    /**
     * Developer management desk: projects, buildings, units, construction
     * progress, pricing, payment plans, buyers, and reservations.
     *
     * Every nav entry and section is gated by the developer's own permission
     * for that area, so the dashboard reflects the role's grants.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('projects.create') || $user->hasPermission('projects.view')), 403, 'Unauthorized access to the developer portal.');

        $canManageProjects = $user->hasPermission('projects.create');
        $canManageBuildings = $user->hasPermission('buildings.create');
        $canManageUnits = $user->hasPermission('units.create');
        $canManageConstruction = $user->hasPermission('construction.manage');
        $canManagePricing = $user->hasPermission('pricing.manage');
        $canManagePaymentPlans = $user->hasPermission('payment_plans.manage');
        $canManageBuyers = $user->hasPermission('buyers.manage');
        $canManageReservations = $user->hasPermission('reservations.manage');

        $projects = Project::query()
            ->withCount(['buildings', 'units'])
            ->with('milestones')
            ->latest()
            ->get();

        $projectIds = $projects->pluck('id');

        $buildings = ProjectBuilding::query()
            ->with('project')
            ->withCount('units')
            ->whereIn('project_id', $projectIds)
            ->orderBy('project_id')
            ->get();

        $units = ProjectUnit::query()
            ->with(['project', 'building'])
            ->whereIn('project_id', $projectIds)
            ->latest()
            ->get();

        $milestones = ConstructionMilestone::query()
            ->with('project')
            ->whereIn('project_id', $projectIds)
            ->orderBy('project_id')
            ->orderBy('order_index')
            ->get();

        $paymentPlans = DeveloperPaymentPlan::query()
            ->with('project')
            ->whereIn('project_id', $projectIds)
            ->orderBy('project_id')
            ->orderBy('order_index')
            ->get();

        $reservations = UnitReservation::query()
            ->with(['project', 'unit', 'user'])
            ->whereIn('project_id', $projectIds)
            ->latest()
            ->get();

        $pricingRows = $projects->map(function (Project $project) use ($units) {
            $projectUnits = $units->where('project_id', $project->id);

            return [
                'project' => $project,
                'unit_count' => $projectUnits->count(),
                'min_price' => $projectUnits->min('price'),
                'max_price' => $projectUnits->max('price'),
                'available_count' => $projectUnits->where('status', 'available')->count(),
                'reserved_count' => $projectUnits->where('status', 'reserved')->count(),
                'sold_count' => $projectUnits->where('status', 'sold')->count(),
            ];
        });

        $stats = [
            'projects' => $projects->count(),
            'under_construction' => $projects->whereIn('construction_status', ['planning', 'pre_construction', 'under_construction', 'near_completion'])->count(),
            'buildings' => $buildings->count(),
            'units' => $units->count(),
            'available_units' => $units->where('status', 'available')->count(),
            'reservations' => $reservations->count(),
            'pending_reservations' => $reservations->where('status', 'pending')->count(),
            'buyers' => $reservations->pluck('buyer_email')->unique()->count(),
            'avg_completion' => $projects->count() > 0 ? (int) round($projects->avg('overall_completion_percentage')) : 0,
        ];

        return view('dashboards.developer', [
            'projects' => $projects,
            'buildings' => $buildings,
            'units' => $units,
            'milestones' => $milestones,
            'paymentPlans' => $paymentPlans,
            'reservations' => $reservations,
            'pricingRows' => $pricingRows,
            'stats' => $stats,
            'canManageProjects' => $canManageProjects,
            'canManageBuildings' => $canManageBuildings,
            'canManageUnits' => $canManageUnits,
            'canManageConstruction' => $canManageConstruction,
            'canManagePricing' => $canManagePricing,
            'canManagePaymentPlans' => $canManagePaymentPlans,
            'canManageBuyers' => $canManageBuyers,
            'canManageReservations' => $canManageReservations,
        ]);
    }

    /**
     * Store a building under a project.
     */
    public function storeBuilding(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('buildings.create')), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'total_floors' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['project_id'] = $project->id;
        $validated['units_count'] = 0;
        $building = ProjectBuilding::create($validated);
        $project->increment('total_buildings_count');

        AuditLog::log(action: 'developer.building_created', entity: $building, reason: "Created building {$building->name} on project {$project->name}", user: $user);

        return redirect()->route('developer.portal', ['#buildings'])->with('success', "Building {$building->name} created successfully.");
    }

    /**
     * Delete a building.
     */
    public function destroyBuilding(Request $request, ProjectBuilding $building): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('buildings.create')), 403);

        AuditLog::log(action: 'developer.building_deleted', entity: $building, reason: "Removed building {$building->name}", user: $user);
        $building->project?->decrement('total_buildings_count');
        $building->delete();

        return redirect()->route('developer.portal', ['#buildings'])->with('success', 'Building removed successfully.');
    }

    /**
     * Bulk generate units across floors for a building.
     */
    public function generateBulkUnits(Request $request, ProjectBuilding $building): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('units.create')), 403);

        $validated = $request->validate([
            'floor_start' => ['required', 'integer', 'min:1'],
            'floor_end' => ['required', 'integer', 'gte:floor_start'],
            'units_per_floor' => ['required', 'integer', 'min:1', 'max:20'],
            'unit_type' => ['required', 'string'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:1'],
            'area_sqm' => ['required', 'numeric', 'min:10'],
            'base_price' => ['required', 'numeric', 'min:100000'],
            'price_per_floor_increment' => ['nullable', 'numeric', 'min:0'],
        ]);

        $increment = $validated['price_per_floor_increment'] ?? 0;
        $count = 0;

        for ($floor = $validated['floor_start']; $floor <= $validated['floor_end']; $floor++) {
            $floorPrice = $validated['base_price'] + (($floor - $validated['floor_start']) * $increment);
            for ($u = 1; $u <= $validated['units_per_floor']; $u++) {
                $unitNumber = $building->name.'-'.$floor.str_pad((string) $u, 2, '0', STR_PAD_LEFT);
                ProjectUnit::create([
                    'project_id' => $building->project_id,
                    'building_id' => $building->id,
                    'unit_number' => $unitNumber,
                    'floor_number' => (string) $floor,
                    'unit_type' => $validated['unit_type'],
                    'bedrooms' => $validated['bedrooms'],
                    'bathrooms' => $validated['bathrooms'],
                    'area_sqm' => $validated['area_sqm'],
                    'price' => $floorPrice,
                    'currency' => 'ETB',
                    'status' => 'available',
                ]);
                $count++;
            }
        }

        $building->increment('units_count', $count);
        $building->project->increment('total_units_count', $count);

        AuditLog::log(action: 'developer.units_generated', entity: $building, reason: "Bulk generated {$count} units for building {$building->name}", user: $user);

        return redirect()->route('developer.portal', ['#units'])->with('success', "Generated {$count} units successfully.");
    }

    /**
     * Store construction milestone with optional progress photo.
     */
    public function storeMilestone(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('construction.manage')), 403);

        $validated = $request->validate([
            'milestone_name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'target_date' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'order_index' => ['nullable', 'integer'],
            'photo' => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('milestones', 'public');
        }
        unset($validated['photo']);
        $validated['project_id'] = $project->id;

        $milestone = ConstructionMilestone::create($validated);

        // Recalculate average progress
        $avg = (int) round($project->milestones()->avg('percentage') ?? 0);
        $project->update(['overall_completion_percentage' => $avg]);

        AuditLog::log(action: 'developer.milestone_created', entity: $milestone, reason: "Created milestone {$milestone->milestone_name} on project {$project->name}", user: $user);

        return redirect()->route('developer.portal', ['#construction'])->with('success', "Milestone {$milestone->milestone_name} recorded.");
    }

    /**
     * Update construction milestone with optional progress photo.
     */
    public function updateMilestone(Request $request, Project $project, ConstructionMilestone $milestone): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('construction.manage')), 403);

        $validated = $request->validate([
            'milestone_name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'target_date' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'photo' => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('milestones', 'public');
        }
        unset($validated['photo']);

        $milestone->update($validated);

        $avg = (int) round($project->milestones()->avg('percentage') ?? 0);
        $project->update(['overall_completion_percentage' => $avg]);

        AuditLog::log(action: 'developer.milestone_updated', entity: $milestone, reason: "Updated milestone {$milestone->milestone_name} to {$milestone->percentage}%", user: $user);

        return redirect()->route('developer.portal', ['#construction'])->with('success', 'Milestone updated successfully.');
    }

    /**
     * Delete construction milestone.
     */
    public function destroyMilestone(Request $request, Project $project, ConstructionMilestone $milestone): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('construction.manage')), 403);

        AuditLog::log(action: 'developer.milestone_deleted', entity: $milestone, reason: "Removed milestone {$milestone->milestone_name}", user: $user);
        $milestone->delete();

        return redirect()->route('developer.portal', ['#construction'])->with('success', 'Milestone removed.');
    }

    /**
     * Store developer payment plan installment.
     */
    public function storePaymentPlan(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('payment_plans.manage')), 403);

        $validated = $request->validate([
            'installment_name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'milestone_trigger' => ['nullable', 'string', 'max:255'],
            'order_index' => ['nullable', 'integer'],
        ]);

        $validated['project_id'] = $project->id;
        DeveloperPaymentPlan::create($validated);

        AuditLog::log(action: 'developer.payment_plan_created', entity: $project, reason: "Created payment plan installment for {$project->name}", user: $user);

        return redirect()->route('developer.portal', ['#payment-plans'])->with('success', 'Payment plan installment created.');
    }

    /**
     * Delete developer payment plan installment.
     */
    public function destroyPaymentPlan(Request $request, DeveloperPaymentPlan $plan): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('payment_plans.manage')), 403);

        AuditLog::log(action: 'developer.payment_plan_deleted', entity: $plan, reason: "Deleted payment plan installment #{$plan->id}", user: $user);
        $plan->delete();

        return redirect()->route('developer.portal', ['#payment-plans'])->with('success', 'Installment removed successfully.');
    }

    /**
     * Update unit reservation status and link contract/deposit details.
     */
    public function updateReservationStatus(Request $request, UnitReservation $reservation): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isDeveloper() || $user->isAdmin() || $user->hasPermission('reservations.manage')), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,reserved,contract_signed,sold,cancelled'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'contract_date' => ['nullable', 'date'],
            'payment_plan_id' => ['nullable', 'exists:developer_payment_plans,id'],
        ]);

        $reservation->update($validated);

        if (in_array($validated['status'], ['contract_signed', 'sold'], true)) {
            $reservation->unit?->update(['status' => $validated['status'] === 'sold' ? 'sold' : 'contracted']);
        } elseif ($validated['status'] === 'cancelled') {
            $reservation->unit?->update(['status' => 'available']);
        }

        AuditLog::log(action: 'developer.reservation_status', entity: $reservation, reason: "Reservation {$reservation->reservation_reference} transitioned to {$validated['status']}", user: $user);

        return redirect()->route('developer.portal', ['#reservations'])->with('success', "Reservation status updated to {$validated['status']}.");
    }
}
