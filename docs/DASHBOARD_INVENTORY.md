# Dashboard Inventory & Migration Map

This document captures the complete inventory of all existing dashboards and portals in `gtm_real_estate` as required by **Phase 0** before consolidating them into the **Unified Dashboard Shell**.

---

## 1. Inventory Summary Matrix

| # | Persona / Role | Route Name | URL Path | Controller & Action | Primary View |
|---|---|---|---|---|---|
| 1 | **Admin (Administrator)** | `dashboard` | `/dashboard` | `WorkerManagementController@dashboard` | `resources/views/dashboard.blade.php` |
| 2 | **Buyer** | `buyer.dashboard` | `/dashboard/buyer` | `BuyerDashboardController@index` | `resources/views/dashboards/buyer.blade.php` |
| 3 | **Tenant** | `tenant.dashboard` | `/dashboard/tenant` | `TenantDashboardController@index` | `resources/views/dashboards/tenant.blade.php` |
| 4 | **Guest / Traveler** | `guest.dashboard` | `/dashboard/guest` | `GuestDashboardController@index` | `resources/views/dashboards/guest.blade.php` |
| 5 | **Seller / Landlord** | `seller.dashboard` | `/dashboard/seller` | `SellerDashboardController@index` | `resources/views/dashboards/seller.blade.php` |
| 6 | **Host** | `host.dashboard` | `/dashboard/host` | `HostDashboardController@index` | `resources/views/dashboards/host.blade.php` |
| 7 | **Agent** | `agent.portal` | `/agent-portal` | `AgentPortalController@index` | `resources/views/agent/portal.blade.php` |
| 8 | **Real Estate Company** | `company.dashboard` | `/company` | `CompanyDashboardController@index` | `resources/views/company/dashboard.blade.php` |
| 9 | **Developer** | `developer.portal` | `/developer-portal` | `DeveloperPortalController@index` | `resources/views/developer/portal.blade.php` |
| 10 | **Property Manager** | `property-manager.dashboard` | `/dashboard/property-manager` | `PropertyManagerDashboardController@index` | `resources/views/dashboards/property_manager.blade.php` |
| 11 | **Professional** | `professional.dashboard` | `/dashboard/professional` | `ProfessionalDashboardController@index` | `resources/views/dashboards/professional.blade.php` |
| 12 | **Cleaner / Housekeeping**| `cleaner.dashboard` | `/dashboard/cleaner` | `CleanerDashboardController@index` | `resources/views/dashboards/cleaner.blade.php` |
| 13 | **Investor** | `investor.dashboard` | `/dashboard/investor` | `InvestorDashboardController@index` | `resources/views/dashboards/investor.blade.php` |
| 14 | **Verification Officer** | `verification-officer.dashboard` | `/dashboard/verification-officer` | `VerificationOfficerDashboardController@index` | `resources/views/dashboards/verification_officer.blade.php` |
| 15 | **Verification Company** | `verification_company.portal` | `/verification-company` | `VerificationCompanyController@index` | `resources/views/verification-company/portal.blade.php` |
| 16 | **Moderator** | `moderator.dashboard` | `/dashboard/moderator` | `ModeratorDashboardController@index` | `resources/views/dashboards/moderator.blade.php` |
| 17 | **Finance Officer** | `finance.index` | `/finance` | `FinancialReportingController@index` | `resources/views/dashboard/finance.blade.php` |
| 18 | **Customer Support** | `customer-support.dashboard`| `/dashboard/customer-support` | `CustomerSupportDashboardController@index` | `resources/views/dashboards/customer_support.blade.php` |
| 19 | **Receptionist** | `receptionist.dashboard` | `/receptionist` | `ReceptionistDashboardController@index` | `resources/views/dashboard/receptionist.blade.php` |
| 20 | **Campaign Officer** | `campaign.dashboard` | `/dashboard/campaign` | `CampaignController@index` | `resources/views/dashboards/campaign_officer.blade.php` |
| 21 | **Corporate Tenancy** | `corporate.portal` | `/corporate-portal` | `CorporateTenancyController@portal` | `resources/views/corporate/portal.blade.php` |
| 22 | **Generic Persona Desk** | `role.portal` | `/persona-portal` | `RolePortalController@index` | `resources/views/persona/portal.blade.php` |
| 23 | **Generic Client Desk** | `client.portal` | `/client-portal` | `ClientPortalController@index` | `resources/views/client/portal.blade.php` |

---

## 2. Detailed Breakdown of Dashboard Capabilities

### 1. Admin Dashboard (`/dashboard`)
- **Controller**: `WorkerManagementController@dashboard`
- **Views**: `resources/views/dashboard.blade.php`, `resources/views/dashboard/partials/*`
- **Features & Business Logic**:
  - Global system metrics: users count, properties, listings, commissions, total revenue.
  - CRUD User Management: create user, edit details, assign roles/permissions, suspend/activate, delete.
  - CRUD Property Management: create, update, delete, publish/unpublish, mark sold, set cover image, agreement generator.
  - CRUD Listings, Orders/Agreements, Job Applications, Articles & News, Company Branches.
  - Roles & Permissions Desk: create/edit custom roles, grant/revoke permissions per role and per user.
  - Audit Logs viewer and platform analytics.

### 2. Buyer Dashboard (`/dashboard/buyer`)
- **Controller**: `BuyerDashboardController@index`
- **View**: `resources/views/dashboards/buyer.blade.php`
- **Features**:
  - Saved properties & collections.
  - Offers & negotiation rounds (active, countered, accepted, rejected).
  - Viewing requests & schedule status.
  - Inquiries sent to agents and property owners.

### 3. Tenant Dashboard (`/dashboard/tenant`)
- **Controller**: `TenantDashboardController@index`
- **View**: `resources/views/dashboards/tenant.blade.php`
- **Features**:
  - Active leases, lease documents, rent schedule.
  - Rent payment invoices & receipts.
  - Maintenance request submission, status updates, contractor logs.

### 4. Guest / Traveler Dashboard (`/dashboard/guest`)
- **Controller**: `GuestDashboardController@index`
- **View**: `resources/views/dashboards/guest.blade.php`
- **Features**:
  - Upcoming, active, and past short-stay bookings.
  - Check-in instructions & host contact info.
  - Favorite stays and travel receipts.

### 5. Seller / Landlord Dashboard (`/dashboard/seller`)
- **Controller**: `SellerDashboardController@index`
- **View**: `resources/views/dashboards/seller.blade.php`
- **Features**:
  - Property listing submission & status (draft, pending, published).
  - Incoming buyer inquiries.
  - Incoming offers with accept/reject/counter actions.
  - Viewing appointment requests and schedule confirmations.

### 6. Host Dashboard (`/dashboard/host`)
- **Controller**: `HostDashboardController@index`
- **View**: `resources/views/dashboards/host.blade.php`
- **Features**:
  - Short-stay properties management & instant booking toggles.
  - Reservation calendar and availability blocks.
  - iCal calendar feed synchronization.
  - Guest reviews & host response publishing.

### 7. Agent Portal (`/agent-portal`)
- **Controller**: `AgentPortalController@index`
- **View**: `resources/views/agent/portal.blade.php`
- **Features**:
  - Client CRM: buyers, sellers, tenants, leads funnel.
  - Assigned listings, performance statistics.
  - Commission claims, deals closed, agreement generation.
  - Inquiries triage and viewing tours schedule.

### 8. Company Dashboard (`/company`)
- **Controller**: `CompanyDashboardController@index`
- **View**: `resources/views/company/dashboard.blade.php`
- **Features**:
  - Agency branch management and locations.
  - Real estate agents roster, commission splits.
  - Company-wide listing inventory and sales reports.

### 9. Developer Portal (`/developer-portal`)
- **Controller**: `DeveloperPortalController@index`
- **View**: `resources/views/developer/portal.blade.php`
- **Features**:
  - Construction projects & phases tracking.
  - Milestone updates, photos, completion percentage.
  - Off-plan unit roll, reservations, buyer contracts.

### 10. Property Manager Dashboard (`/dashboard/property-manager`)
- **Controller**: `PropertyManagerDashboardController@index`
- **View**: `resources/views/dashboards/property_manager.blade.php`
- **Features**:
  - Multi-unit residential/commercial portfolio roll.
  - Unit occupancy status, vacant units tracking.
  - Tenant rent collections roll & overdue alerts.
  - Maintenance tickets dispatch and cleaner work orders.

### 11. Professional Service Provider Dashboard (`/dashboard/professional`)
- **Controller**: `ProfessionalDashboardController@index`
- **View**: `resources/views/dashboards/professional.blade.php`
- **Features**:
  - Service requests from clients, landlords, and property managers.
  - Work order job accept/decline, completion documentation.
  - Earnings and invoice tracking.

### 12. Cleaner Dashboard (`/dashboard/cleaner`)
- **Controller**: `CleanerDashboardController@index`
- **View**: `resources/views/dashboards/cleaner.blade.php`
- **Features**:
  - Daily unit turnaround cleaning tasks.
  - Cleaning checklist verification and completion timestamps.
  - Ready-for-guest status reporting.

### 13. Investor Dashboard (`/dashboard/investor`)
- **Controller**: `InvestorDashboardController@index`
- **View**: `resources/views/dashboards/investor.blade.php`
- **Features**:
  - Real estate portfolio asset allocation.
  - Yield projections, cap rates, rental dividend cashflows.
  - High-yield property recommendations.

### 14. Verification Officer Dashboard (`/dashboard/verification-officer`)
- **Controller**: `VerificationOfficerDashboardController@index`
- **View**: `resources/views/dashboards/verification_officer.blade.php`
- **Features**:
  - Title deed and ownership verification queue.
  - Site inspection scheduling and findings report.
  - Listing badge approval / fraud rejection.

### 15. Verification Company Portal (`/verification-company`)
- **Controller**: `VerificationCompanyController@index`
- **View**: `resources/views/verification-company/portal.blade.php`
- **Features**:
  - Institutional property audits and compliance checks.
  - Verification certificate issuance.

### 16. Moderator Dashboard (`/dashboard/moderator`)
- **Controller**: `ModeratorDashboardController@index`
- **View**: `resources/views/dashboards/moderator.blade.php`
- **Features**:
  - Misleading listing reports, duplicate post flags.
  - Inappropriate review flags and dispute moderation.
  - Action logs: approve, remove, warning, suspension.

### 17. Finance Officer Dashboard (`/finance`)
- **Controller**: `FinancialReportingController@index`
- **View**: `resources/views/dashboard/finance.blade.php`
- **Features**:
  - General cash ledger, income/expense auditing.
  - Commission payouts, escrow releases, refunds.
  - Receptionist / cashier shift verification.
  - Tax withholding calculations and CSV exports.

### 18. Customer Support Dashboard (`/dashboard/customer-support`)
- **Controller**: `CustomerSupportDashboardController@index`
- **View**: `resources/views/dashboards/customer_support.blade.php`
- **Features**:
  - Customer ticket queue, triage by priority.
  - Inquiries assistance and account issue escalation.

### 19. Receptionist Dashboard (`/receptionist`)
- **Controller**: `ReceptionistDashboardController@index`
- **View**: `resources/views/dashboard/receptionist.blade.php`
- **Features**:
  - Front desk folio: active guests, check-ins, check-outs.
  - Walk-in reservations, key handovers, payments.
  - Shift reconciliation and handover notes.

### 20. Campaign Officer Dashboard (`/dashboard/campaign`)
- **Controller**: `CampaignController@index`
- **View**: `resources/views/dashboards/campaign_officer.blade.php`
- **Features**:
  - Marketing campaign scheduling.
  - Banner ad placements and click-through performance.

---

## 3. Migration Plan to Unified Dashboard
1. **Single Layout Adoption**:
   - Standardize on `resources/views/dashboards/layout.blade.php` (or consolidated unified layout).
   - Sidebar hierarchy:
     1. User profile block (photo, name, role badge with profile management modal trigger).
     2. Home (public site link) and Overview (personal summary).
     3. Permission-driven menu groups dynamically retrieved from the database.
     4. Logout button at bottom.
   - Topbar:
     - Global search with shortcut.
     - Notifications bell with live unread count.
     - 3-Language switcher (English, Amharic, Afaan Oromo).
     - Light / Dark theme toggle.
     - User profile dropdown menu.
2. **Permission-Driven Navigation**:
   - Driven entirely by DB `permissions` with `menu_label`, `icon`, `route`, and `group`.
   - Never show admin links to non-admin users.
   - Restrict guest users to public browsing; prompt login/registration for sensitive actions.
3. **Module Desks**:
   - Maintain all existing business logic and controllers without deletion until Phase 6.
   - Render appropriate desks dynamically within the unified shell based on user role and permissions.
