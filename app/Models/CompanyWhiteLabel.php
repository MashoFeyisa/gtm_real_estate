<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyWhiteLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'subdomain',
        'custom_domain',
        'brand_name',
        'logo_path',
        'primary_color',
        'secondary_color',
        'support_phone',
        'support_email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function resolveByHostOrSubdomain(string $host): ?self
    {
        // Try exact custom domain first
        $whiteLabel = self::where('is_active', true)
            ->where('custom_domain', $host)
            ->first();

        if ($whiteLabel) {
            return $whiteLabel;
        }

        // Try extracting subdomain
        $parts = explode('.', $host);
        if (count($parts) >= 2) {
            $subdomain = $parts[0];

            return self::where('is_active', true)
                ->where('subdomain', $subdomain)
                ->first();
        }

        return null;
    }
}
