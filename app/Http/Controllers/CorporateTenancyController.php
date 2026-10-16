<?php

namespace App\Http\Controllers;

use App\Models\ConstructionMilestone;
use App\Models\CorporateEmployee;
use App\Models\CorporateLeaseAssignment;
use App\Models\CorporateTenant;
use App\Models\DeveloperEscrowDisbursement;
use App\Models\Lease;
use App\Models\Project;
use App\Services\FinanceSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CorporateTenancyController extends Controller
{
    /**
     * Display the corporate tenancy portal with employees, leases, and consolidated billing.
     */
    public function portal(Request $request): View
    {
        $user = $request->user();

        // Find tenant matching user or fallback to first active for corporate admins
        $tenant = CorporateTenant::where('user_id', $user?->id)->first()
            ?? CorporateTenant::with(['employees', 'leaseAssignments.employee', 'leaseAssignments.lease'])->first();

        $employees = $tenant
            ? $tenant->employees()->latest()->paginate(15, ['*'], 'emp_page')
            : collect();

        $assignments = $tenant
            ? $tenant->leaseAssignments()->with(['employee', 'lease.property'])->latest()->paginate(15, ['*'], 'assign_page')
            : collect();

        $availableLeases = Lease::where('status', 'active')->get();

        $stats = [
            'total_employees' => $tenant ? $tenant->employees()->count() : 0,
            'active_assignments' => $tenant ? $tenant->leaseAssignments()->where('status', 'active')->count() : 0,
            'total_monthly_commitment_etb' => $tenant ? $tenant->total_monthly_rent_commitment : 0,
            'credit_limit_etb' => $tenant ? (float) $tenant->credit_limit : 0,
        ];

        return view('corporate.portal', [
            'tenant' => $tenant,
            'employees' => $employees,
            'assignments' => $assignments,
            'availableLeases' => $availableLeases,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a new corporate employee in the housing roster.
     */
    public function storeEmployee(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'corporate_tenant_id' => ['required', 'exists:corporate_tenants,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'employee_id_number' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
        ]);

        CorporateEmployee::create($validated);

        return back()->with('success', 'Corporate employee registered in housing roster successfully.');
    }

    /**
     * Assign an employee occupant to a corporate lease.
     */
    public function assignLease(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'corporate_tenant_id' => ['required', 'exists:corporate_tenants,id'],
            'corporate_employee_id' => ['required', 'exists:corporate_employees,id'],
            'lease_id' => ['nullable', 'exists:leases,id'],
            'unit_identifier' => ['required', 'string', 'max:100'],
            'assigned_from' => ['required', 'date'],
            'assigned_until' => ['nullable', 'date', 'after_or_equal:assigned_from'],
            'monthly_rent_etb' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['status'] = 'active';

        CorporateLeaseAssignment::create($validated);

        return back()->with('success', 'Employee successfully assigned to residential unit.');
    }

    /**
     * View or download consolidated corporate billing statement.
     */
    public function billingStatement(Request $request, CorporateTenant $tenant): View
    {
        $assignments = $tenant->leaseAssignments()
            ->with(['employee', 'lease.property'])
            ->where('status', 'active')
            ->get();

        $subtotal = $assignments->sum('monthly_rent_etb');
        $vatRate = app(FinanceSettings::class)->vatRate();
        $vat = round($subtotal * $vatRate / 100, 2); // Ethiopian VAT
        $total = $subtotal + $vat;

        return view('corporate.billing-statement', [
            'tenant' => $tenant,
            'assignments' => $assignments,
            'subtotal' => $subtotal,
            'vat' => $vat,
            'vatRate' => $vatRate,
            'total' => $total,
            'statementDate' => now()->format('F d, Y'),
            'invoiceNumber' => 'CORP-INV-'.now()->format('Ym').'-'.str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Request milestone escrow disbursement for developers.
     *
     * Only the developer who owns the project may request; everyone else is
     * refused by the DeveloperEscrowDisbursementPolicy.
     */
    public function requestEscrowDisbursement(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('request', $project);

        $validated = $request->validate([
            'milestone_id' => ['required', 'exists:construction_milestones,id'],
            'amount_etb' => ['required', 'numeric', 'min:1000'],
            'bank_reference' => ['nullable', 'string', 'max:100'],
            'inspection_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $milestone = ConstructionMilestone::findOrFail($validated['milestone_id']);

        abort_if($milestone->project_id !== $project->id, 422, 'The milestone does not belong to this project.');

        DeveloperEscrowDisbursement::create([
            'project_id' => $project->id,
            'milestone_id' => $milestone->id,
            'requested_by' => $request->user()->id,
            'amount_etb' => $validated['amount_etb'],
            'status' => 'pending',
            'bank_reference' => $validated['bank_reference'] ?? null,
            'inspection_notes' => $validated['inspection_notes'] ?? null,
        ]);

        return back()->with('success', 'Escrow milestone disbursement request submitted for verification.');
    }

    /**
     * Approve and disburse milestone escrow funds following inspector sign-off.
     *
     * Finance staff and administrators only, and never the requester - the
     * DeveloperEscrowDisbursementPolicy enforces both.
     */
    public function approveEscrowDisbursement(Request $request, DeveloperEscrowDisbursement $disbursement): RedirectResponse
    {
        Gate::authorize('approve', $disbursement->project);

        abort_if($disbursement->status !== 'pending', 422, 'Only a pending escrow request can be approved.');

        $disbursement->update([
            'status' => 'disbursed',
            'verified_by' => $request->user()->id,
            'disbursed_at' => now(),
        ]);

        return back()->with('success', 'Developer milestone escrow funds released successfully.');
    }
}
