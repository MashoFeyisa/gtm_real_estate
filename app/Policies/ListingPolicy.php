<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
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
     * Anyone can view public listings.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the listing.
     */
    public function view(?User $user, Listing $listing): bool
    {
        if (in_array($listing->status, ['published', 'active'], true)) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isCompany()) {
            return true;
        }

        $isOwner = ($listing->user_id !== null && $listing->user_id === $user->id)
            || ($listing->property && (
                $listing->property->user_id === $user->id
                || $listing->property->host_id === $user->id
            ));

        if ($isOwner) {
            return true;
        }

        return $user->hasPermission('listings.view') || $user->hasPermission('properties.view');
    }

    /**
     * Determine whether the user can create listings.
     */
    public function create(User $user): bool
    {
        if ($user->isCompany()) {
            return true;
        }

        if ($user->hasPermission('listings.create') || $user->hasPermission('properties.create') || $user->hasPermission('stays.listing_create')) {
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
     * Determine whether the user can update the listing.
     */
    public function update(User $user, Listing $listing): bool
    {
        if ($user->isCompany()) {
            return true;
        }

        $isOwner = ($listing->user_id !== null && $listing->user_id === $user->id)
            || ($listing->property && (
                $listing->property->user_id === $user->id
                || $listing->property->host_id === $user->id
            ));

        $isAssignedAgent = $user->agentProfile && $listing->property && $listing->property->agent_id === $user->agentProfile->id;

        if ($isOwner || $isAssignedAgent) {
            return true;
        }

        return $user->hasPermission('listings.update');
    }

    /**
     * Determine whether the user can delete the listing.
     */
    public function delete(User $user, Listing $listing): bool
    {
        if ($user->isCompany()) {
            return true;
        }

        $isOwner = ($listing->user_id !== null && $listing->user_id === $user->id)
            || ($listing->property && (
                $listing->property->user_id === $user->id
                || $listing->property->host_id === $user->id
            ));

        return $isOwner && ($user->hasPermission('listings.delete') || in_array($user->role, ['seller', 'property_owner', 'landlord', 'host'], true));
    }

    /**
     * Determine whether the user can publish or pause the listing.
     */
    public function publish(User $user, Listing $listing): bool
    {
        if ($user->isCompany()) {
            return true;
        }

        $isOwner = ($listing->user_id !== null && $listing->user_id === $user->id)
            || ($listing->property && (
                $listing->property->user_id === $user->id
                || $listing->property->host_id === $user->id
            ));

        return $isOwner && ($user->hasPermission('listings.publish') || $user->hasPermission('properties.update'));
    }
}
