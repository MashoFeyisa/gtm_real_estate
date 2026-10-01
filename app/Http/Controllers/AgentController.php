<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentFeedback;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    /**
     * Display all agents in a public directory.
     */
    public function index(Request $request)
    {
        $query = Agent::query()
            ->active()
            ->withCount(['approvedFeedbacks as approved_feedbacks_count'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('agents.index', [
            'agents' => $query->get(),
            'searchQuery' => $request->query('search', ''),
        ]);
    }

    /**
     * Display a single agent profile with storefront, contact, call, and feedback options.
     */
    public function show(Request $request, Agent $agent)
    {
        abort_unless($agent->is_active, 404);

        $type = $request->query('type');
        $propertiesQuery = $agent->properties()->where('is_active', true)->latest('updated_at');

        if (in_array($type, ['sale', 'rent'], true)) {
            $propertiesQuery->where('type', $type);
        }

        return view('agents.show', [
            'agent' => $agent->loadCount(['properties', 'approvedFeedbacks as approved_feedbacks_count']),
            'feedbacks' => $agent->approvedFeedbacks()->limit(10)->get(),
            'properties' => $propertiesQuery->paginate(12)->withQueryString(),
            'selectedType' => $type ?? 'all',
            'saleCount' => $agent->properties()->where('is_active', true)->where('type', 'sale')->count(),
            'rentCount' => $agent->properties()->where('is_active', true)->where('type', 'rent')->count(),
        ]);
    }

    /**
     * Store client feedback for an agent.
     */
    public function storeFeedback(Request $request, Agent $agent)
    {
        $validated = $request->validateWithBag('feedback', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        AgentFeedback::create([
            ...$validated,
            'agent_id' => $agent->id,
            'is_approved' => false,
        ]);

        return redirect()
            ->route('agents.show', $agent)
            ->with('feedback_success', 'Thank you! Your feedback was submitted and will appear once reviewed.');
    }
}
