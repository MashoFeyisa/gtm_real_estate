<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Verification decisions behind the verification officer desk:
 * company license verification and developer verification.
 */
class VerificationDeskController extends Controller
{
    public function companyDecision(Request $request, Agent $agent): RedirectResponse|JsonResponse
    {
        $this->authorizeVerification('verifications.company');

        $validated = $request->validate([
            'decision' => ['required', 'in:verify,revoke'],
        ]);

        $agent->update([
            'license_verified' => $validated['decision'] === 'verify',
        ]);

        $message = $validated['decision'] === 'verify'
            ? "Company '{$agent->name}' verified."
            : "Verification revoked for '{$agent->name}'.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'verifications',
            ]);
        }

        return redirect()->to(route('dashboard').'#verifications')->with('success', $message);
    }

    public function developerDecision(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $this->authorizeVerification('verifications.developer');

        $validated = $request->validate([
            'decision' => ['required', 'in:verify,revoke'],
        ]);

        $project->update([
            'developer_verified' => $validated['decision'] === 'verify',
        ]);

        $message = $validated['decision'] === 'verify'
            ? "Developer '{$project->developer_name}' verified."
            : "Developer verification revoked for '{$project->developer_name}'.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'verifications',
            ]);
        }

        return redirect()->to(route('dashboard').'#verifications')->with('success', $message);
    }

    private function authorizeVerification(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->isAdmin() || ($user->role === 'verification_officer' && $user->hasPermission($permission))),
            403
        );
    }
}
