<?php

namespace App\Models;

use Database\Factories\BankAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    /** @use HasFactory<BankAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'branch',
        'type',
        'instructions',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope query to only active bank accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to standard bank transfer accounts.
     */
    public function scopeBank($query)
    {
        return $query->where('type', 'bank');
    }

    /**
     * Scope query to CBE Birr merchant accounts.
     */
    public function scopeCbeBirr($query)
    {
        return $query->where('type', 'cbe_birr');
    }

    /**
     * Get active bank accounts for display in checkout and payment modals.
     */
    public static function getActiveBanks()
    {
        $accounts = static::active()
            ->bank()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($accounts->isEmpty()) {
            return collect([
                (object) [
                    'id' => 1,
                    'bank_name' => 'CBE Bank',
                    'account_name' => 'GTM Real Estate PLC',
                    'account_number' => '1000 2837 4920 1',
                    'branch' => 'Finfinne Branch',
                    'instructions' => 'Deposit at any Commercial Bank of Ethiopia branch or via CBE Mobile Banking.',
                ],
                (object) [
                    'id' => 2,
                    'bank_name' => 'Awash Bank',
                    'account_name' => 'GTM Real Estate PLC',
                    'account_number' => '0132 0492 8172 00',
                    'branch' => 'Head Office Branch',
                    'instructions' => 'Deposit at any Awash Bank branch or Awash Birr / Mobile Banking.',
                ],
                (object) [
                    'id' => 3,
                    'bank_name' => 'Dashen Bank',
                    'account_name' => 'GTM Real Estate PLC',
                    'account_number' => '0029 3847 2918 01',
                    'branch' => 'Bole Branch',
                    'instructions' => 'Deposit at any Dashen Bank branch or Amole / Mobile Banking.',
                ],
            ]);
        }

        return $accounts;
    }

    /**
     * Get the active CBE Birr merchant account number/code.
     */
    public static function getCbeBirrMerchantCode(): string
    {
        $merchant = static::active()
            ->cbeBirr()
            ->orderBy('sort_order')
            ->first();

        return $merchant?->account_number ?? '284920';
    }
}
