<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Authorize company executives, finance staff, or administrators.
     */
    protected function authorizeManage(?User $user): void
    {
        if (! $user || (! $user->isFinanceStaff() && ! $user->canAccessCompanyDashboard())) {
            abort(403, 'Unauthorized. Corporate Treasury management access required.');
        }
    }

    /**
     * Store a new company bank account or CBE Birr merchant account.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage($request->user());

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
            'branch' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:bank,cbe_birr'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        BankAccount::create($validated);

        $label = $validated['type'] === 'cbe_birr' ? 'CBE Birr merchant account' : 'Bank account';

        return back()->with('success', "{$label} '{$validated['bank_name']}' created successfully.");
    }

    /**
     * Update an existing bank account or CBE Birr merchant account.
     */
    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorizeManage($request->user());

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
            'branch' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:bank,cbe_birr'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $bankAccount->is_active;
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? $bankAccount->sort_order);

        $bankAccount->update($validated);

        $label = $bankAccount->type === 'cbe_birr' ? 'CBE Birr merchant account' : 'Bank account';

        return back()->with('success', "{$label} '{$bankAccount->bank_name}' updated successfully.");
    }

    /**
     * Delete an existing bank account or CBE Birr merchant account.
     */
    public function destroy(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorizeManage($request->user());

        $name = $bankAccount->bank_name;
        $label = $bankAccount->type === 'cbe_birr' ? 'CBE Birr merchant account' : 'Bank account';

        $bankAccount->delete();

        return back()->with('success', "{$label} '{$name}' deleted successfully.");
    }

    /**
     * Toggle the active status of an account.
     */
    public function toggleActive(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorizeManage($request->user());

        $bankAccount->update([
            'is_active' => ! $bankAccount->is_active,
        ]);

        $status = $bankAccount->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Account '{$bankAccount->bank_name}' has been {$status}.");
    }
}
