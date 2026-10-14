# Functionality Report — Button & Action Audit (Task 2)

Date: 2026-10-14 · Branch: `chore/user-cleanup-and-functional-audit`

**Final state: 954 tests / 954 passed / 5,576 assertions / 0 failed — suite fully green.**
Browser audit: **22/22 accounts** sign in, land on their dashboard, and switch every
sidebar section with **0 console errors, 0 page errors, 0 failed network responses**.

## 1. Method

| Layer | What was done | Result |
| --- | --- | --- |
| Static route scan | Every `route()` call in `app/`, `resources/views/`, `routes/`, `database/` cross-checked against the 450 registered route entries (425 named) | 1 real defect found & fixed, 1 benign pattern, 1 false positive |
| Static form/button scan | Every `<form method action>` matched to the target route's allowed HTTP methods (incl. `@method` spoofing); every literal `href`/`action`/`window.location`/`fetch` URL matched to registered URI patterns | Clean after fixes |
| Test suite | Full `php artisan test --compact`, three times (baseline → after view ports → after final fixes) | 34 → 0 failures |
| Runtime browser test per role | Headless Chromium (Playwright, installed ad-hoc outside the repo): real form login → post-login redirect → click every `[data-target-section]` sidebar link → record console/page/network errors, for all 21 personas + the corporate demo account | 22/22 OK (after 3 defects fixed) |
| Automated crawl test | `tests/Feature/UserDashboardLinksCrawlerTest.php`, extended to all 21 personas: every dashboard returns 200 and every internal link on it returns neither 404 nor 500 | Green |

Re-running the browser audit (needs `npx playwright install chromium`, no repo dependency changes):

```bash
php artisan serve --port=8231
BASE_URL=http://127.0.0.1:8231 node /tmp/opencode/browser_audit.mjs   # report: /tmp/opencode/browser_audit/report.json
```

## 2. Test-suite failures: 34 → 0

Root cause of **every** baseline failure was the same: the in-progress *unified dashboard
migration* (uncommitted WIP) replaced four legacy portal views with one-line stubs
(`@include('dashboards.*')`) whose replacement views had dropped required content, plus
three wiring/data regressions. No test was deleted or weakened; all fixes are in
production code.

### 2.1 Finance desk — 17 failures

Files: `FinanceDashboardPermissionsTest` (4), `FinanceDeskAgentScopingTest` (5),
`FinanceOfficerAccessTest` (2), `FinancialReportingAndCommissionSettlementTest` (2 admin),
`AgentCommissionSplitAndHierarchyTest` (1), `AdminAnalyticsDashboardTest` (1),
`ProfileSelfServiceTest` (1), `StayBookingEngineTest` (1).

- **Cause:** `resources/views/dashboard/finance.blade.php` (1,936 lines at HEAD) was stubbed
  to the new `dashboards/finance.blade.php`, which was missing the payment-queue section,
  lease & rental collection desk, double-entry ledger heading, agency-retained commission
  breakdown, period filter, cashflow/categories charts, permission-gated sidebar items
  (`Payments to Confirm`, `Receive Commission`, `Double-Entry Ledgers`) and the profile
  section/nav link.
- **Fix:** ported all missing sections, nav items and permission gates into the unified view
  (gates computed in-view from `hasPermission()`: `payments.manage`, `commissions.manage`,
  `reconciliation.manage`), keeping every section the new view already had. Controller
  unchanged. Three subtleties fixed along the way: an HTML comment naming the payments
  section sat *outside* its `@if` gate (leaking the needle on revocation), inline
  `@section('title', …)` HTML-escapes `&` (switched to block form for the literal
  `Financial Reporting & Commission Settlement` heading), and the legacy `Co-Brokered` /
  `+X ETB Agency` badges in the commissions table were re-added.

### 2.2 Agent portal — 14 failures

Files: `RealEstateTest` (1), `DeleteOrderCommissionInquiryTest` (2),
`AgentFeedbackManagementTest` (2), `AgentListingManagementTest` (2),
`RegistrationLocationPickerTest` (1), `AgentCommissionSplitAndHierarchyTest` (1),
`FinancialReportingAndCommissionSettlementTest` (1), `StayBookingEngineTest` (2),
`AdminAnalyticsDashboardTest` (1), `PropertyViewingAndOfferNegotiationTest` (1).

- **Cause:** `resources/views/agent/portal.blade.php` (2,851 lines at HEAD) was stubbed to
  `dashboards/agent.blade.php`, missing the feedback desk, buy-request/order delete forms +
  custom confirmation modal, property create/edit forms and location map, stay-bookings
  desk, performance charts (`agent-property-status-chart`, `agent-commission-chart`),
  agent tier badge, earned-commissions desk and the viewings/offers desks.
- **Fix:** ported the missing nav items, sections, forms, modals and chart blocks; added the
  Leaflet CDN tags the location picker depends on. The shared
  `dashboards/partials/profile-management-modal.blade.php` (included by every portal) still
  used an inline `onsubmit="return confirm…"`; replaced with a delegated `submit` listener
  showing the same confirmation dialog (behaviour-preserving, fixes the custom-confirm
  assertion for all portals).

### 2.3 Company dashboard — 1 failure

- **Cause:** `company/dashboard.blade.php` stub dropped the legacy sidebar badge.
- **Fix:** re-added the pending-reports counter (`{{ $pendingReportsCount }} Issues` →
  `1 Issues`) as a view-level `@php` block. Controller unchanged.

### 2.4 Wiring / data regressions — 3 failures

| Failure | Root cause | Fix |
| --- | --- | --- |
| `SupportProviderOwnerActionsTest` — `role.portal` 403 for support/provider/owner | WIP flipped `service_provider.portal` from `role.portal` to `professional.dashboard` in `config/personas.php`, tripping `RolePortalController`'s universal-portal gate | Restored `'portal' => 'role.portal'` (matches HEAD; login still lands on `professional.dashboard` via the hard-coded dashboard URL, and `ProfessionalDashboardController` gates by permission, not portal field) |
| `PermissionDrivenDashboardTest` — revoked seller still sees `data-target-section="properties"` | The seller sidebar `My Properties` link had no permission gate | Wrapped in `@if (auth()->user()?->hasAnyPermission(['properties.create','properties.view','properties.update','properties.delete', …]))` |
| `PropertyViewingAndOfferNegotiationTest` — `36,500,000` missing | Agent-portal half of the page (viewings/offers desks) — resolved by §2.2 | — |

## 3. Static route/button audit findings

1. **Fixed — seller viewings buttons were dead.** `dashboards/seller.blade.php` Confirm/Cancel
   forms called `route('viewings.status')`, which does not exist (RouteNotFoundException → 500
   on submit), and were plain `POST` forms to a `PUT`-only route (405). Corrected to
   `route('viewings.update-status', $vw)` + `@method('PUT')`, matching the canonical form in
   `properties/partials/viewings-desk.blade.php`.
2. **Benign — login form action.** `POST /login` is unnamed; the form generates its action from
   the GET twin `route('login')`. Same URI, so the POST matches correctly — left as is.
3. **False positive.** `PropertyFormRequest::route('property')` reads a route *parameter*, it
   does not generate a URL.
4. **Verified safe:** all JS-constructed URLs (`/rent-schedules/{id}/pay`,
   `/developer/projects/{id}/buildings|milestones|payment-plans`) map to registered POST routes.
5. 117 named routes are not referenced via `route()` today — mostly endpoints the legacy
   (stubbed) views used; they remain registered and are re-referenced by the ported views where
   relevant. None are broken.

## 4. Runtime browser audit findings (22 accounts)

The HTTP suite cannot see these — all three were found by the real-browser pass and fixed:

| # | Symptom | Root cause | Fix |
| --- | --- | --- | --- |
| 1 | `agent@realestate.com` form login bounced with *"Your agent account has been deactivated"* | Demo DB had `agents.is_active = false` for the persona agent, and `DatabaseSeeder` never set `is_active` on its `Agent::updateOrCreate`, so drift could not self-heal | Seeder now writes `'is_active' => true`; demo DB re-converged via `php artisan db:seed` |
| 2 | `support@realestate.com` could not sign in with the documented `secret123` | Demo-DB password drifted from the README-documented value (seeder itself was correct) | Re-converged by `php artisan db:seed` (idempotent — user count stays 26) |
| 3 | `ReferenceError: updateHash is not defined` on the client portal for **every** section click | `client/portal.blade.php` defined `activateSection(target)` but called/used the dropped `updateHash` parameter (two call sites pass it) | Restored the signature: `activateSection(target, updateHash = true)` |

A transient >20 s `load`-event wait on one run traced to an external CDN fetch (Google Fonts);
no pending requests, `readyState: complete` on retry — not an application defect (the audit
script now waits on `domcontentloaded`).

## 5. Automated crawl test

`tests/Feature/UserDashboardLinksCrawlerTest.php` now crawls **all 21 personas**
(was 17 — added `guest`, `landlord`, `campaign_officer`, `verification_company`): each
dashboard returns 200, and every internal `<a href>` on it must return neither 404 nor 500
(200/301/302/307/308/403 accepted). The security-boundary test (403/302 for a buyer on
admin/finance/company/developer desks) and legacy-URL redirect tests remain unchanged.

## 6. Test changes made during Task 2 (with reasons)

| Test | Change | Reason |
| --- | --- | --- |
| `UserDashboardLinksCrawlerTest` | Added the 4 missing personas to the crawl map; description "all 17 dashboards" → "all 21 persona dashboards" | Task 2 requires crawl coverage per role; the old count in the name became factually wrong after extending coverage. No assertion was removed or relaxed |

No other test file was modified. (Task 1's documented `UserDeleteTest`/`RealEstateTest`
soft-delete updates and the reverted experiments are recorded in `docs/USER_CLEANUP_PLAN.md`.)

## 7. Final verification

```bash
php artisan test --compact     # 954 tests, 954 passed, 5,576 assertions, 0 failed
vendor/bin/pint --dirty        # clean
```

| Check | Result |
| --- | --- |
| Full suite | **954 / 954, 0 failures** (baseline was 34 failing / 35 total) |
| Browser audit (per role) | **22 / 22 OK**, 0 console errors, 0 page errors, 0 failed responses |
| Crawl test | 21 personas × dashboard + link crawl green; security boundaries enforced |
| Static scans | route()/form-method/literal-URL audits clean |
| Pint | clean |
