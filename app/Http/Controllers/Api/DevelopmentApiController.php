<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevelopmentApiController extends Controller
{
    /**
     * List master real estate developments.
     */
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->with(['buildings', 'units'])
            ->where('status', 'published')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $projects->items(),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    /**
     * Show master development details, buildings, and units.
     */
    public function show(Project $project): JsonResponse
    {
        $project->load(['buildings.units', 'milestones', 'paymentPlans', 'agent']);

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }
}
