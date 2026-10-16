<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Developer milestone escrow rules (SRS §38, §137).
 *
 * Only the developer who owns a project may ask for its escrow to be released;
 * nobody may approve a request they raised themselves.
 */
class DeveloperEscrowDisbursementPolicy
{
    use HandlesAuthorization;

    /**
     * Request a milestone disbursement: the project's own developer only.
     */
    public function request(User $user, Project $project): bool
    {
        if ($user->status === 'suspended') {
            return false;
        }

        return $project->developer_user_id !== null
            && (int) $project->developer_user_id === (int) $user->id;
    }

    /**
     * Approve a disbursement: finance staff or administrators only, and never
     * the person who raised the request.
     */
    public function approve(User $user, Project $project): bool
    {
        if ($user->status === 'suspended') {
            return false;
        }

        if (! $user->isFinanceStaff()) {
            return false;
        }

        return $project->developer_user_id === null
            || (int) $project->developer_user_id !== (int) $user->id;
    }
}
