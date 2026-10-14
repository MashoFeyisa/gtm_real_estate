# Known Failures

> **RESOLVED 2026-10-14 — all 34 failures fixed in Task 2 (button & action audit).**
> Final suite: **954 tests / 954 passed / 5,576 assertions / 0 failed**.
> Root causes, fixes, static scan and per-role browser audit are recorded in
> [`docs/FUNCTIONALITY_REPORT.md`](FUNCTIONALITY_REPORT.md). The table below is
> kept as the historical baseline.

Captured 2026-10-14 from `php artisan test --compact` on branch `chore/user-cleanup-and-functional-audit`.

**34 failing tests** out of 952 (917 pass).

### These failures are pre-existing, not caused by the cleanup

Verified by reverting the only cleanup change that touches behaviour (`app/Models/User.php` → `SoftDeletes`) and re-running the affected files: the identical tests fail. The three tests the change *did* break (`UserDeleteTest` ×2 and `RealEstateTest` user-manager delete) were updated to assert soft-delete semantics and now pass.

### Owner decision

Fix them in **Task 2** (button & action audit). Task 2 is complete only when the whole suite is green.

### Rules while fixing

1. Fix the production code.
2. Never delete or weaken a test.
3. Change a test only when the test itself is wrong — then record the reason in the table.

## Failures

| # | Test file | Test | Symptom |
| --- | --- | --- | --- |
| 1 | `tests/Feature/AdminAnalyticsDashboardTest.php` | Agent portal overview displays embedded apexcharts | Rendered page does **not** contain expected text: "agent-property-status-chart" |
| 2 | `tests/Feature/AdminAnalyticsDashboardTest.php` | Finance desk overview displays embedded apexcharts | Rendered page does **not** contain expected text: "finance-cashflow-chart" |
| 3 | `tests/Feature/AdminClientReportTest.php` | Client submitting report notifies company users and appears on company dashboard | Rendered page does **not** contain expected text: "1 Issues" |
| 4 | `tests/Feature/AgentCommissionSplitAndHierarchyTest.php` | Agent portal displays agent tier and includes co brokered commissions in commissions desk | Rendered page does **not** contain expected text: "Senior Consultant" |
| 5 | `tests/Feature/AgentCommissionSplitAndHierarchyTest.php` | Finance dashboard renders agency retained commissions and co brokered breakdown | Rendered page does **not** contain expected text: "Agency Retained Comm." |
| 6 | `tests/Feature/AgentFeedbackManagementTest.php` | Agent portal shows pending and approved feedback states | Rendered page does **not** contain expected text: "Pending review" |
| 7 | `tests/Feature/AgentFeedbackManagementTest.php` | Agent sees their own received feedback in the portal | Rendered page does **not** contain expected text: "Happy Client" |
| 8 | `tests/Feature/AgentListingManagementTest.php` | Agent can load edit form in portal with pre filled values and save changes | Rendered page does **not** contain expected text: "Edit: Initial Luxury Villa" |
| 9 | `tests/Feature/AgentListingManagementTest.php` | Agent portal shows the add property button and the full listing list | Rendered page does **not** contain expected text: "+ Add Property" |
| 10 | `tests/Feature/DeleteOrderCommissionInquiryTest.php` | Agent portal renders delete buttons for orders and messages | Rendered page does **not** contain expected text: "http://localhost:8000/agent-portal/orders/1" |
| 11 | `tests/Feature/DeleteOrderCommissionInquiryTest.php` | Agent portal uses custom confirmation modal and data confirm for buy and rent requests | Rendered page does **not** contain expected text: "id=" |
| 12 | `tests/Feature/FinanceDashboardPermissionsTest.php` | Admins keep full access to the finance desk on a seeded catalog | Rendered page does **not** contain expected text: "Payments to Confirm" |
| 13 | `tests/Feature/FinanceDashboardPermissionsTest.php` | Finance dashboard hides a desk the officer may not use | Rendered page does **not** contain expected text: "Payments to Confirm" |
| 14 | `tests/Feature/FinanceDashboardPermissionsTest.php` | Finance officer dashboard shows every granted desk section | Rendered page does **not** contain expected text: "Payments to Confirm" |
| 15 | `tests/Feature/FinanceDashboardPermissionsTest.php` | Revoking a desk permission hides its finance navigation and section | Rendered page does **not** contain expected text: "Double-Entry Financial Ledgers" |
| 16 | `tests/Feature/FinanceDeskAgentScopingTest.php` | Admins still see every pending payment and ledger entry | Rendered page does **not** contain expected text: "Confidential Buyer Beta" |
| 17 | `tests/Feature/FinanceDeskAgentScopingTest.php` | Agent b sees their own payment but not agent a ledger | Rendered page does **not** contain expected text: "Confidential Buyer Beta" |
| 18 | `tests/Feature/FinanceDeskAgentScopingTest.php` | Agents do not receive global company cashflow charts on the finance desk | Rendered page does **not** contain expected text: "Monthly Cash Inflow vs. Outflow (ETB)" |
| 19 | `tests/Feature/FinanceDeskAgentScopingTest.php` | An agent sees their own pending payment on the finance desk | Rendered page does **not** contain expected text: "Own Deal Payer Alpha" |
| 20 | `tests/Feature/FinanceDeskAgentScopingTest.php` | Finance officers still see every pending payment | Rendered page does **not** contain expected text: "Confidential Buyer Beta" |
| 21 | `tests/Feature/FinanceOfficerAccessTest.php` | Finance officer can operate the financial audit  tax  payments and ledger desk | Rendered page does **not** contain expected text: "Financial Reporting & Commission Settlement" |
| 22 | `tests/Feature/FinanceOfficerAccessTest.php` | Finance officer lease desk renders rent installment schedules and overdue rent | Rendered page does **not** contain expected text: "Rent Installment Schedules" |
| 23 | `tests/Feature/FinancialReportingAndCommissionSettlementTest.php` | Admin can filter financial reporting by period | Rendered page does **not** contain expected text: "This Month" |
| 24 | `tests/Feature/FinancialReportingAndCommissionSettlementTest.php` | Admin can view financial reporting desk with KPI stats and ledger breakdown | Rendered page does **not** contain expected text: "Financial Reporting & Commission Settlement" |
| 25 | `tests/Feature/FinancialReportingAndCommissionSettlementTest.php` | Agent can view commissions desk in their agent portal | Rendered page does **not** contain expected text: "My Earned Commissions &amp; Payouts" |
| 26 | `tests/Feature/PermissionDrivenDashboardTest.php` | Revoking property creation permission blocks seller from creating properties with 403 | Rendered page does **not** contain expected text: "data-target-section=" |
| 27 | `tests/Feature/ProfileSelfServiceTest.php` | The client portal and finance desk expose the profile section and current photo | Rendered page does **not** contain expected text: "data-target-section=" |
| 28 | `tests/Feature/PropertyViewingAndOfferNegotiationTest.php` | It renders viewing and offer desks in admin dashboard and agent portal | Rendered page does **not** contain expected text: "36,500,000" |
| 29 | `tests/Feature/RealEstateTest.php` | Agent can log in and see their portal with notifications and buy requests | Rendered page does **not** contain expected text: "Buy Requests" |
| 30 | `tests/Feature/RegistrationLocationPickerTest.php` | The agent portal property forms render the map picker with the current pin | Rendered page does **not** contain expected text: "create-property-location-map" |
| 31 | `tests/Feature/StayBookingEngineTest.php` | Agent stay bookings section shows the payment state and receipt link | Rendered page does **not** contain expected text: "Stay Bookings on My Listings" |
| 32 | `tests/Feature/StayBookingEngineTest.php` | Assigned agent can view and delete a stay booking for their property | Rendered page does **not** contain expected text: "STAY-AA-202610-AGENT1" |
| 33 | `tests/Feature/StayBookingEngineTest.php` | Bank transfer stay payment waits for finance confirmation before confirming the reservation | Rendered page does **not** contain expected text: "Payments Awaiting Confirmation" |
| 34 | `tests/Feature/SupportProviderOwnerActionsTest.php` | Support provider and owner desks render their action forms | Failed asserting that 403 is identical to 200. |

## Grouped by area (confirm root cause while fixing)

| Area | Test files | Fails | Likely root cause |
| --- | --- | --- | --- |
| Agent portal (feedback, listings, delete buttons, confirmation modal, tier, commissions) | AgentFeedbackManagementTest, AgentListingManagementTest, DeleteOrderCommissionInquiryTest, AgentCommissionSplitAndHierarchyTest, RegistrationLocationPickerTest, RealEstateTest | 8 | Agent portal sections not rendered for the agent persona. |
| Finance desk (permissions, scoping, officer access, reporting) | FinanceDashboardPermissionsTest, FinanceDeskAgentScopingTest, FinanceOfficerAccessTest, FinancialReportingAndCommissionSettlementTest | 13 | Finance desk sections empty/hidden for officers, agents and admins. |
| Support provider & owner desk actions | SupportProviderOwnerActionsTest | 8 | Desk routes return 403 for the seeded persona accounts. |
| Stay booking engine | StayBookingEngineTest | 3 | Finance confirmation / payment-state / receipt sections missing. |
| Admin analytics, client report, profile self-service | AdminAnalyticsDashboardTest, AdminClientReportTest, ProfileSelfServiceTest | 4 | Overview charts, company notification and profile sections missing. |
| Permission-driven dashboard & viewings/offers desks | PermissionDrivenDashboardTest, PropertyViewingAndOfferNegotiationTest | 2 | Expected sections not rendered. |

## Tracking

| Status | Count |
| --- | --- |
| Open | 0 |
| Fixed | 34 |

All rows moved to Fixed on 2026-10-14 — production code only; no test was deleted or weakened.
The single test edit of Task 2 (`UserDashboardLinksCrawlerTest` persona map + description count)
extended coverage and is recorded with its reason in `docs/FUNCTIONALITY_REPORT.md`.

## How to re-check

```bash
php artisan test --compact
```

Baseline before Task 2 fixes (unchanged by Task 1): **917 passed / 35 failed**.
Final state after Task 2: **954 passed / 0 failed** (suite grew by the Task 1
`SeededPersonaLoginTest` pair and the extended crawler coverage).
