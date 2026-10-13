<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'document_type',
        'title',
        'document_number',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'access_level',
        'status',
        'verification_notes',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFormattedTypeAttribute(): string
    {
        return match ($this->document_type) {
            'title_deed_carta' => 'Title Deed (የይዞታ ካርታ)',
            'site_plan_mapa' => 'Site Plan (ማፓ / Cadastre)',
            'building_permit' => 'Building Permit (የግንባታ ፈቃድ)',
            'ownership_certificate' => 'Ownership Certificate (የባለቤትነት ማረጋገጫ)',
            'tax_clearance' => 'Municipal Tax Clearance (የግብር ክሊራንስ)',
            'power_of_attorney' => 'Power of Attorney (የውክልና ማስረጃ)',
            'seller_id' => 'National ID / Passport (መታወቂያ)',
            default => ucfirst(str_replace('_', ' ', $this->document_type ?? 'Document')),
        };
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }

    /**
     * Check if a given user has permission to download/view this document (SRS §28).
     * Document access levels:
     * - public: Visible to all (subject to media_access_enabled for auth users)
     * - private / confidential: Only uploader, property owner/agent, or admin
     * - verification_only: Verification officers, verification companies, admins, and uploader
     * - admin_only: Super Admin / Platform Admin only
     * - transaction_participants: Buyers with offers/viewings, tenants with leases, landlord, agent, admin
     */
    public function isAccessibleBy(?User $user): bool
    {
        if ($this->access_level === 'public' && ! $user) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->status === 'suspended' || $user->media_access_enabled === false) {
            return false;
        }

        if ($user->isAdmin() || $user->role === 'super_admin') {
            return true;
        }

        if ($this->access_level === 'admin_only') {
            return false;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        // Listing agent / property owner
        if ($user->agentProfile && $this->property && $this->property->agent_id === $user->agentProfile->id) {
            return true;
        }

        if ($this->property && $this->property->user_id === $user->id) {
            return true;
        }

        if ($this->access_level === 'public') {
            return true;
        }

        if ($this->access_level === 'verification_only') {
            return in_array($user->role, ['verification_officer', 'verification_company'], true)
                || $user->hasPermission('documents.review_property')
                || $user->hasPermission('verifications.identity');
        }

        if ($this->access_level === 'transaction_participants') {
            if (! $this->property) {
                return false;
            }

            $hasOffer = $this->property->offers()->where('user_id', $user->id)->exists();
            $hasViewing = $this->property->viewings()->where('user_id', $user->id)->exists();
            $hasLease = $this->property->leases()->where('tenant_user_id', $user->id)->orWhere('landlord_user_id', $user->id)->exists();

            return $hasOffer || $hasViewing || $hasLease;
        }

        // private / confidential default: uploader, agent, admin only
        return false;
    }
}
