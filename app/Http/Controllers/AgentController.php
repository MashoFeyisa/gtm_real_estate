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
    public function index()
    {
        return view('agents.index', [
            'agents' => Agent::query()
                ->withCount(['approvedFeedbacks as approved_feedbacks_count'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Display a single agent profile with contact, call, and feedback options.
     */
    public function show(Agent $agent)
    {
        return view('agents.show', [
            'agent' => $agent->loadCount(['properties', 'approvedFeedbacks as approved_feedbacks_count']),
            'feedbacks' => $agent->approvedFeedbacks()->limit(10)->get(),
            'properties' => $agent->properties()->where('is_active', true)->latest('updated_at')->limit(3)->get(),
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
