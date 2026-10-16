# Fix backlog

Derived from **section 7 (Reality check)** of `docs/HOW_THE_PLATFORM_WORKS.md`. Every
claim in section 7 was re-checked against the code before this list was written; the
guide has been corrected where it was wrong (see the bottom of this file).

Priority meanings:

- **P0 — security.** Money or privilege can be taken by the wrong person.
- **P1 — money correctness.** The books can be wrong.
- **P2 — workflow gaps.** The feature is half-wired or inconsistent.
- **P3 — polish.** Cosmetic or documentation.

Each item lists the **file**, the **risk**, the **fix**, and the **test** that proves it.

---

## P0 — security (done in this pass)

### P0.1 Payment gateway callback was unauthenticated and not idempotent

- **File:** `app/Http/Controllers/PaymentController.php`, `app/Http/Middleware/VerifyPaymentGatewaySignature.php`, `app/Models/PaymentGatewayLog.php`, `database/migrations/2026_10_16_090000_create_payment_gateway_logs_table.php`, `config/services.php`, `routes/web.php`
- **Risk:** `POST /payments/callback` accepted anyone. Knowing a transaction number was enough to settle a payment, and a replayed webhook could open a second payment row.
- **Fix:** the route now runs the `provider.signature` middleware, which verifies an HMAC-SHA256 signature over the raw body per provider (a provider with no configured secret rejects every call). Every call — accepted or rejected — is written to `payment_gateway_logs`. The callback resolves or creates the payment by provider reference inside a locked transaction, so the same reference twice creates one payment and settles once.
- **Test:** `tests/Feature/PaymentCallbackSecurityTest.php` — unsigned/wrong-secret callbacks are refused and logged; a valid callback settles once; the same reference twice yields one payment.

### P0.2 Telebirr verified itself

- **File:** `app/Http/Controllers/PaymentController.php` (`store`)
- **Risk:** a Telebirr payment was created `completed` with `verified_by` set to the payer. A payer could be the verifier of their own payment.
- **Fix:** no method is "instant" any more. Every submitted payment is recorded `pending` with `verified_at`/`verified_by` null and no ledger entries; only finance can confirm it through `payments.verify`.
- **Test:** `tests/Feature/PaymentCallbackSecurityTest.php` and `tests/Feature/EthiopianPaymentAndFinancialLedgerTest.php` — a Telebirr payment stays pending, the payer is forbidden from verifying it, and finance settles it.

### P0.3 Developer escrow request/approve had no policy

- **File:** `app/Policies/DeveloperEscrowDisbursementPolicy.php`, `app/Providers/AppServiceProvider.php`, `app/Http/Controllers/CorporateTenancyController.php`
- **Risk:** any signed-in user could request a disbursement against any project and approve any disbursement — a direct path to moving developer escrow funds.
- **Fix:** `Gate::authorize('request', $project)` allows only the project's own developer; `Gate::authorize('approve', ...)` allows only finance/admin and never the requester. A milestone must belong to the project, and only a `pending` request can be approved.
- **Test:** `tests/Feature/DeveloperEscrowAuthorizationTest.php` — owner may request; strangers/buyers may not; finance may approve; non-finance may not; the requester cannot approve their own request; an already-disbursed request cannot be approved twice.

### P0.4 No route-level permission middleware; unknown keys defaulted to allow

- **File:** `app/Models/User.php` (`hasPermission`), `routes/web.php`, `config/personas.php`
- **Risk:** `routes/web.php` used only `auth`/`agent`, and a permission key that was not in the catalogue was granted by default — a typo or a missing catalogue row silently widened access.
- **Fix:** the money routes (refunds, invoices, payouts, commissions, payment verification, shift reconciliation) now carry `permission:` middleware. `hasPermission` returns **false** for any key that is not in the catalogue. Every key already referenced in code was added to the catalogue first — `bookings.manage` and `lease.view` as persona grants, and the remaining 23 keys that no persona grants into a `catalogue` list in `config/personas.php` so they resolve (and stay denied) rather than being unknown. The administrator boundary (admins hold every permission) is unchanged.
- **Test:** `tests/Feature/PermissionEnforcementTest.php` — unknown keys are denied, catalogued keys still resolve, admins are unaffected, and route gates return 403 for a caller without the capability.

### P0.5 Refunds could exceed the payment, and never reversed the payable

- **File:** `app/Http/Controllers/FinancialReportingController.php` (`issueRefund`), `app/Models/Payment.php` (`reversePayableAfterRefund`)
- **Risk:** the refund cap was the original amount, so a payment of 1000 could be refunded 1000 twice. The booking/offer/unit/rent schedule the payment had advanced was never reversed or flagged.
- **Fix:** the cap is the original amount minus what has already been refunded, re-checked under a row lock. Each refund writes an audit ledger debit and reverses or flags the linked booking, offer, unit reservation or rent schedule.
- **Test:** `tests/Feature/RefundIntegrityTest.php` — over-refunding is refused, a fully refunded payment cannot be refunded again, every refund writes a debit, and the linked booking is reversed/flagged.

### P0.6 Demo accounts and the login quick-fill existed in every environment

- **File:** `app/Support/DemoData.php`, `database/seeders/DatabaseSeeder.php`, `app/Console/Commands/DemoUserAccountsCommand.php`, `app/Console/Commands/DemoResetCommand.php`, `resources/views/auth/login.blade.php`
- **Risk:** the demo accounts share the public password `secret123` and the login page offered them on a one-click quick fill. On a production deploy that is an open door.
- **Fix:** `DemoData::allowed()` is true only for `APP_ENV=local` (or the test environment). The seeder returns early otherwise, the `demo:*` commands fail with a message, and the quick-fill block is hidden. `demo:purge` (see P0.10) cleans up rows that predate the guard.
- **Test:** `tests/Feature/DemoAccountEnvironmentTest.php` — production seeding creates no demo accounts and hides the quick-fill; local seeding still creates them.

---

## P0 follow-up checks (hardening pass on the accepted P0)

### P0.7 Gateway callback had no replay window and no flood guard

- **File:** `app/Http/Middleware/VerifyPaymentGatewaySignature.php`, `app/Providers/AppServiceProvider.php`, `routes/web.php`, `config/services.php`, `database/migrations/2026_10_16_100000_add_provider_timestamp_to_payment_gateway_logs_table.php`
- **Risk:** a signature over the body alone can be replayed forever, and the unauthenticated callback could be flooded to exhaust the audit table.
- **Fix:** the HMAC now covers `<timestamp>.<raw body>`; `X-Provider-Timestamp` is required and must sit inside the provider's `tolerance_seconds` window, and an empty or missing secret still rejects every call (fail closed). The route carries a `payments-callback` limiter (60/min per IP, configurable) and the verified timestamp is stored on the audit row.
- **Test:** `tests/Feature/PaymentCallbackSecurityTest.php` — missing, stale and future timestamps are refused, an empty secret rejects, and the endpoint returns 429 past the limit.

### P0.8 A payer could still verify their own payment

- **File:** `app/Http/Controllers/PaymentController.php` (`verify`, `reject`, `abortIfPaymentOriginator`)
- **Risk:** the confirm/reject gate was `isFinanceStaff()` only, so an admin or finance officer who was also the payer could confirm their own money.
- **Fix:** whoever submitted the payment (by `user_id` or a matching `payer_email`) is forbidden from verifying or rejecting it, mirroring the escrow separation of duties.
- **Test:** `tests/Feature/PaymentSeparationOfDutiesTest.php` — a finance officer, an admin and a matching-email payer are all refused; an independent officer still verifies.

### P0.9 No route/permission tripwires, and no guarantee the catalogue is seeded on deploy

- **File:** `tests/Feature/RouteAuthorizationCoverageTest.php`, `tests/Feature/PermissionCatalogueCoverageTest.php`, `app/Console/Commands/SyncPermissionsCommand.php`, `composer.json`, `.github/workflows/laravel.yml`, `config/personas.php` (`aliases`)
- **Risk:** a new route could ship without a guard, a permission typo would silently default-deny, and a permission added to config might never be seeded in production.
- **Fix:** one test lists every route with no auth/permission middleware and fails when the set changes (the 93 intentionally-public routes are documented in an allowlist); another scans code for permission keys and asserts each is catalogued or a declared alias (the alias map now lives in `config/personas.php`). `permissions:sync` re-runs the idempotent seeder and is wired into `composer setup`, a new `composer deploy` script, and the CI workflow.
- **Test:** the two audit tests plus `tests/Feature/SyncPermissionsCommandTest.php`.

### P0.10 `.env.example` shipped `APP_ENV=local`, and demo rows could linger

- **File:** `.env.example`, `app/Console/Commands/PurgeDemoAccountsCommand.php`
- **Risk:** copying the example produced a local environment in production, and a database seeded before the demo guard could still carry the public-password accounts.
- **Fix:** `.env.example` now defaults to `APP_ENV=production`; `demo:purge` finds the `is_demo` / `@realestate.com` accounts, breaks the shared password, deactivates their agent profiles and removes their access (refusing in local/testing without `--force`).
- **Test:** `tests/Feature/DemoAccountEnvironmentTest.php` — the template is not local, the command refuses while demo data is allowed, and a forced purge removes demo accounts while keeping real ones and blocking the old password.

---

## P1 — money correctness

### P1.1 Ledger was not double-entry

- **File:** `app/Services/LedgerService.php`, `app/Models/Payment.php` (`settle`), `app/Models/Commission.php` (`settlePayout`), `app/Http/Controllers/FinancialReportingController.php` (refund, payout), `app/Http/Controllers/{LeaseManagement,ReceptionistDashboard,WorkerManagement}Controller.php`, migration `2026_10_15_232000_add_transaction_group_to_financial_ledgers_table.php`
- **Risk:** `settle()` wrote credit legs only. The books balanced only by convention, and the UI advertised "Double-Entry Ledgers" that did not exist.
- **Fix:** every money path now posts a balanced journal through `LedgerService::post()`, which refuses to write an unbalanced posting and stamps the legs with a shared `transaction_group`. A settled payment debits `cash` and credits the beneficiary (gross less the platform fee) plus the platform fee; a commission/payout debits the gross and credits net cash plus withholding; a refund debits the adjustment and credits cash. The direct debit in `CommissionSplitService` and the receptionist / lease / website-commission writers were converted too, so nothing writes a lone leg any more.
- **Test:** `tests/Feature/FinanceSettingsAndDoubleEntryLedgerTest.php` exercises payment verify, commission settle, refund and payout, then asserts every `transaction_group` has debits equal to credits.

### P1.2 Rental commission was 100% of one month's rent

- **File:** `app/Http/Controllers/ViewingOfferController.php` (rental branch)
- **Risk:** a rental commission was recorded as `commission_rate => 100.0`, i.e. the whole accepted amount, not a rate.
- **Fix:** the rate is now `FinanceSettings::defaultCommissionRate()` (the one settings-backed default) and the amount is `commissionFor($acceptedAmount, $rate)`.
- **Test:** `FinanceSettingsAndDoubleEntryLedgerTest` — with `finance.default_commission_rate = 3`, a rental deal records a 3% commission (3,000 on a 100,000 rent), not the full month.

### P1.3 Commission defaults disagreed

- **File:** `app/Services/FinanceSettings.php` (`DEFAULT_COMMISSION_RATE`), `ViewingOfferController.php` (`?? 2.0`), `AgentPortalController.php` (`?? 5.00`), `UserManagementController.php` (`?? 5`), `CompanyDashboardController.php` (`?? 70.0`)
- **Risk:** the same action produced a different commission depending on which code path ran.
- **Fix:** one source of truth — `FinanceSettings::defaultCommissionRate()` reads `finance.default_commission_rate` (default 5.0) and every path falls back to it.
- **Test:** the rate comes from settings and drives the rental conversion (see P1.2), `commissionFor()`, and the `Agent`/company creation defaults.

### P1.4 `CommissionSplitService` split engine is never called

- **File:** `app/Services/CommissionSplitService.php` (`calculateSplits`, `recordDealCommission`)
- **Risk:** tiers, co-broking and referral splits are fully written and tested but dead, so those commissions are never actually split or recorded.
- **Fix:** call `recordDealCommission` when a deal closes (the same place `Commission` is created).
- **Test:** a feature test that closing a co-brokered deal creates the expected split rows.

### P1.5 Commission settlement and payout have no UI

- **File:** `app/Http/Controllers/FinancialReportingController.php` (`settleCommission`, `storePayoutRequest`), `resources/views/dashboards/finance.blade.php`
- **Risk:** the routes exist (`commissions.settle`, `finance.payouts.store`) but no screen posts to them, so finance cannot actually pay anyone from the app.
- **Fix:** add the settle and payout-request forms to the finance desk; validate against the agent's balance.
- **Test:** a feature test that the desk renders a working settle form and that settling moves the ledger.

### P1.6 Stay cancellation refund moved no money — FIXED

- **File:** `app/Http/Controllers/GuestDashboardController.php` (`cancelBooking`), `app/Services/RefundService.php`
- **Risk:** the refund was calculated and shown, but no `Payment` and no ledger entry was written, so the books never saw it.
- **Fix:** a cancellation that owes money now advances the booking's payment and posts a balanced journal through the one refund writer (`RefundService`), the same writer the finance desk uses. The cancellation policy retains the whole service fee, so the refund comes out of the accommodation revenue and no fee or VAT is reversed.
- **Test:** `StayAndRentMoneyIntegrityTest` — a stay cancellation writes a 6,500 refund debit, a 6,500 cash credit, keeps the fee, and balances.

### P1.7 Rent paid via the landlord route left no financial trace — FIXED

- **File:** `app/Http/Controllers/SellerDashboardController.php` (`recordRentPayment`), `app/Http/Controllers/LeaseManagementController.php` (`recordInstallmentPayment`), `app/Services/RentPaymentService.php`
- **Risk:** the landlord path updated the schedule and an audit log but created no payment and no ledger row, unlike the tenant and receptionist paths.
- **Fix:** both rent desks now call one writer (`RentPaymentService`), which creates a verified payment row and posts a balanced journal. Rent carries no platform fee today, but the fee and VAT still come from `FinanceSettings`, so both desks honour a future rent fee without a code change.
- **Test:** `StayAndRentMoneyIntegrityTest` — a landlord-recorded rent payment writes a completed payment and a balanced `payment:{id}` journal.

### P1.8 Tax withholding ledger entries were never written

- **File:** `app/Models/Commission.php` (`settlePayout`), `app/Http/Controllers/FinancialReportingController.php` (`processPayout`), `app/Http/Controllers/WorkerManagementController.php` (mark paid)
- **Risk:** the `tax_withholding` category existed but no entry ever used it, so withholding was only a description on a commission.
- **Fix:** settling a commission or disbursing a payout now writes a `tax_withholding` credit for the withheld amount (and debits the gross), balancing the journal.
- **Test:** `FinanceSettingsAndDoubleEntryLedgerTest` asserts a `tax_withholding` credit of 5,000 at a 5% settings rate; `Batch4OperationsGovernanceAndFinanceTest` asserts the payout journal carries the 400 withholding credit and balances.

### P1.9 Platform fee, VAT, withholding and commission were hard-coded

- **File:** `app/Services/FinanceSettings.php`, `PaymentController.php`, `StayBookingController.php`, `FinancialReportingController.php`, `Api/IntegrationApiController.php`, `CorporateTenancyController.php`, `CommissionSplitService.php`
- **Risk:** the 2% fee, 15% VAT, 2% withholding, the 2%/5%/70% commission defaults and the 15% experience fee were literals, contrary to SRS §171 ("the application should not hard-code one permanent tax rate") and §195 ("rates must not be hard-coded").
- **Fix:** one `FinanceSettings` service reads `finance.platform_fee_rate`, `finance.vat_rate`, `finance.withholding_tax_rate`, `finance.default_commission_rate` and `finance.experience_platform_fee_rate` (with the historic values as defaults) — and, since P1.13, the dated `finance_rates` rule book in front of them; every money path reads it, and `net_amount` has a single formula (`FinanceSettings::netAmount`, `amount - platform_fee - vat`, see P1.12) shared by the controller and the model.
- **Test:** `FinanceSettingsAndDoubleEntryLedgerTest` — changing `finance.platform_fee_rate`/`finance.vat_rate` changes the computed fee/VAT/net, and the model and controller agree on `net_amount`.

### P1.10 No real money movement and no bank reconciliation

- **File:** `config/services.php`, `app/Http/Controllers/PaymentController.php`
- **Risk:** there is no outbound gateway, no bank call, and `bank_transaction_reference` is never matched against a bank statement.
- **Fix:** add the gateway SDK/keys and a reconciliation step that matches stored references to a statement import.
- **Test:** a test that a matching statement line marks a payment reconciled.

### P1.11 Sale escrow / deal closing does not exist

- **File:** `app/Http/Controllers/ViewingOfferController.php:429`
- **Risk:** the code writes an "earnest money escrow milestone initiated" message but no escrow record exists; only developer projects have escrow.
- **Fix:** model a sale escrow (hold, release, refund) or remove the misleading message.
- **Test:** a feature test asserting a sale offer conversion creates an escrow record.

---

## P1 follow-up checks (rate history, VAT, legacy books, precision, refunds, governance)

### P1.12 VAT on the platform fee was never posted

- **File:** `app/Models/Payment.php` (`settle`), `app/Services/FinanceSettings.php` (`netAmount`), `app/Http/Controllers/FinancialReportingController.php`
- **Risk:** the fee was treated as VAT-inclusive: `tax_amount` was computed but never written to the ledger, so the VAT the platform owed the authority was invisible, and the beneficiary's net did not reflect it.
- **Fix:** VAT is now **charged on top of the fee** and posted to its own `vat_payable` account. The single `net_amount` formula is `amount - platform_fee - vat`, and the payment journal debits `cash` and credits the beneficiary net, `platform_fee`, and `vat_payable`. The finance desk's VAT provision now reads the `vat_payable` account instead of re-deriving it from today's rate.
- **Test:** `FinanceSettingsAndDoubleEntryLedgerTest` — a 50,000 payment posts a 1,000 fee and a 150 VAT credit, nets 48,850, and the journal balances.

### P1.13 Rates had no history or effective dates

- **File:** `app/Models/FinanceRate.php`, `app/Models/CommissionRule.php`, migrations `2026_10_16_120000_*`, `2026_10_16_121000_*`, `app/Services/FinanceSettings.php`, `app/Models/Payment.php`
- **Risk:** every calculation read "today's" rate, so a rate change silently rewrote the meaning of old records and there was no way to say when a rate applied.
- **Fix:** a dated `finance_rates` table is the tax engine (SRS §171): a rate is a fact with an `effective_from`/`effective_to` window and a reason, resolved at the moment of the transaction and falling back to `site_settings`, then the shipped default. `payments` snapshot `platform_fee_rate` and `vat_rate`; `financial_ledgers` snapshot the rate on each rate-bearing leg. Nothing recalculates an old record.
- **Test:** `FinanceSettingsAndDoubleEntryLedgerTest` — a snapshot survives a later rate change, and a dated rule only applies from its effective date.

### P1.14 Commission had one default and no overrides

- **File:** `app/Models/CommissionRule.php`, `app/Services/FinanceSettings.php` (`commissionRateFor`), `app/Http/Controllers/ViewingOfferController.php`, `app/Http/Controllers/AgentPortalController.php`
- **Risk:** the commission was the shared default (or a legacy per-agent column) with no way to vary it by company, property type or date (SRS §99).
- **Fix:** a dated `commission_rules` table overrides the default by agent, then property type, then company, with an effective date, falling back to the one default rate. The rental and sale conversion paths and the agent portal read it.
- **Test:** `FinanceSettingsAndDoubleEntryLedgerTest` — agent beats property type beats company beats the default.

### P1.15 Legacy ledger rows were indistinguishable from current ones

- **File:** migration `2026_10_16_122000_*`, `app/Models/FinancialLedger.php`, `app/Http/Controllers/FinancialReportingController.php`, `database/factories/FinancialLedgerFactory.php`, `docs/LEDGER_RECONCILIATION.md`
- **Risk:** after the double-entry change, old single-sided rows sat in the same table as balanced journals with nothing to tell them apart, so a balance audit or a category total could silently mix them.
- **Fix:** `is_legacy` (default false) is set true once, for every row that predates the migration; old rows are never rewritten. Reports sum both kinds by category but the balance audit only covers `transaction_group` journals, the desk surfaces a legacy count, and the CSV export labels each row. `docs/LEDGER_RECONCILIATION.md` gives the seeded before/after totals and explains every difference.
- **Test:** `LegacyLedgerReconciliationTest` — new rows are current, a legacy row is excluded from the audit, the report counts both and still sums the categories, and the export flags them.

### P1.16 The company "commission split" default had been clobbered

- **File:** `app/Http/Controllers/CompanyDashboardController.php` (`storeAgent`)
- **Risk:** P1.3's "one default commission rate" sweep replaced the company agent form's `70.0` with the platform default. But `agents.commission_rate` on the company desk is the company's **internal split share** (the form labels it "commission split ratios"), not the platform commission rate — so the sweep silently rewrote the company's share.
- **Fix:** the `70.0` share default is restored; the platform commission rate stays in `FinanceSettings` (`finance.default_commission_rate`, default **5.0%**).
- **Test:** `Batch3CommercialAndDevelopmentRolesTest` — a company agent keeps the submitted split share; the platform default is asserted through `FinanceSettings`.

### P1.17 Money precision was undocumented

- **File:** `app/Services/FinanceSettings.php`, `app/Services/LedgerService.php`, `docs/LEDGER_RECONCILIATION.md`
- **Risk:** the rounding rule was implicit, so a reader could not tell how a half-cent was resolved or whether money was stored as decimals or minor units.
- **Fix:** every money column is `decimal(15,2)` (major units, two decimals); every computation rounds half away from zero to two decimals with `round($x, 2)`, and the journal writer rounds each side before comparing, so a posting either balances exactly or is refused.
- **Test:** `FinanceSettingsAndDoubleEntryLedgerTest` — 66,666.67 ETB yields a 1,333.33 fee, 200.00 VAT, 65,133.34 net, and debits equal credits.

### P1.18 Refund treatment of the fee and VAT was undefined

- **File:** `app/Http/Controllers/FinancialReportingController.php` (`issueRefund`, `proportionalReversal`)
- **Risk:** a refund debited a single `refund_adjustment` amount and credited cash, leaving the platform fee and VAT stranded on the books.
- **Fix:** a refund reverses the platform fee and the VAT on it **in proportion to the refunded fraction** (a full refund reverses both completely), measured cumulatively so several partial refunds always sum to the whole. The journal debits the beneficiary reversal, the fee and the VAT, and credits cash — and balances.
- **Test:** `FinanceSettingsAndDoubleEntryLedgerTest` — a 40% partial refund reverses 80 fee / 12 VAT, the remainder reverses the rest, and every group balances; `RefundIntegrityTest` asserts the audit debit.

### P1.19 Editing a rate was ungoverned

- **File:** `app/Http/Controllers/FinancialReportingController.php` (`updateFinanceRate`), `routes/web.php`, `config/personas.php`, `resources/views/dashboards/finance.blade.php`
- **Risk:** nothing stopped a rate from being changed without authority, without a reason, or with no record of who changed it.
- **Fix:** `POST /finance/settings/rates` requires the new `finance.settings.update` permission, a written reason, and a future-or-today effective date; it writes a new `finance_rates` row and an audit-log entry. It applies to future transactions only — records already written keep their snapshot.
- **Test:** `FinanceSettingsGovernanceTest` — a caller without the permission is refused, a finance officer records a dated rate that is audit-logged, a missing reason and a backdated effective date are refused, and the change only affects new payments.

### P1.20 A failed or refunded payment left the payable advanced, and money creation was not idempotent

- **File:** `app/Models/Payment.php` (`flagPayableRejected`), `app/Services/RefundService.php`, `app/Services/RentPaymentService.php`, `app/Http/Controllers/PaymentController.php` (`store`), migrations `2026_10_16_123000_*`
- **Risk:** a rejected payment only reopened a booking — an offer, reservation or rent schedule stayed looking paid. And a double-submitted payment or a retried refund could be charged twice.
- **Fix:** a rejected payment now returns the offer to `submitted`, the reservation to `pending` and the rent schedule to unpaid, while a refund already rolled them back. Payment creation carries a unique `idempotency_key` and refunds carry one too (the same key reuses the same ledger group), so a retry returns the existing record instead of charging again.
- **Test:** `StayAndRentMoneyIntegrityTest` — refunding rolls the offer/reservation/rent back, rejecting reopens the offer, and a repeated payment or refund with the same idempotency key happens once.

---

## P2 — workflow gaps

| Item | File | Risk | Fix | Test |
|---|---|---|---|---|
| Create-property default status differs per entry point (`draft` / `published` / `available`) | `SellerDashboardController.php:157`, `HostDashboardController.php:140`, `PropertyManagerDashboardController.php:157` | The same action leaves a property in different states depending on who did it | One default (probably `draft`) plus an explicit publish step | A test asserting every create path lands on the same status |
| EXIF/GPS stripping only on the seller path | `SellerDashboardController.php:367` vs host/agent/admin uploads | Photos from other desks leak the photographer's GPS location | Call `ImageSecurityService` on every upload path | A test asserting an uploaded photo has no EXIF |
| No moderation enforcement; auto-created listings publish instantly | `Property.php:93`, `PropertyFormRequest.php:47` | The moderation queue the spec requires (SRS §30) can be bypassed | Default new listings to `pending_review` and send them to the queue | A test asserting a new listing is not publicly visible until moderated |
| Listing quality score has four algorithms | four call sites + `properties.quality_score` accessor | Scores are inconsistent and never stored | One scoring service, stored on write | A test asserting the stored score equals the computed score |
| Fabricated coordinates for unpinned properties | `Property.php:534-592` | The map looks full but those properties cannot be found by area search | Geocode on write or mark the pin as approximate and exclude it from area search | A test asserting a property without a real pin is not returned by area search |
| Inquiries are unlinked side effects | `OrderController.php:74-82`, `ContactController.php:31` | Inquiries cannot be traced to a property or user | Link `property_id`/`user_id` and add a dedicated inquiry form | A test asserting an inquiry is linked to its property |
| Viewing reschedule notifies nobody; offer rejection notifies nobody | `BuyerDashboardController.php:247-293`, `ViewingOfferController.php:438-455` | Buyers and agents miss status changes; the `'rejected'` notification branch is dead | Send the existing notifications on both paths | Tests asserting the notification is queued on reschedule and rejection |
| Cash is accepted by the validator but absent from the checkout modal | `PaymentController.php:35`, `payment-modal.blade.php` | A supported method cannot actually be chosen | Add `cash` to the modal or drop it from the validator | A test asserting the modal offers every accepted method |
| Many notifications are missing (booking confirmed/cancelled, refund, verification, lease, report, ticket, dispute, review, maintenance assignment) | see §7.4 of the guide | Users are not told about decisions that affect them | Send the existing notification classes at those transitions | A `Notification::fake()` test per transition |
| `stays:send-arrival-instructions` is never scheduled | `routes/console.php`, `SendArrivalInstructionsCommand.php` | Arrival instructions are never sent unless run by hand | Add it to the scheduler | A test asserting the command is scheduled |
| Owner/Landlord and Support/Receptionist share desks | `User.php:336`, two dashboard controllers | Two roles have no dedicated workspace | Split the desks or document the merge | A test asserting each role lands on its own desk |
| No self-service role choice on registration; `campaign_officer`/`receptionist` cannot be created via the admin UI | `AuthController.php:202`, `config/personas.php:1239-1260`, `users-desk.blade.php` | Roles cannot be provisioned the way the spec expects | Add the roles to `assignable_roles` and align the dropdown with the validator | A test asserting each assignable role can be created |
| No staff-to-company linking | no `companies` table, no `users.company_id` | Staff cannot be grouped under a company | Add the company model and the foreign key | A test asserting a staff member can be linked to a company |
| **(fixed)** Agent-facing finance desk sections were gated behind finance-only permissions | `resources/views/dashboards/finance.blade.php` (`$canManagePayments`), `FinancialReportingController.php` | The controller scopes an agent's own payments/ledger, but the view hid those sections from agents, so the scoping was effectively dead | The controller now passes `$scopedAgent`; the view gives an agent a read-only **My Payments** section, shows the scoped ledger nav, and hides the finance-only confirm/reject queue and the platform Export CSV / Audit Statement buttons | `tests/Feature/FinanceDeskAgentScopingTest.php` — an agent sees only their own scoped payment rows and no confirm/reject action, while finance keeps the queue and no agent section |
| **(fixed)** The client portal housekeeping/maintenance desk was hidden | `config/personas.php` (client personas) | `maintenance.create`/`maintenance.view` were granted only to `tenant`/`landlord`, so a buyer, traveler or guest account could not open `user.maintenance` | Granted `maintenance.create` + `maintenance.view` to the `buyer`, `traveler` and `guest_account` personas (the `tenant` persona already had them) | `tests/Feature/RolePermissionManagementTest.php` (client grants), `tests/Feature/UnifiedDashboardShellTest.php` (buyer reaches `user.maintenance`), `tests/Feature/AdminUserCustomPermissionAssignmentTest.php` (tenant resolves `maintenance.create`) |
| All 8 notifications are `database` only | `app/Notifications/*` | No email, SMS or push reach a user who is not in the app | Add a mail channel and preferences | A test asserting the mail channel is used where configured |

---

## P3 — polish

| Item | File | Risk | Fix | Test |
|---|---|---|---|---|
| This guide had no table of contents | `docs/HOW_THE_PLATFORM_WORKS.md` | 1700+ lines with ~50 headings are hard to navigate | Add a linked TOC after the subtitle | — (docs) |
| Subtitle uses `###` directly under `#` | same | Heading level skip | Use `##` for the subtitle | — (docs) |
| Numbering is decimal in §4/§7 but not §1–3/§6 | same | Inconsistent outline | One numbering scheme | — (docs) |
| `pending` is outside the inquiry status list | `InquiryController.php:31` vs `OrderController.php:81` | A written status is not selectable | Add `pending` to the list | A test asserting the status round-trips |
| Agent feedback and property reviews use opposite approval rules | `AgentController.php:66-90` vs `PropertyReviewController.php:15-59` | The same "review" behaves differently | Align the two rules | A test asserting consistent behaviour |
| The `receptionist` dropdown entry is rejected by the validator | `users-desk.blade.php:111`, `UserManagementController.php:36` | A user picks a role the form then refuses | Align the dropdown with `assignable_roles` | A test asserting the dropdown only offers creatable roles |

---

## Guide corrections made alongside this backlog

`docs/HOW_THE_PLATFORM_WORKS.md` said things the code does not do. Corrected:

- **Database engine:** said SQLite; the app runs on **MySQL** (`DB_CONNECTION=mysql`, live `Pdo\Mysql`).
- **Framework versions:** now states **Laravel 13** and PHP 8.5 explicitly.
- **Stray backtick** on the admin demo account line.
- **Duplicate appendix row** ("The marketplace search" listed twice).
- **Permission catalogue count:** 355 → **349** (318 persona-granted + 23 catalogued-but-ungranted).
- **`CommissionSplitService` "never called":** wrong — the tier lookup is used; only the split engine is dead.
- **Agent payout "nothing creates one":** wrong — `finance.payouts.store` creates a `PayoutRequest`; the gap is the missing UI and balance check.
- **"No listing CRUD screen":** wrong — seller, moderator and company listing routes exist.
- **"Only the buyer is told" on reschedule:** wrong — that path notifies nobody.
- **Create-property defaults:** also `available`, not just `draft`/`published`.
- **Inquiry creation:** the contact form creates one with no agent at all.
- **Bank reference "never matched":** it is matched once by the gateway webhook lookup; it is bank-statement reconciliation that is missing.
- Rows changed by this pass (callback signature, Telebirr self-verification, refunds, route permission middleware, default-deny, demo accounts) were updated to their new verdicts.
- **"Only credits are written; the ledger is not double-entry":** now false — every money path posts a balanced journal through `LedgerService::post()`, and pre-change rows are flagged `is_legacy` (see `docs/LEDGER_RECONCILIATION.md`).
- **"VAT is included in the fee":** the platform now charges VAT **on top of** the fee and posts it to a `vat_payable` account; `net_amount = amount − fee − VAT`.
- **"`net_amount` = gross − platform fee":** it now also subtracts the VAT on the fee.
- **"Over-refunding possible":** false — the limit is the original amount minus what is already refunded, re-checked under a row lock.
- **"Stay cancellation refund creates no payment or ledger entry":** false — it now writes a refund through the shared `RefundService`.
- **"Landlord rent leaves no financial trace":** false — both rent desks share `RentPaymentService`.
- **"Rental commission is 100% of one month's rent":** false — it uses the dated rule book and the 5% default.
- **"A whole commission engine is unused":** only the split engine is dead; the tier lookup is used.

---

## P1 Batch B acceptance checks (this pass)

The eight follow-up checks on Batch B, each with its test.

| # | Check | What the code now does | Test |
|---|---|---|---|
| 1 | Rate timing | A payment snapshots `platform_fee_rate`/`vat_rate` at **creation**; `settle()` reuses the stored amounts, so verifying after a rate change keeps the creation rate. | `P1MoneyGovernanceAndPayoutsTest` — "a payment created before a rate change uses the rate snapshotted at creation" |
| 2 | Idempotency | The key is **client-supplied** (`Idempotency-Key` header, or `idempotency_key` input). The same key + same payload returns the original result; the same key + a different payload is a **409**. A payload fingerprint is stored on `payments.idempotency_hash` and `refunds.idempotency_hash`. | `P1MoneyGovernanceAndPayoutsTest` — the three idempotency tests |
| 3 | Rollback safety | A refund/rejection only rolls a payable back while it is still in the state the payment set (`under_review` offer, `reserved` reservation, `paid` rent). Anything that moved on gets a durable `finance_review_flags` row instead. | `P1MoneyGovernanceAndPayoutsTest` — "countered flags for review", "under review rolls back" |
| 4 | Refund after payout | A refund after the commission was disbursed posts a **recovery** that reverses the party's earning, so the ledger-derived balance goes negative. The guest-facing service fee (`booking.service_fee`) is the policy's retention; the platform fee (`payment.platform_fee`) is reversed proportionally by the accounting. | `P1MoneyGovernanceAndPayoutsTest` — "a refund after payout makes the agent balance negative"; `StayAndRentMoneyIntegrityTest` — "separates the guest service fee from the platform fee" |
| 5 | VAT | A VAT report (`GET /finance/vat`) shows the `vat_payable` balance by period; `POST /finance/vat/remit` records a remittance (debit `vat_payable`, credit `cash`, with a reference); a VAT-reversing refund issues a **credit note** (SRS §170). | `P1MoneyGovernanceAndPayoutsTest` — the two VAT tests |
| 6 | Legacy ledger | **Chosen: keep legacy single-sided rows out of net revenue and the P&L**, and show legacy and current totals separately in every report. No opening-balance journal is posted and no old row is rewritten. | `LegacyLedgerReconciliationTest` — "keeps legacy rows out of net revenue and shows them separately"; see `docs/LEDGER_RECONCILIATION.md` |
| 7 | Open decisions | Recorded as **options only** below; nothing was decided. | — |
| 8 | Rate changes | A change logs the **old** and **new** rate (`finance.rate_updated`). An optional `finance.rates.require_second_approval` setting holds a change `pending` until a **different** finance user approves it (`finance.settings.approve`); a pending row never moves money. | `P1MoneyGovernanceAndPayoutsTest` — the two rate tests |

### Check 7 — open decisions (options only, no decision taken)

Effects are computed with the shipped defaults (2% VAT on the fee is **not** included
here; commission is shown gross of withholding tax).

**A 100,000 ETB sale, commission only:**

| Default commission | Commission | Agent net after 2% WHT | Platform fee on a 100,000 payment (2%) |
|---|---|---|---|
| **2%** | 2,000.00 ETB | 1,960.00 ETB | 2,000.00 ETB + 300.00 VAT |
| **5%** | 5,000.00 ETB | 4,900.00 ETB | 2,000.00 ETB + 300.00 VAT |

**A 25,000 ETB monthly rent, 12-month lease (300,000 ETB lease value):**

| Rental commission base | Commission | Agent net after 2% WHT |
|---|---|---|
| **One month's rent** (25,000) at 5% | 1,250.00 ETB | 1,225.00 ETB |
| **The lease value** (300,000) at 5% | 15,000.00 ETB | 14,700.00 ETB |

With a 2% default instead, the same two bases give 500.00 / 6,000.00 ETB gross.

**Fee on front-desk cash collections:**

| Front-desk cash fee | On a 25,000 ETB cash rent collection |
|---|---|
| **0%** (current) | Platform fee 0.00, VAT 0.00, landlord nets 25,000.00 ETB |
| **Standard 2% + 15% VAT** | Platform fee 500.00, VAT 75.00, landlord nets 24,425.00 ETB |

Answer these three and the defaults become a one-line change in `FinanceSettings`
plus the seeded `finance_rates` rows.

---

## P1 Batch C — paying people (this pass)

| Item | What was built | Test |
|---|---|---|
| Ledger-derived balance | `AgentBalanceService` computes available = attributed earning debits − reversed earnings − withholding − net cash paid − held, per agent/host/professional. Never a stored column (`financial_ledgers.party_user_id` attributes the legs). | `P1MoneyGovernanceAndPayoutsTest` — recovery + holdback tests |
| Payout request screen | Minimum threshold, configurable schedule (SRS §194: `immediately`, `after_check_in`, `after_check_out`, `weekly`, `monthly`) and a holdback are enforced by `PayoutService`; the finance desk shows the balance, held amount, threshold and schedule. | `P1MoneyGovernanceAndPayoutsTest` — threshold, schedule, holdback |
| Maker-checker | The requester can never approve or disburse their own payout. Finance approves, withholding tax is applied, a voucher number is issued, the bank reference is recorded, and the transfer is marked a **manual disbursement** (no bank integration). | `P1MoneyGovernanceAndPayoutsTest` — maker-checker + disbursement |
| Commission settlement | The finance desk's `commissions.settle` keeps the same rules (WHT, voucher, bank reference, balanced journal) and now attributes the legs to the agent's user. | `FinanceSettingsAndDoubleEntryLedgerTest`, `FinancialReportingAndCommissionSettlementTest` |
| `CommissionSplitService` | **Kept and used.** It already drives co-brokered/referral splits and the tier lookup; removing it would delete a working, tested engine. | `AgentCommissionSplitAndHierarchyTest` |
| Bank reference matching | `GET /finance/reconciliation` lists pending/matched references; `POST /finance/reconciliation/match` matches a reference to a payment and settles it (the payer can never match their own). | `P1MoneyGovernanceAndPayoutsTest` — "a bank reference can be matched" |
| Notifications | `PayoutStatusNotification` fires on requested/approved/rejected/paid. | `P1MoneyGovernanceAndPayoutsTest` — disbursement notification |
| Idempotency / audit / balanced journals / append-only | Payout disbursement is idempotent per `payout:{id}`; every step is audit-logged; journals balance; ledger rows stay append-only. | `P1MoneyGovernanceAndPayoutsTest`, `LedgerService` tests |

