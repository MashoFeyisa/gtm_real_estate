<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ConstructionMilestone;
use App\Models\DeveloperPaymentPlan;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use App\Models\Property;
use App\Models\UnitReservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DevelopmentController extends Controller
{
    /**
     * Display public directory of Ethiopian real estate developments and projects.
     */
    public function index(Request $request): View
    {
        $query = Project::with(['buildings', 'units', 'milestones', 'agent'])
            ->where('status', 'published');

        if ($request->filled('city')) {
            $query->where(function ($q) use ($request) {
                $q->where('city', 'like', '%'.$request->city.'%')
                    ->orWhere('subcity', 'like', '%'.$request->city.'%')
                    ->orWhere('address', 'like', '%'.$request->city.'%');
            });
        }

        if ($request->filled('project_type')) {
            $query->where('project_type', $request->project_type);
        }

        if ($request->filled('construction_status')) {
            $query->where('construction_status', $request->construction_status);
        }

        if ($request->filled('min_completion')) {
            $query->where('overall_completion_percentage', '>=', (int) $request->min_completion);
        }

        if ($request->filled('max_price')) {
            $query->where('starting_price', '<=', (float) $request->max_price);
        }

        $projects = $query->latest()->paginate(12)->withQueryString();

        $cities = Project::where('status', 'published')
            ->distinct()
            ->pluck('city')
            ->filter();

        return view('developments.index', compact('projects', 'cities'));
    }

    /**
     * Display comprehensive development project details, unit inventory, and milestones.
     */
    public function show(string $slug): View
    {
        $project = Project::with([
            'buildings.units',
            'units' => function ($q) {
                $q->orderBy('price', 'asc');
            },
            'milestones',
            'paymentPlans',
            'agent',
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $unitsGroupedByBuilding = $project->units->groupBy(function ($unit) {
            return $unit->building?->name ?? 'Main Tower / Phase 1';
        });

        $availableUnitsCount = $project->units->where('status', 'available')->count();
        $reservedUnitsCount = $project->units->where('status', 'reserved')->count();
        $soldUnitsCount = $project->units->where('status', 'sold')->count();

        return view('developments.show', compact(
            'project',
            'unitsGroupedByBuilding',
            'availableUnitsCount',
            'reservedUnitsCount',
            'soldUnitsCount'
        ));
    }

    /**
     * Store a newly created development project (by Admin or Agent).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->agentProfile && ! ($user->isDeveloper() && $user->hasPermission('projects.create')))) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'developer_name' => ['required', 'string', 'max:255'],
            'developer_phone' => ['nullable', 'string', 'max:50'],
            'developer_email' => ['nullable', 'email', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'project_type' => ['required', 'in:residential,commercial,mixed_use,luxury_villas,apartments'],
            'construction_status' => ['required', 'in:planning,pre_construction,under_construction,near_completion,completed,delivered'],
            'overall_completion_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_completion_date' => ['nullable', 'string', 'max:50'],
            'starting_price' => ['required', 'numeric', 'min:0'],
            'land_area_sqm' => ['nullable', 'numeric', 'min:0'],
            'total_buildings_count' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'facilities_input' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'featured_image_file' => ['nullable', 'image', 'max:8192'],
        ]);

        // Determine agent ownership
        $agentId = null;
        if ($user->agentProfile) {
            $agentId = $user->agentProfile->id;
        } elseif ($user->isAdmin() && $request->filled('agent_id')) {
            $agentId = $request->agent_id;
        }

        // Handle image upload or image URL
        $featuredImagePath = $validated['featured_image'] ?? null;
        if ($request->hasFile('featured_image_file')) {
            $featuredImagePath = $request->file('featured_image_file')->store('developments', 'public');
        }

        // Parse facilities from newline or comma delimited text
        $facilities = [];
        if (! empty($validated['facilities_input'])) {
            $facilities = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['facilities_input']))));
        }

        $project = Project::create([
            'name' => $validated['name'],
            'developer_name' => $validated['developer_name'],
            'developer_phone' => $validated['developer_phone'] ?? null,
            'developer_email' => $validated['developer_email'] ?? null,
            'agent_id' => $agentId,
            'city' => $validated['city'],
            'subcity' => $validated['subcity'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => ! empty($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => ! empty($validated['longitude']) ? (float) $validated['longitude'] : null,
            'project_type' => $validated['project_type'],
            'construction_status' => $validated['construction_status'],
            'overall_completion_percentage' => $validated['overall_completion_percentage'] ?? 0,
            'expected_completion_date' => $validated['expected_completion_date'] ?? null,
            'starting_price' => $validated['starting_price'],
            'land_area_sqm' => $validated['land_area_sqm'] ?? null,
            'total_buildings_count' => $validated['total_buildings_count'] ?? 1,
            'total_units_count' => 0,
            'currency' => 'ETB',
            'description' => $validated['description'] ?? null,
            'facilities' => ! empty($facilities) ? $facilities : [
                '3-Phase Electric & Standby Generator Ready',
                'Municipal Water & Water Reservoir Tanks',
                '24/7 Gated Security & Controlled Access',
            ],
            'featured_image' => $featuredImagePath,
            'status' => 'published',
            'is_featured' => $request->boolean('is_featured'),
        ]);

        // Create default starter tower building (SRS §89)
        $building = ProjectBuilding::create([
            'project_id' => $project->id,
            'name' => 'Tower A - Main Phase',
            'total_floors' => 'G+12',
            'units_count' => 0,
        ]);

        // Create initial starter milestones conforming to SRS §93
        $initialProgress = (int) ($validated['overall_completion_percentage'] ?? 0);
        $milestones = [
            ['milestone_name' => 'Excavation & Foundation', 'percentage' => min(100, $initialProgress > 20 ? 100 : $initialProgress), 'status' => $initialProgress >= 20 ? 'completed' : 'in_progress', 'order_index' => 1],
            ['milestone_name' => 'Structural Skeleton', 'percentage' => $initialProgress > 50 ? 100 : ($initialProgress > 20 ? 60 : 0), 'status' => $initialProgress > 50 ? 'completed' : 'pending', 'order_index' => 2],
            ['milestone_name' => 'Block Masonry & Facade', 'percentage' => $initialProgress > 75 ? 100 : 0, 'status' => 'pending', 'order_index' => 3],
            ['milestone_name' => 'Interior Finishing & Glazing', 'percentage' => $initialProgress >= 90 ? 90 : 0, 'status' => 'pending', 'order_index' => 4],
            ['milestone_name' => 'Handover & Commissioning', 'percentage' => $initialProgress >= 100 ? 100 : 0, 'status' => 'pending', 'order_index' => 5],
        ];

        foreach ($milestones as $m) {
            ConstructionMilestone::create(array_merge(['project_id' => $project->id], $m));
        }

        // Create default payment schedule conforming to SRS §94
        $plans = [
            ['installment_name' => '10% Reservation & Booking Deposit', 'percentage' => 10.00, 'milestone_trigger' => 'Upon Contract Signing', 'order_index' => 1],
            ['installment_name' => '20% Foundation Milestone', 'percentage' => 20.00, 'milestone_trigger' => 'Foundation Inspection Signed-Off', 'order_index' => 2],
            ['installment_name' => '20% Structural Skeleton Milestone', 'percentage' => 20.00, 'milestone_trigger' => 'Casting of Top Floor Slab', 'order_index' => 3],
            ['installment_name' => '20% Masonry & Roofing Milestone', 'percentage' => 20.00, 'milestone_trigger' => 'Perimeter Walls & Roof Cladding', 'order_index' => 4],
            ['installment_name' => '20% Interior Finishing Milestone', 'percentage' => 20.00, 'milestone_trigger' => 'Floor Tiling, Windows & Fittings', 'order_index' => 5],
            ['installment_name' => '10% Handover & Title Deed Transfer', 'percentage' => 10.00, 'milestone_trigger' => 'Final Key & Municipal Deed Handover', 'order_index' => 6],
        ];

        foreach ($plans as $p) {
            DeveloperPaymentPlan::create(array_merge(['project_id' => $project->id], $p));
        }

        AuditLog::log(
            action: 'development.created',
            entity: $project,
            newValue: [
                'name' => $project->name,
                'project_code' => $project->project_code,
                'developer' => $project->developer_name,
                'type' => $project->project_type,
                'status' => $project->construction_status,
                'starting_price' => $project->starting_price,
                'completion_percentage' => $project->overall_completion_percentage,
            ],
            reason: "Development project '{$project->name}' created"
        );

        $message = "Development project '{$project->name}' posted successfully.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'project' => $project,
            ]);
        }

        if ($request->input('from_dashboard') || str_contains(url()->previous(), 'dashboard')) {
            return redirect()->to(route('dashboard').'#projects')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Update an existing development project (Admin or project's Agent).
     */
    public function update(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $this->authorizeProjectAccess($project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'developer_name' => ['required', 'string', 'max:255'],
            'developer_phone' => ['nullable', 'string', 'max:50'],
            'developer_email' => ['nullable', 'email', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'project_type' => ['required', 'in:residential,commercial,mixed_use,luxury_villas,apartments'],
            'construction_status' => ['required', 'in:planning,pre_construction,under_construction,near_completion,completed,delivered'],
            'overall_completion_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_completion_date' => ['nullable', 'string', 'max:50'],
            'starting_price' => ['required', 'numeric', 'min:0'],
            'land_area_sqm' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'facilities_input' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'featured_image_file' => ['nullable', 'image', 'max:8192'],
        ]);

        $oldData = [
            'name' => $project->name,
            'developer_name' => $project->developer_name,
            'project_type' => $project->project_type,
            'construction_status' => $project->construction_status,
            'overall_completion_percentage' => $project->overall_completion_percentage,
            'starting_price' => $project->starting_price,
            'city' => $project->city,
        ];

        $data = [
            'name' => $validated['name'],
            'developer_name' => $validated['developer_name'],
            'developer_phone' => $validated['developer_phone'] ?? null,
            'developer_email' => $validated['developer_email'] ?? null,
            'city' => $validated['city'],
            'subcity' => $validated['subcity'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => ! empty($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => ! empty($validated['longitude']) ? (float) $validated['longitude'] : null,
            'project_type' => $validated['project_type'],
            'construction_status' => $validated['construction_status'],
            'expected_completion_date' => $validated['expected_completion_date'] ?? null,
            'starting_price' => $validated['starting_price'],
            'land_area_sqm' => $validated['land_area_sqm'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if (isset($validated['overall_completion_percentage'])) {
            $data['overall_completion_percentage'] = $validated['overall_completion_percentage'];
        }

        if ($request->hasFile('featured_image_file')) {
            $data['featured_image'] = $request->file('featured_image_file')->store('developments', 'public');
        } elseif ($request->filled('featured_image')) {
            $data['featured_image'] = $validated['featured_image'];
        }

        if (! empty($validated['facilities_input'])) {
            $data['facilities'] = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['facilities_input']))));
        }

        if ($request->has('is_featured')) {
            $data['is_featured'] = $request->boolean('is_featured');
        }

        $project->update($data);

        AuditLog::log(
            action: 'development.updated',
            entity: $project,
            oldValue: $oldData,
            newValue: [
                'name' => $project->name,
                'developer_name' => $project->developer_name,
                'project_type' => $project->project_type,
                'construction_status' => $project->construction_status,
                'overall_completion_percentage' => $project->overall_completion_percentage,
                'starting_price' => $project->starting_price,
                'city' => $project->city,
            ],
            reason: "Development project '{$project->name}' updated"
        );

        $message = "Development project '{$project->name}' updated successfully.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'project' => $project,
            ]);
        }

        if ($request->input('from_dashboard') || str_contains(url()->previous(), 'dashboard')) {
            return redirect()->to(route('dashboard').'#projects')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Delete/archive a development project.
     */
    public function destroy(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $this->authorizeProjectAccess($project);

        $name = $project->name;
        $projectData = [
            'name' => $project->name,
            'project_code' => $project->project_code,
            'developer_name' => $project->developer_name,
            'starting_price' => $project->starting_price,
            'city' => $project->city,
        ];

        AuditLog::log(
            action: 'development.deleted',
            entity: $project,
            oldValue: $projectData,
            newValue: null,
            reason: "Development project '{$name}' deleted"
        );

        $project->delete();

        $message = "Development project '{$name}' deleted successfully.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        if ($request->input('from_dashboard') || str_contains(url()->previous(), 'dashboard')) {
            return redirect()->to(route('dashboard').'#projects')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Add a unit to an existing project inventory (Admin or project's Agent).
     */
    public function storeUnit(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $this->authorizeProjectAccess($project, 'units.create');

        $validated = $request->validate([
            'unit_number' => ['required', 'string', 'max:50'],
            'building_id' => ['nullable', 'exists:project_buildings,id'],
            'floor_number' => ['required', 'string', 'max:50'],
            'unit_type' => ['required', 'in:studio,1_bedroom,2_bedroom,3_bedroom,4_bedroom,penthouse,duplex,retail_shop'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:1', 'max:20'],
            'area_sqm' => ['required', 'numeric', 'min:1'],
            'price' => ['required', 'numeric', 'min:1'],
            'orientation' => ['nullable', 'string', 'max:100'],
            'parking_spaces' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:available,reserved,contracted,sold,blocked'],
        ]);

        $unit = $project->units()->create([
            'building_id' => $validated['building_id'] ?? $project->buildings()->first()?->id,
            'unit_number' => $validated['unit_number'],
            'floor_number' => $validated['floor_number'],
            'unit_type' => $validated['unit_type'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area_sqm' => $validated['area_sqm'],
            'price' => $validated['price'],
            'currency' => 'ETB',
            'orientation' => $validated['orientation'] ?? null,
            'parking_spaces' => $validated['parking_spaces'] ?? 1,
            'status' => $validated['status'],
        ]);

        // Update project total units count
        $project->update([
            'total_units_count' => $project->units()->count(),
        ]);

        AuditLog::log(
            action: 'development.unit_created',
            entity: $project,
            newValue: [
                'project' => $project->name,
                'unit_number' => $unit->unit_number,
                'unit_type' => $unit->unit_type,
                'price' => $unit->price,
                'status' => $unit->status,
            ],
            reason: "Unit {$unit->unit_number} added to {$project->name}"
        );

        $message = "Unit {$unit->unit_number} added to {$project->name} inventory.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'unit' => $unit,
            ]);
        }

        if ($request->input('from_dashboard') || str_contains(url()->previous(), 'dashboard')) {
            return redirect()->to(route('dashboard').'#projects')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Delete a unit from project inventory.
     */
    public function destroyUnit(Request $request, ProjectUnit $unit): RedirectResponse|JsonResponse
    {
        $this->authorizeProjectAccess($unit->project, 'units.create');

        $unitNumber = $unit->unit_number;
        $project = $unit->project;

        AuditLog::log(
            action: 'development.unit_deleted',
            entity: $project,
            oldValue: ['unit_number' => $unitNumber],
            newValue: null,
            reason: "Unit {$unitNumber} removed from inventory"
        );

        $unit->delete();

        $project->update([
            'total_units_count' => $project->units()->count(),
        ]);

        $message = "Unit {$unitNumber} removed from inventory.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        if ($request->input('from_dashboard') || str_contains(url()->previous(), 'dashboard')) {
            return redirect()->to(route('dashboard').'#projects')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Process unit reservation / lead request (SRS §95: Sales Workflow).
     */
    public function reserveUnit(Request $request, Project $project, ProjectUnit $unit): RedirectResponse
    {
        if (! auth()->check()) {
            return $this->redirectGuestToSignup(
                $request,
                route('developments.show', $project->slug),
                'Register / Login to continue: Create a client account or sign in before reserving a development unit.'
            );
        }

        $request->validate([
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_phone' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $reservation = DB::transaction(function () use ($project, $unit, $request) {
                $lockedUnit = ProjectUnit::where('id', $unit->id)->lockForUpdate()->firstOrFail();

                if ($lockedUnit->status !== 'available') {
                    throw new \RuntimeException('This unit is no longer available for reservation.');
                }

                $res = UnitReservation::create([
                    'project_id' => $project->id,
                    'unit_id' => $lockedUnit->id,
                    'user_id' => auth()->id(),
                    'buyer_name' => $request->buyer_name,
                    'buyer_email' => $request->buyer_email,
                    'buyer_phone' => $request->buyer_phone,
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);

                // Transition unit status to reserved
                $lockedUnit->update(['status' => 'reserved']);

                return $res;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'unit_id' => $e->getMessage(),
                'unit_error' => $e->getMessage(),
            ]);
        }

        return back()->with('developer_reservation_success', [
            'reference' => $reservation->reservation_reference,
            'unit' => $unit->unit_number,
            'project' => $project->name,
            'developer' => $project->developer_name,
            'price' => $unit->formatted_price,
            'buyer_name' => $reservation->buyer_name,
        ]);
    }

    /**
     * Update unit status directly from management dashboard (SRS §92, §157).
     */
    public function updateUnitStatus(Request $request, ProjectUnit $unit): RedirectResponse|JsonResponse
    {
        $this->authorizeProjectAccess($unit->project, 'units.create');

        $request->validate([
            'status' => ['required', 'in:available,reserved,contracted,sold,blocked'],
        ]);

        $oldStatus = $unit->status;
        $unit->update(['status' => $request->status]);

        AuditLog::log(
            action: 'development.unit_status_updated',
            entity: $unit->project,
            oldValue: ['unit_number' => $unit->unit_number, 'status' => $oldStatus],
            newValue: ['unit_number' => $unit->unit_number, 'status' => $request->status],
            reason: "Unit {$unit->unit_number} status changed to {$request->status}"
        );

        $message = "Unit {$unit->unit_number} status updated to {$request->status}.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'unit' => $unit,
            ]);
        }

        if ($request->input('from_dashboard') || str_contains(url()->previous(), 'dashboard')) {
            return redirect()->to(route('dashboard').'#projects')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Update construction milestone percentage and recalculate project overall progress (SRS §93).
     */
    public function updateMilestone(Request $request, Project $project, ConstructionMilestone $milestone): RedirectResponse|JsonResponse
    {
        $this->authorizeProjectAccess($project, 'construction.manage');

        $request->validate([
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $oldValues = [
            'percentage' => $milestone->percentage,
            'status' => $milestone->status,
        ];

        $milestone->update([
            'percentage' => $request->percentage,
            'status' => $request->status,
        ]);

        // Recalculate average overall completion for the project
        $averagePercentage = (int) round($project->milestones()->avg('percentage') ?? 0);
        $project->update(['overall_completion_percentage' => $averagePercentage]);

        AuditLog::log(
            action: 'development.milestone_updated',
            entity: $project,
            oldValue: array_merge(['milestone' => $milestone->milestone_name], $oldValues),
            newValue: [
                'milestone' => $milestone->milestone_name,
                'percentage' => $request->percentage,
                'status' => $request->status,
                'project_overall' => $averagePercentage,
            ],
            reason: "Construction milestone '{$milestone->milestone_name}' updated ({$request->percentage}%)"
        );

        $message = "Construction milestone '{$milestone->milestone_name}' updated ({$request->percentage}%).";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'milestone' => $milestone,
                'project_completion' => $averagePercentage,
            ]);
        }

        if ($request->input('from_dashboard') || str_contains(url()->previous(), 'dashboard')) {
            return redirect()->to(route('dashboard').'#projects')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Publish a developer project unit into the public marketplace listing feed (§92).
     */
    public function publishUnitAsListing(Request $request, ProjectUnit $unit): RedirectResponse
    {
        $project = $unit->project;
        $this->authorizeProjectAccess($project, 'listings.publish');

        $title = "{$project->name} - Unit {$unit->unit_number} ({$unit->bedrooms} Bed)";
        $property = Property::create([
            'user_id' => $request->user()->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'description' => "Brand new {$unit->unit_type} in {$project->name}. Floor {$unit->floor_number}, {$unit->area_sqm} sqm with panoramic views. Developed by {$project->developer_name}.",
            'price' => $unit->price,
            'bedrooms' => $unit->bedrooms,
            'bathrooms' => $unit->bathrooms,
            'area' => (int) $unit->area_sqm,
            'building_area' => (int) $unit->area_sqm,
            'parking_spaces' => $unit->parking_spaces ?? 1,
            'type' => 'sale',
            'property_category' => 'apartment',
            'status' => 'published',
            'city' => $project->city ?? 'Addis Ababa',
            'subcity' => $project->subcity ?? 'Bole',
            'address' => $project->address,
            'latitude' => $project->latitude,
            'longitude' => $project->longitude,
            'is_active' => true,
        ]);

        return back()->with('success', "Unit {$unit->unit_number} published to marketplace listings as '{$property->title}'.");
    }

    /**
     * Authorize that the current user is admin, a developer holding the
     * relevant permission, OR owns the project as an agent.
     */
    protected function authorizeProjectAccess(Project $project, string $permission = 'projects.create'): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($user->agentProfile && $project->agent_id === $user->agentProfile->id) {
            return;
        }

        if ($user->isDeveloper() && $user->hasPermission($permission)) {
            return;
        }

        abort(403, 'Unauthorized access to this development project.');
    }
}
