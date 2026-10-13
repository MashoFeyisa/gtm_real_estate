<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Intercept all checks to grant admins full access or deny suspended users.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->status === 'suspended') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Anyone can view public property listings.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific property.
     */
    public function view(?User $user, Property $property): bool
    {
        if ($property->is_active && $property->status !== 'archived') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isCompany()) {
            return true;
        }

        if ($property->user_id === $user->id || $property->host_id === $user->id) {
            return true;
        }

        if ($user->agentProfile && $property->agent_id === $user->agentProfile->id) {
            return true;
        }

        return $user->hasPermission('properties.view') || $user->hasPermission('property.view');
    }

    /**
     * Determine whether the user can create property records.
     * Supported for: Admin, Company, Seller/Property Owner, Landlord, Host, Agent, Developer.
     */
    public function create(User $user): bool
    {
        if ($user->isCompany()) {
            return true;
        }

        if ($user->hasPermission('properties.create') || $user->hasPermission('property.create') || $user->hasPermission('stays.listing_create')) {
            return true;
        }

        return in_array($user->role, [
            'seller',
            'property_owner',
            'landlord',
            'host',
            'agent',
            'developer',
            'company_admin',
            'branch_manager',
            'real_estate_company',
        ], true);
    }

    /**
     * Determine whether the user can update the property.
     */
    public function update(User $user, Property $property): bool
    {
        if ($user->isCompany()) {
            return true;
        }

        $isOwner = ($property->user_id !== null && $property->user_id === $user->id)
            || ($property->host_id !== null && $property->host_id === $user->id);

        $isAssignedAgent = $user->agentProfile && $property->agent_id === $user->agentProfile->id;

        return $isOwner || $isAssignedAgent;
    }

    /**
     * Determine whether the user can delete the property.
     */
    public function delete(User $user, Property $property): bool
    {
        if ($user->isCompany()) {
            return true;
        }

        $isOwner = ($property->user_id !== null && $property->user_id === $user->id)
            || ($property->host_id !== null && $property->host_id === $user->id);

        return $isOwner;
    }

    /**
     * Determine whether the user can publish/unpublish the property.
     */
    public function publish(User $user, Property $property): bool
    {
        if ($user->isCompany()) {
            return true;
        }

        $isOwner = ($property->user_id !== null && $property->user_id === $user->id)
            || ($property->host_id !== null && $property->host_id === $user->id);

        return $isOwner;
    }
}
