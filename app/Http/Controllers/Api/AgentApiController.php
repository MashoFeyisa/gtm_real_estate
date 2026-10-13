<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentApiController extends Controller
{
    /**
     * List verified brokers and real estate agents.
     */
    public function index(Request $request): JsonResponse
    {
        $agents = Agent::query()
            ->with(['branch'])
            ->where('is_active', true)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $agents->items(),
            'pagination' => [
                'current_page' => $agents->currentPage(),
                'last_page' => $agents->lastPage(),
                'total' => $agents->total(),
            ],
        ]);
    }

    /**
     * Show agent profile and published listings.
     */
    public function show(Agent $agent): JsonResponse
    {
        $agent->load(['branch', 'properties' => function ($q) {
            $q->where('status', 'published')->where('is_active', true)->with('images');
        }]);

        return response()->json([
            'success' => true,
            'data' => $agent,
        ]);
    }
}
