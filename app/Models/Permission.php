<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'label', 'menu_label', 'icon', 'route', 'group', 'description', 'sort_order'])]
class Permission extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Role-wide default grants for this permission.
     */
    public function roleGrants(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /**
     * Per-user overrides for this permission.
     */
    public function userOverrides(): HasMany
    {
        return $this->hasMany(UserPermissionOverride::class);
    }
}
