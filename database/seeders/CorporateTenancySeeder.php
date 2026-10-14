<?php

namespace Database\Seeders;

use App\Models\CompanyWhiteLabel;
use App\Models\ConstructionMilestone;
use App\Models\CorporateEmployee;
use App\Models\CorporateLeaseAssignment;
use App\Models\CorporateTenant;
use App\Models\DeveloperEscrowDisbursement;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CorporateTenancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reserved `contact` role: this is a demo customer-company account,
        // not an operator persona — the `company` persona must stay one
        // account per role (docs/USER_CLEANUP_PLAN.md).
        $corpUser = User::updateOrCreate(
            ['email' => 'housing.manager@ethiopianairlines.com'],
            [
                'name' => 'Ethiopian Airlines Housing Division',
                'password' => Hash::make('password123'),
                'role' => 'contact',
            ]
        );

        $tenant = CorporateTenant::firstOrCreate(
            ['company_name' => 'Ethiopian Airlines Group'],
            [
                'user_id' => $corpUser->id,
                'tax_id' => 'TIN-0019283746',
                'contact_person' => 'Tewodros Mengistu',
                'contact_email' => 'housing.manager@ethiopianairlines.com',
                'contact_phone' => '+251 91 123 9900',
                'industry' => 'Aviation & Logistics',
                'billing_address' => 'Bole International Airport Complex, PB 1755',
                'city' => 'Addis Ababa',
                'credit_limit' => 5000000.00,
                'status' => 'active',
            ]
        );

        if ((int) $tenant->user_id !== (int) $corpUser->id) {
            $tenant->update(['user_id' => $corpUser->id]);
        }

        $emp1 = CorporateEmployee::firstOrCreate(
            ['employee_id_number' => 'ET-CREW-1049'],
            [
                'corporate_tenant_id' => $tenant->id,
                'full_name' => 'Captain Michael Tadesse',
                'email' => 'michael.tadesse@ethiopianairlines.com',
                'phone' => '+251 91 555 1100',
                'department' => 'Flight Operations (Boeing 787 Fleet)',
                'designation' => 'Senior Captain',
                'status' => 'active',
            ]
        );

        $emp2 = CorporateEmployee::firstOrCreate(
            ['employee_id_number' => 'ET-CREW-2088'],
            [
                'corporate_tenant_id' => $tenant->id,
                'full_name' => 'Bethlehem Haile',
                'email' => 'bethlehem.haile@ethiopianairlines.com',
                'phone' => '+251 91 555 2200',
                'department' => 'In-Flight Services',
                'designation' => 'Chief Purser',
                'status' => 'active',
            ]
        );

        CorporateLeaseAssignment::firstOrCreate(
            ['unit_identifier' => 'Bole Sun Tower Penthouse #1201'],
            [
                'corporate_tenant_id' => $tenant->id,
                'corporate_employee_id' => $emp1->id,
                'assigned_from' => '2026-01-01',
                'assigned_until' => '2026-12-31',
                'monthly_rent_etb' => 75000.00,
                'status' => 'active',
            ]
        );

        CorporateLeaseAssignment::firstOrCreate(
            ['unit_identifier' => 'Kazanchis Executive Suite #405'],
            [
                'corporate_tenant_id' => $tenant->id,
                'corporate_employee_id' => $emp2->id,
                'assigned_from' => '2026-02-01',
                'assigned_until' => '2026-12-31',
                'monthly_rent_etb' => 48000.00,
                'status' => 'active',
            ]
        );

        // White-Label Enterprise Brand Seeding
        CompanyWhiteLabel::firstOrCreate(
            ['subdomain' => 'luxurybole'],
            [
                'user_id' => $corpUser->id,
                'company_name' => 'Luxury Bole Developments',
                'custom_domain' => 'luxurybole.et',
                'brand_name' => 'Luxury Bole Residences & Estates',
                'logo_path' => 'images/luxury/luxury-logo.png',
                'primary_color' => '#1e3a8a',
                'secondary_color' => '#f59e0b',
                'support_phone' => '+251 11 661 0000',
                'support_email' => 'concierge@luxurybole.et',
                'is_active' => true,
            ]
        );

        // Developer Escrow Milestone Seed
        $project = Project::first();
        if ($project) {
            $milestone = ConstructionMilestone::where('project_id', $project->id)->first();
            if ($milestone) {
                DeveloperEscrowDisbursement::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'milestone_id' => $milestone->id,
                    ],
                    [
                        'requested_by' => $corpUser->id,
                        'amount_etb' => 2500000.00,
                        'status' => 'pending',
                        'bank_reference' => 'CBE-ESCROW-2026-099',
                        'inspection_notes' => 'Structural engineering foundation report confirmed compliant with Addis Ababa building code.',
                    ]
                );
            }
        }
    }
}
