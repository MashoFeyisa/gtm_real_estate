<?php

namespace App\Http\Controllers;

use App\Models\ConstructionMilestone;
use App\Models\DeveloperPaymentPlan;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use App\Models\UnitReservation;
use Illuminate\Contracts\View\View;
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

        return view('developer.portal', [
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
}
