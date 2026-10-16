# How the GTM Real Estate Platform Works
### A plain-English guide, end to end

**Who this is for:** anyone who wants to understand what the system actually does —
new developers, testers, demo presenters, and business owners.

**How to read this:** everything here is taken from the real code and `docs/SRS.md`.
No feature is invented. Where something is only half-built or missing, it is marked
**Partly working** or **Missing**, with the file as proof.

**Words used a lot in this document:**

| Word | Simple meaning |
|---|---|
| Property | The real building, land or room itself. The master record. |
| Listing | The advert for that property, with a price. A property can have several listings. |
| Role | What a person is on the platform (buyer, agent, admin...). |
| Status | Where something is in its life (draft, published, paid...). |
| Ledger | The money diary. It only grows. Nobody edits or deletes rows. |
| ETB | Ethiopian Birr, the money used. |

---

## 1. Quick summary (one page)

### What it is

A single website for **everything to do with property in Ethiopia**. It is not just
a classified-ads board. It carries the whole life of a property: finding it, checking
it is real, advertising it, arranging viewings, negotiating price, taking money,
writing the contract, and collecting reviews afterwards.

It is built with **Laravel 13** (PHP 8.5) and runs on **MySQL** in this setup. It works
on the web, on Android through a REST API, and in three languages: English, Amharic
and Afaan Oromoo. Prices are in **ETB** and it understands both the Ethiopian and
Gregorian calendars.

### Who uses it

Seventeen kinds of people are planned in the specification, and 21 demo accounts exist
in the seeded data. All of them log in through one login page.

```
                    ┌──────────────────────┐
                    │   ONE LOGIN PAGE     │
                    │   /login             │
                    └──────────┬───────────┘
                               │ password is checked
              ┌────────────────┼────────────────┐
              ▼                ▼                ▼
        PUBLIC SIDE      PERSONAL SIDE      STAFF SIDE
        (no login)       (owner/buyer)     (desks)
```

The public side lets anyone browse, search the map, and view properties without an
account. The personal side is for people who own, rent, buy, or host. The staff side is
a set of **desks** — Moderation, Verification, Finance, Support — each with its own
screen and its own list of jobs.

### How it earns money

Three fees, all **configurable in the `site_settings` table** and read through
`app/Services/FinanceSettings.php` (SRS §171/§195). Since the rate-history pass,
a dated `finance_rates` rule book sits in front of the settings: a rate is a fact
with an `effective_from` date, and the rate that produced an amount is snapshotted
on the payment, commission or journal leg. The shipped defaults are:

| Fee | Default | Setting key |
|---|---|---|
| Platform fee | **2% of the amount paid** | `finance.platform_fee_rate` |
| VAT on that fee | **15% of the fee**, charged on top | `finance.vat_rate` |
| Withholding tax on agent commission | **2% of the commission** | `finance.withholding_tax_rate` |
| Default commission rate | **5%** | `finance.default_commission_rate` |

VAT is charged **on top of** the fee and posted to its own `vat_payable` ledger
account, so the platform's fee and the tax it owes are never confused. A
commission can be overridden by a dated rule per agent, property type or company
(`commission_rules`, SRS §99), falling back to the 5% default.

So the platform keeps about 2% of every payment that passes through it, plus the
difference between a website commission and what is paid out to an agent.

**Important truth:** no real money moves. There is no Telebirr, CBE Birr, or Chapa
connection. There is no payment gateway library, no API key, and no bank connection.
Money is **recorded** (a payment row, a ledger entry) — it is never **sent**.

### The five modes

The specification names five marketplace modes (SRS section 8). In the code, each is
really just a value in the `type` column of a property.

```
   BUY              RENT              LEASE             STAY            NEW PROJECTS
   type=sale        type=rent         type=rent         type=stay       developer tables
   one big price    monthly rent      long contract     nightly price   buildings + units
        │                │                 │                │                 │
        ▼                ▼                 ▼                ▼                 ▼
   Sale contract   Lease + rent       Lease            Booking          Unit reservation
   + commission   schedule + Payments  (land etc.)      + check-in      + escrow
        │                │                 │                │                 │
        └────────────────┴────────┬────────┴────────────────┴─────────────────┘
                                  ▼
                       ALL END IN THE SAME PLACE
                 payment → ledger → agreement → review
```

- **Buy** — a one-time price. Ends in a sale contract and an agent commission.
- **Rent** — a monthly amount. Ends in a lease with a rent schedule.
- **Lease** — like rent, but for land and longer arrangements.
- **Stay** — a nightly price, like Airbnb. Ends in a booking with check-in and check-out.
- **New Projects** — developer buildings and units. Ends in a reserved unit.

All five share one engine: photos, documents, verification, offers, payments, ledger,
contracts, and reviews.

---

## 2. Property life, step by step

This is the whole journey of one property, from a blank form to a finished review.

### The big picture

```
  1 CREATE      2 PHOTOS      3 DOCUMENTS     4 VERIFY      5 LISTING
     │             │              │              │             │
  owner or      owner or       owner or       verification   automatic
  agent         agent          agent          officer       (no button)
     │             │              │              │             │
     ▼             ▼              ▼              ▼             ▼
  status=      images in     documents in    level 0 → 5    listing row
  draft/published the table   the table      unverified     appears
                                             → verified
```

```
  6 PUBLISH     7 SEARCH      8 INQUIRY      9 VIEWING      10 OFFER
     │             │              │              │             │
  publish      public map     buyer fills    buyer picks    buyer names
  button       + filters      a form         a date         a price
     │             │              │              │             │
     ▼             ▼              ▼              ▼             ▼
  is_active=    listing is    inquiry row    viewing row    offer row
  true          visible       created        'requested'    'submitted'
```

```
 11 RESERVATION  12 PAYMENT    13 AGREEMENT   14 COMPLETION   15 REVIEW
     │              │              │              │             │
  booking or    buyer pays     PDF contract   property sold/  buyer writes
  unit held     (slip)         is generated   rented         a review
     │              │              │              │             │
     ▼              ▼              ▼              ▼             ▼
  status=       finance       agreement_path lease active   review
  pending       verifies      is saved       / order made   'published'
```

### Step 1 — Create the property

**Who does it:** property owner, landlord, host, agent, company, developer, or admin.
Each has their own "add listing" form, but they all save the same kind of record.

**Which screen or button:**
- Owner/Landlord → Seller desk → "Add Listing" (`POST /seller/listings`)
- Agent → Agent portal → add property (`POST /agent-portal/properties`)
- Company → Company desk → add listing (`POST /company/listings`)
- Host → Host desk → add stay (`POST /host/stays`)
- Developer → publish a unit (`POST` unit publish)
- Admin → Admin dashboard → add property (`POST /dashboard/properties`)

**What the system does:** checks the form (title, price, type, area, lat/lng inside
Ethiopia's bounds, up to 50 images). Then saves a row in the `properties` table.

**Status change:** `properties.status` becomes whatever the form sent. In practice:
`seller.listings.store` allows `draft` or `published`; the agent portal defaults to
`published`; the host form **defaults to `published`**; the plain "my properties"
screen forces `draft`.

Evidence: `app/Http/Requests/PropertyFormRequest.php:36-79`,
`app/Http/Controllers/SellerDashboardController.php:157-185`,
`app/Http/Controllers/HostDashboardController.php:140`.

> **Note:** the very first stage is not guaranteed. Some forms let a property be born
> **published** straight away, skipping the draft step. The SRS (section 30) wants
> everything to start as a draft and be submitted for review.

### Step 2 — Photos and documents

**Who does it:** the owner, from the Seller desk (or the agent/host equivalents).

**Which screen or button:**
- Seller desk → **Media Studio & Photo Gallery** → "Upload" button
  (`POST /seller/properties/{property}/photos`)
- Seller desk → **Property Document Vault** → "Add Legal Document"
  (`POST /seller/properties/{property}/documents`)

**What the system does for photos:** accepts jpg/png/webp up to 10 MB each. Runs the
file through `ImageSecurityService::storeAndStrip`, which re-encodes it using the GD
library. That throws away the EXIF data — including the GPS coordinates of where the
photo was taken — so a seller cannot leak their home address. Then it creates a row in
`property_images` with a sort order.

Evidence: `app/Http/Controllers/SellerDashboardController.php:356-384`,
`app/Services/ImageSecurityService.php:16-56`.

> **Partly working.** Only the Seller desk uses the EXIF stripper. The host's cover
> image and the admin/agent upload paths store the file directly and **keep** the GPS
> data. (`app/Http/Controllers/HostDashboardController.php:143`)

**What the system does for documents:** stores the file in `property_documents` with a
type (title deed, site plan, tax clearance...), an access level, a version number and
`status = pending`.

**Status change:** none on the property yet. The document is `pending`.

### Step 3 — Verification

**Who does it:** a Verification Officer, a Verification Company, or an admin.

**Which screen or button:**
- Verification Officer desk → documents decision
  (`PATCH /moderator/listings/...` — actually route `verification-officer.documents.decision`)
- Verification Officer desk → property trust level
  (`PATCH .../properties/trust-level`)

**What the system does:** the officer marks a document `verified` or `rejected`, and
can raise the property's `verification_level` from 0 (unverified) up to 5 (transaction
ready). A badge is then shown on the listing.

**Status change:**
- `property_documents.status`: `pending` → `verified` / `rejected`
- `properties.verification_level`: 0 → 5
- `properties.verification_status`: `unverified` → `pending` → `verified` / `rejected`

Evidence: `app/Http/Controllers/PropertyVerificationController.php:122-169,174-190`,
`app/Http/Controllers/VerificationOfficerDashboardController.php:98-183`,
`database/migrations/2026_10_03_110857_create_property_documents_and_verification_tables.php`.

**Handy shortcut:** the moment anyone uploads a document, the property is automatically
bumped to **level 3** and marked `pending`. So verification starts the second evidence
arrives.

### Step 4 — The listing appears

**Who does it:** nobody. There is no button.

**What the system does:** when a property is saved, a model event
(`Property::booted()` → `saved`) automatically creates a row in the `listings` table.
It works out the kind of advert from the property type, and copies the price.

**Status change:** `listings.status` becomes `published` unless the property is
`draft` or `archived`, in which case it becomes `draft`.

Evidence: `app/Models/Property.php:61-97,362-370`.

> **Partly working.** There is no screen to edit a listing directly, and the public
> search never reads the `listings` table — it reads `properties`. So the listing is
> mostly a shadow copy used for the quality score and for moderation.

### Step 5 — Publish

**Who does it:** the owner, agent, company or host.

**Which screen or button:** "Publish" / "Unpublish" toggle on the own desk
(`POST /seller/listings/{property}/publish`), or a status dropdown
(`POST .../status/{status}`).

**What the system does:** flips `is_active`, sets `status` to `published` or `draft`,
and copies the same status onto every listing for that property.

**Status change:** `properties.status` ∈ `draft, pending_review, published, paused,
sold, rented`. `is_active` becomes true only for `published`, `sold` and `rented`.

Evidence: `app/Http/Controllers/SellerDashboardController.php:301-351`.

> **Partly working.** Publishing is **instant** for owners, agents, companies and
> hosts. Nothing forces a property through the Moderator first. A seller *can* choose
> `pending_review` from the dropdown, but no code automatically puts new listings there.

### Step 6 — Search and discovery

**Who does it:** anyone, logged in or not.

**Which screen or button:** `/properties` (the marketplace), the map view
`/properties/map`, or category pages like `/properties/villas`.

**What the system does:** only shows properties where `is_active = true` and the status
is not `draft` or `archived`. Then filters by:
- category, type, city, subcity, woreda
- free text — including plain sentences like "3 bedroom villa under 30 million"
  which are parsed by a helper
- **a box drawn on the map** — only properties with real latitude and longitude inside
  the box appear

Evidence: `app/Http/Controllers/PropertyController.php:17-184`,
`app/Models/Property.php:146-151`.

**Handy detail:** if a property has no GPS pin, the system invents a plausible location
from a built-in neighbourhood map (`app/Models/Property.php:534-592`) so it still shows
on the map — but such a property can never be found by map-area search.

### Step 7 — Inquiry

**Who does it:** a buyer, tenant or guest fills in the contact form on the property page.

**Which screen or button:** "Buy / Request" or "Contact" on the property page
(`POST /properties/{property}/orders`, `POST /contact`).

**What the system does:** creates a row in the `inquiries` table. It also sends an
email to the agent.

**Status change:** `inquiries.status` = `new` or `pending`.

**Who answers:** the property owner (`POST /seller/inquiries/{inquiry}/reply`) or the
agent (`POST /agent/inquiries/{inquiry}/reply`).

> **Partly working.** There is no dedicated inquiry form. Inquiries are a **side
> effect** of the order form or the contact form, only when an agent is assigned.
> The inquiry is linked to the agent but **not** to the property or the user, which is
> why some reply buttons refuse to work. Two different first statuses are used
> (`new` and `pending`) and `pending` is not in the allowed list.
> Evidence: `app/Http/Controllers/OrderController.php:61-82`,
> `app/Http/Controllers/ContactController.php:31-38`.

### Step 8 — Viewing

**Who does it:** the buyer requests it; the agent confirms it.

**Which screen or button:** "Schedule Viewing" on the property page
(`POST /properties/{property}/viewings`). The agent then changes the status
(`PUT /viewings/{viewing}/status`).

**What the system does:** saves the date, a time slot (morning, afternoon, evening),
and whether it is in person or a virtual tour. It attaches the property's agent. Then it
sends a database notification to the agent.

**Status change:** `property_viewings.status` = `requested` → `confirmed` →
`rescheduled` → `completed` (or `cancelled` / `no_show`).

Evidence: `app/Http/Controllers/ViewingOfferController.php:27-73,457-485`.

### Step 9 — Offer and negotiation

**Who does it:** the buyer makes an offer; the owner, agent or admin counters or decides.

**Which screen or button:** "Make Formal Offer" on the property page
(`POST /properties/{property}/offers`). Then
`POST /offers/{offer}/counter`, `PUT /offers/{offer}/accept`,
`PUT /offers/{offer}/reject`.

**What the system does:** saves the offered price, the financing type (cash, bank loan,
diaspora remittance, installments) and how long the offer is valid. Every back-and-forth
is saved as a negotiation message.

**Status change:** `property_offers.status` = `submitted` → `countered` →
`under_review` → `accepted` / `rejected` / `expired` / `withdrawn`.

Evidence: `app/Http/Controllers/ViewingOfferController.php:100-260`.

**Handy detail:** the buyer, the seller, the agent and an admin can all counter or
accept. It is a shared desk, not a one-way counter.

### Step 10 — Reservation (short stays)

**Who does it:** a traveler books a stay; the host, receptionist or admin confirms.

**Which screen or button:** on the property page, pick dates and press "Book"
(`POST /properties/{property}/stays/book`).

**What the system does:** the price is worked out **on the server**, not trusted from
the browser — nightly rate × nights + cleaning fee + service fee. Rows are locked while
the check happens, so two people cannot grab the same last night.

**Status change:** `bookings.status` = `pending` (money not yet received) → `confirmed`
→ `checked_in` → `checked_out` / `completed` (or `cancelled`).

Evidence: `app/Http/Controllers/StayBookingController.php:137-200`.

**Auto-cleanup:** a command called `reservations:expire-holds` runs every hour and
cancels stays unpaid for 24 hours, and unit reservations unpaid for 72 hours.
(`app/Console/Commands/ExpireReservationHoldsCommand.php`, `routes/console.php:12`)

### Step 11 — Payment

**Who does it:** the buyer pays; the **Finance Officer** checks it.

**Which screen or button:** the payment modal on the property page
(`POST /payments`). Finance then presses Verify or Reject
(`PUT /payments/{payment}/verify`, `PUT .../reject`).

**What the system does:** saves the amount, the method, and if the bank transfer was
used, the deposit slip. Then it works out the platform fee (2%), the VAT on the fee
(15%) and the net amount.

**Status change:** `payments.status` = `pending` → `completed` / `failed` /
`refunded` / `cancelled`.

**A trust shortcut with a hole in it:** if the buyer says they paid with **telebirr**,
the payment is marked `completed` **immediately**, and the buyer is recorded as the
person who verified their own payment. Every other method waits for the finance desk.

Evidence: `app/Http/Controllers/PaymentController.php:23-96,122-190`.

### Step 12 — Agreement

**Who does it:** the agent, admin or finance, usually when an offer is turned into a
deal.

**Which screen or button:** "Convert to Deal" on an accepted offer
(`POST /offers/{offer}/convert-to-deal`), or the agent's order screen.

**What the system does:** creates either an `Order` (a sale) or a `Lease` (a rent),
generates a **PDF contract** in Amharic using DomPDF, stamps it with a SHA-256
fingerprint so nobody can quietly edit it, and saves the file path.

**Status change:** `orders.status` = `accepted`, `leases.status` = `active`, and the
property becomes `sold` or `rented`.

Evidence: `app/Services/AgreementPdfService.php:17-104`,
`app/Http/Controllers/ViewingOfferController.php:264-433`.

### Step 13 — Completion

**Who does it:** the tenant signs, finance pays out.

**What the system does:** for a rent, the tenant presses "Sign Lease"
(`POST /tenant/leases/sign`) which sets the lease to `active`, records their signature
and creates the monthly rent schedule. For a sale, the property is switched to `sold`
and is taken off the market. An agent commission is created at the same moment.

Evidence: `app/Http/Controllers/TenantDashboardController.php:254-297`,
`app/Http/Controllers/ViewingOfferController.php:312-343,386-422`.

### Step 14 — Review

**Who does it:** the buyer, tenant or guest.

**Which screen or button:** the review form on the property page
(`POST /properties/{property}/reviews`). Agents are rated separately
(`POST /agents/{agent}/feedback`).

**What the system does:** saves a 1-5 star rating plus five sub-scores, and does notice
whether the reviewer actually stayed there.

**Status change:** the review is saved as `published` **straight away**.

> **Two different rules.** Property reviews appear instantly. Agent feedback is held
> back as `is_approved = false` and needs an admin to approve it. That is the opposite
> of what you would expect.

Evidence: `app/Http/Controllers/PropertyReviewController.php:15-59`,
`app/Http/Controllers/AgentController.php:66-90`.

---

## 3. One workflow for each of the 17 roles

The specification plans **17 kinds of user** (SRS section 5). The code actually carries
**22 role strings** — 17 planned plus 5 that were added later
(`verification_company`, `cleaner`, `investor`, `campaign_officer`, `contact`).

### Before the roles: how anyone joins

```
  Visitor
     │  opens /register  (only name, email, password — NO role chooser)
     ▼
  role = 'client'   (hard-coded, AuthController.php:202)
     │
     ├── a command 'roles:consolidate' folds client → buyer
     │
     ▼
  Buyer            ← the only role you can give yourself on the website
```

Every other role is given by an **admin** (`UserManagementController`) or created by a
**company** (which can only create agents). There is no self-service sign-up for staff.

### How permissions work

There are four ways a permission can be decided, checked in this order
(`app/Models/User.php:465-635`):

```
  1. A per-user override: granted = NO  →  blocked, no matter what
  2. A per-user override: granted = YES →  allowed, no matter what
  3. The role's permission list
  4. The persona config file (config/personas.php)
     └── a key nobody catalogued is ALLOWED, except users.*, platform.*,
         roles.*, audit.* which are always denied
```

An **admin** skips all of it and is allowed everything.

> **Worth knowing:** routes use no role middleware at all. Every rule is a hand-written
> `if` inside the controller, which is why some pages disagree with each other.

### Roles that share one desk

Four roles are **merged** in the interface but are still separate rows in the database:

| Roles that look the same to the user | The one desk they share |
|---|---|
| Property Owner, Landlord (and old `seller`) | Seller desk `/dashboard/seller` |
| Customer Support, old `receptionist` | Support desk `/dashboard/customer-support` |
| Company, old `company_admin`, `branch_manager` | Company desk `/company` |
| Traveler, registered `guest` | Guest desk `/dashboard/guest` |

---

### ROLE 1 — Guest (a visitor who has not logged in)

**Goal:** look at properties without an account.

**How they join:** nothing to join. Open the site.

**The right of way:** 6 permissions — browse, search, view, look at the map, look at
photos, search stays (`config/personas.php:31-38`).

**Numbered steps:**

1. **Home page** → the system shows featured properties, latest properties, projects,
   news, agents and testimonials in five sliding carousels.
2. **Marketplace `/properties`** → the system filters to public properties only and
   applies any search box, filter or map box.
3. **Open a property `/properties/{slug}`** → the system shows photos, price, map pin,
   documents it allows, reviews and the agent's contact.
4. **Press "Book", "Offer", "Contact" or the heart icon** → the system refuses and
   sends them to `/login`.
5. **Log in** → they become `client`, and around the same time the
   `roles:consolidate` command quietly renames that to **buyer**.

**Money:** none in, none out.

**Finished state:** still a visitor. Nothing was saved.

**If something goes wrong:** they cannot report, review, or save anything. Support
cannot help because there is no account to look up.

**Who handles it:** nobody — this is the intended behaviour.

---

### ROLE 2 — Buyer (Abebe Desta)

**Goal:** find a property, see it, agree a price, pay, get the contract.

**How they join:** self-register as `client` (then merged to `buyer`), or admin/company
creates them. Demo account: **buyer@realestate.com**.

**Screen:** Buyer Hub `/dashboard/buyer` (`app/Http/Controllers/BuyerDashboardController.php:30-49`).

**Numbered steps:**

1. **Buyer Hub** → the system lists their saved searches, their viewings, their offers,
   their reservations and their documents.
2. **Search `/properties`** → the system filters public listings. They may save the
   search for later.
3. **Property page → "Schedule Viewing"** → the system saves a viewing with status
   `requested` and **notifies the agent** (ViewingStatusNotification).
   *Next person: the Agent.*
4. **Property page → "Make Formal Offer"** → the system saves an offer with status
   `submitted` and **notifies the agent** (OfferStatusNotification).
   *Next person: the Agent or Owner.*
5. **Wait for the answer.** When the agent counters, they get a notification; when they
   accept, the buyer is notified.
6. **Payment modal → pay** → the system records the payment as `pending` and works out
   the 2% platform fee. *Next person: the Finance Officer.*
7. **When finance presses Verify** → the payment becomes `completed`, a ledger credit is
   written, and the buyer gets a "Payment Verified" notification with a receipt link.
8. **The deal is converted** → an order or lease is created, a PDF contract is generated,
   and the property becomes `sold` or `rented`. *Next person: none — job done.*
9. **Review form** → they rate the property and it is published instantly.

**Money:** out. They pay the amount; the platform deducts its 2% fee and the 15%
VAT charged on that fee before the seller is credited with the balance.

**Finished state:** they own (or rent) the property. Their dashboard shows the closed
offer and the contract.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Agent never answers the inquiry | Customer Support desk (ticket) |
| Payment is rejected by finance | Finance Officer; the buyer gets a failed notification and pays again |
| Listing looks like a scam | Moderator, via the report button |
| Cannot cancel a booking | Host or Receptionist |

**Handy detail:** a buyer can **withdraw** their own offer
(`PUT /buyer/offers/{offer}/withdraw`) and cancel a booking, with no money moving.

---

### ROLE 3 — Tenant (Sara Mulugeta)

**Goal:** rent a place, sign the lease, pay rent on time, get repairs done.

**How they join:** admin/company creates them, or the mobile API allows `tenant` on
self sign-up. Demo account: **tenant@realestate.com**.

**Screen:** Tenant Hub `/dashboard/tenant` (`app/Http/Controllers/TenantDashboardController.php:25-43`).

**Numbered steps:**

1. **Tenant Hub** → the system shows their lease, the rent schedule, applications and
   maintenance requests.
2. **Apply for a rental** → the system saves an application.
   *Next person: the Landlord, Property Manager or Agent — only admins and agents may
   actually create a lease (`User.php:373-376`).*
3. **Lease is created** → the tenant signs it (`POST /tenant/leases/sign`). The lease
   becomes `active`, their signature is stored, and the 12 monthly rent rows appear.
   *Next person: the Landlord.*
4. **Every day at midnight** the command `rents:process-schedules` runs. Three days
   before rent is due it sends an "Upcoming Rent Due" notification; after the grace
   period (default 5 days) it adds the late fee and sends an "Overdue Rent" notice.
5. **Rent period arrives** → the tenant pays from the client portal
   (`POST /rent-schedules/{schedule}/record-payment`). The schedule becomes `paid`.
   *Next person: the Finance Officer, who verifies it.*
6. **Something breaks** → they raise a maintenance request (`POST /tenant/maintenance`).
   *Next person: the Property Manager or Landlord, who assigns a contractor.*

**Money:** out. Monthly rent. Note that rent paid through the tenant portal is charged
the full **2% platform fee**, while rent recorded by the finance desk is charged **0%** —
the same event, two different fees.

**Finished state:** an `active` lease with a paid schedule and a signed agreement.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Rent is overdue | System flags it `overdue`; the Landlord escalates |
| Deposit is withheld | the Landlord deducts it (`POST /seller/leases/{id}/deposits/deduct`) |
| Repair not done | Property Manager, then Customer Support |
| Dispute | Customer Support desk |

**Handy detail:** a tenant's lease cannot be created by a **Finance Officer** — the
code blocks it on purpose (`app/Http/Controllers/LeaseManagementController.php:34`).

---

### ROLE 4 — Guest / Traveler (Dawit Tadesse) — the STAY mode

**Goal:** book a room for a few nights, pay, check in, check out, review the host.

**How they join:** seeded as `traveler`, or created by an admin. The mobile API also
allows self sign-up as `guest`. Demo account: **traveler@realestate.com**.

**Screen:** Guest Hub `/dashboard/guest`
(`app/Http/Controllers/GuestDashboardController.php:24-33`).

**Numbered steps:**

1. **Guest Hub** → the system lists their bookings, their stay reviews and their saved
   properties.
2. **Search stays `/stays`** → the system filters to `type=stay` listings.
3. **Property page → pick dates and guests → "Book"** → the system recalculates the price
   itself (nightly × nights + cleaning + service), locks the row so no one else can take
   the same night, saves a booking with status `pending` and `payment_status = unpaid`,
   and **notifies every receptionist and admin** (GuestBookingNotification).
   *Next person: the Host, Receptionist and Finance.*
4. **Pay** → the payment is recorded. *Next person: the Finance Officer.*
5. **Host accepts** (`POST /host/bookings/{booking}/accept`) → `confirmed`.
6. **Arrival day** → the Receptionist checks them in (`POST /receptionist/check-in`).
   The booking becomes `checked_in` and the guest is notified.
7. **Departure** → check-out (`POST /receptionist/check-out`). Status becomes `completed`.
8. **Review the host** → the review is published.

**Money:** out. Nightly rate + cleaning fee + a service fee (the host sets it; if left
empty the system uses 5% of the room price).

**Finished state:** a `completed` booking and a published review.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Unpaid booking | The hourly command cancels it after 24 hours |
| Wants to cancel | They can cancel; the refund is worked out by a fixed rule (see Money flow) |
| Double booked | The row lock prevents it; if it slips, the Receptionist fixes it |
| Complaint about the room | Customer Support desk (dispute) |

**Handy detail:** the cancellation refund is **now real**. A cancellation that owes
money advances the booking's payment and posts a balanced double-entry journal
through the one refund writer (`RefundService`), so it shows up in the finance
ledger. The policy retains the whole service fee, so the refund comes out of the
accommodation revenue. (`app/Http/Controllers/GuestDashboardController.php`)

---

### ROLE 5 — Property Owner (Tigist Haile)

**Goal:** advertise a property, answer buyers, sell or rent it.

**How they join:** admin creates them, or the mobile API allows `seller`. Seeded as
`property_owner`; the old `seller` role is merged into it.
Demo account: **owner@realestate.com**.

**Screen:** the **Seller desk** `/dashboard/seller` — shared with Landlords
(`app/Http/Controllers/SellerDashboardController.php:34-44`).

**Numbered steps:**

1. **Seller desk → "Add Listing"** → a property is saved. *Next person: nobody, they own it.*
2. **Media Studio** → upload photos. EXIF/GPS is stripped. The listing quality score
   climbs (photos, description, GPS pin, documents each add points).
3. **Document Vault** → upload the title deed. The property jumps to verification
   **level 3** and is marked `pending`. *Next person: the Verification Officer.*
4. **Verification Officer verifies the deed** → level can rise to 5, and a badge appears.
5. **"Publish"** → `is_active = true`, status `published`, the listing is copied to
   `published`. The property now appears in search.
6. **A buyer inquires** → the owner answers (`POST /seller/inquiries/{id}/reply`).
7. **A buyer books a viewing** → the owner sees it in the desk and confirms it.
8. **An offer arrives** → the owner counters or accepts.
9. **"Convert to Deal"** → a contract PDF is generated, the property becomes `sold` or
   `rented`, and an agent commission is created. *Next person: Finance.*
10. **The money is confirmed** → the owner sees the sale closed.

**Money:** in — the sale price or the rent, minus any agent commission.

**Finished state:** the property is `sold` or `rented`, off the market, with a signed
agreement on file.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Upload says "This action is unauthorized" | see the note below — it is a permission clash |
| Property reported as fake | Moderator |
| Buyer vanishes | Agent, then Customer Support |
| Wants to take it off the market | they pause it themselves (`status = paused`) |

**Handy detail:** the desk deliberately lists properties the owner only *manages* (they
hold a lease, or uploaded a document), not just ones they own. The permission rule was
recently widened to match, so the upload button now works on those too.

---

### ROLE 6 — Host (Girma Assefa) — the STAY supply side

**Goal:** list rooms, set prices, take bookings, keep the rooms clean.

**How they join:** admin creates them, or an existing user is treated as a host once
they own a stay property. Demo account: **host@realestate.com**.

**Screen:** Host desk `/dashboard/host`
(`app/Http/Controllers/HostDashboardController.php:28-48`).

**Numbered steps:**

1. **Host desk → "Add Stay"** → the property is saved. **Note it is published
   immediately** — the host form has no draft step.
2. **Set pricing, house rules, availability** → the system stores nightly rate, cleaning
   fee, service fee, minimum stay, and the calendar.
3. **Block dates** → the host marks nights as unavailable or as an external booking
   (Airbnb, Booking.com) so the calendar never double-books.
4. **A booking arrives** → the host is notified. They **accept** or **reject** it.
5. **They dispatch cleaning** (`POST /host/cleaning`). *Next person: the Cleaner.*
6. **Guest checks in and out** → the host (or the receptionist) updates the status.
7. **The guest leaves a review** → the host replies (`POST /host/reviews/{id}/reply`).
8. **Monthly** → the host sees what they are owed.

**Money:** in — the nightly rate. Out — a service fee and cleaning costs.

**Finished state:** a `completed` booking and a clean turnover.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Guest damages the room | Host raises a dispute → Customer Support |
| Room not cleaned | Host chases the Cleaner, then the Property Manager |
| Wants their money | **Fixed** — the finance desk's payouts queue shows the host's ledger-derived balance and a request form (minimum threshold, schedule, holdback); finance approves and disburses |

**Handy detail:** host cover images skip the EXIF stripper, so GPS data from the host's
own phone can stay inside the uploaded photo.

---

### ROLE 7 — Landlord (Meseret Kebede)

**Goal:** rent out property, keep tenants, collect rent every month, handle deposits.

**How they join:** admin creates them. Demo account: **landlord@realestate.com**.

**Screen:** **the same Seller desk** (`User.php:334` sends `landlord` to
`seller.dashboard`). No separate screen exists.

**Numbered steps:**

1. **Seller desk → "Add Listing"** → the property is saved as `type=rent`.
2. **Publish** → it appears in search.
3. **A tenant applies** → the landlord reviews it.
4. **Create the lease** — *but a landlord cannot do this themselves*. Only an admin or
   an agent may press the button. The code returns "Permission denied: Cannot create
   leases" to a landlord who tries (`SellerDashboardController.php:548`).
5. **The tenant signs** → the lease is `active` and 12 rent rows are created.
6. **Rent is due** → the daily command reminds the tenant automatically.
7. **Rent arrives** → the landlord records it (`POST /seller/rent-schedules/{id}/pay`) or
   the tenant pays through the portal.
8. **A late tenant** → the landlord marks the schedule late (`seller.rent.mark-late`).
9. **Move-out** → they deduct or return the deposit
   (`POST /seller/leases/{id}/deposits/deduct` / `/return`).

**Money:** in — monthly rent, minus any commission.

**Finished state:** an `active` lease, a paid schedule, and the deposit settled.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Tenant stops paying | Landlord escalates → Customer Support dispute |
| Repair needed | Property Manager or a contractor |
| Cannot create a lease | Admin (or ask an agent) — this is a real blocker |

**Handy detail:** the landlord's own "record rent" button now writes a real trace:
it routes through the same rent writer the tenant desk uses (`RentPaymentService`),
creating a verified payment row and a balanced double-entry journal, so an offline
collection shows up in the finance reports exactly like an online one.
Evidence: `app/Http/Controllers/SellerDashboardController.php`, `app/Services/RentPaymentService.php`.

---

### ROLE 8 — Agent / Broker (Tadesse Bekele)

**Goal:** sell other people's property, run a client list, earn commission.

**How they join:** an admin or a company creates them. The system automatically builds a
matching row in the `agents` table. **There is no self-service route to become an agent.**
Demo account: **agent@realestate.com**.

**Screen:** Agent portal `/agent-portal`
(`app/Http/Middleware/EnsureUserIsAgent.php` plus `AgentPortalController.php:36`).

**Numbered steps:**

1. **Agent portal** → the system shows their properties, clients, leads, viewings,
   offers, campaigns and commissions.
2. **Add a property** → saved as `published`.
3. **A buyer inquires** → the agent is emailed **immediately** (OrderReceived /
   InquiryReceived) and sees it in their CRM. They reply and move the lead through
   stages: new → contacted → viewing → negotiating → won/lost.
4. **A viewing is requested** → the agent is notified, then confirms or reschedules it.
5. **An offer arrives** → the agent is notified, then counters, accepts or rejects.
6. **They accept the offer and convert it to a deal** → an order or lease is made, the
   contract PDF is generated, and a **commission** row is created at the agent's own
   rate (or the one configured default). All finance officers and admins are notified.
7. **They create a marketing campaign** (`POST /campaigns`). *Next person: the Campaign
   Officer, who approves or rejects it.*
8. **Every month** → they check the Commissions desk for what they are owed.

**Money:** in — commission (the agent's own rate, or the one configured default —
`finance.default_commission_rate`, 5% out of the box). Out — nothing.

**Finished state:** a closed deal and a commission waiting to be settled.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Their agent profile is switched off | Their login is **blocked** (`AuthController.php:71-79`) — only an admin can fix it |
| Cannot get paid | **Fixed** — the payouts queue has a request form and an approve/disburse flow |
| Client complaint | Customer Support |
| Listing reported | Moderator |

**Handy detail:** `CommissionSplitService` holds a multi-tier commission engine (junior
60% / senior 70% / principal 80% splits, co-brokered and referral cuts). Only its **tier
lookup** (`getTierDetails`) is called in production — `Agent`, `UserManagementController`
and `WorkerManagementController` use it for default splits. The **split engine**
(`calculateSplits` / `recordDealCommission`) is **not called by any production code**; the
deal-close flow creates a single-agent `Commission` for the full pool. Wiring the engine in
would change how much an agent is paid (a tier split instead of the full pool), which is a
commission-policy decision still open with the product owner.

---

### ROLE 9 — Real Estate Company (Ethio Prime Properties)

**Goal:** run a branch office: manage agents, listings and clients under one brand.

**How they join:** admin creates them. Demo account: **company@realestate.com**.

**Screen:** Company desk `/company`
(`app/Http/Controllers/CompanyDashboardController.php:42`).

**Numbered steps:**

1. **Company desk** → the system shows branches, agents, listings, clients and reports.
2. **Create a branch, then an agent inside it** (`POST /company/agents`) → a user with
   role `agent` plus an `agents` row pointing at that branch. *Next person: the Agent.*
3. **Add or approve listings** (`POST /company/listings`).
4. **Reassign clients between agents**.
5. **Set the subscription tier** (`POST /company/subscription`). *Next person: Finance
   Officer, who bills it.*
6. **View reports** → listings by branch, agents, performance.

**Money:** in — sales through their own listings, plus billed subscriptions.
Out — commission to their agents.

**Finished state:** a running office with staff and listings.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Their agency licence is questioned | Verification Officer (`verification.companies.decision`) |
| Duplicate listings in their area | Moderator (merge desk) |
| Subscription lapses | Finance Officer |
| An admin tries to open their desk | Blocked by design (`CompanyDashboardController.php:42`) |

**Handy detail:** there is **no `companies` table and no `company_id` on users**. The
"company" is really just one user account, and its staff are agent rows. So a company
cannot list "all my staff" in one query.

---

### ROLE 10 — Developer (Nuradin Dalu)

**Goal:** sell a whole building, unit by unit, with staged payments.

**How they join:** admin creates them. Demo account: **developer@realestate.com**.

**Screen:** Developer portal `/developer-portal`
(`app/Http/Controllers/DeveloperPortalController.php:29`).

**Numbered steps:**

1. **Developer portal** → the system shows projects, buildings, units, construction
   milestones, payment plans and buyers.
2. **Create a project, then a building, then auto-generate the units** — one row per
   apartment (`POST /developer/buildings/{id}/generate-units`).
3. **Set pricing and a payment plan** → e.g. 30% now, the rest on milestones.
4. **Publish a unit as a normal marketplace listing** → a property is created with
   status `published`, and the normal listing hook fires.
5. **Record construction milestones** with photos → buyers see progress.
6. **A buyer reserves a unit** (`POST /developments/{unit}/reserve-unit`) → a
   `UnitReservation` is created with status `reserved`. *Next person: Finance.*
7. **Buyer pays the deposit** → the payment settles and money is credited to the ledger
   under `developer_deposit`.
8. **Ask for an early release of the funds against a milestone**
   (`POST /projects/{id}/escrow-disbursement`) → a `DeveloperEscrowDisbursement` is
   created with status `pending`. *Next person: whoever approves.*
9. **Approve it** (`POST /escrow-disbursements/{id}/approve`) → the disbursement is
   marked `disbursed`.

**Money:** in — deposits and milestone payments. Out — construction costs.

**Finished state:** units sold, milestones built, deposits disbursed.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Unit unpaid | The hourly command expires the hold after 72 hours |
| Licence questioned | Verification Officer (`verification.developers.decision`) |
| Escrow release refused | Finance / admin |

**Handy detail:** both escrow endpoints have **no authorisation check at all** — any
logged-in user can request or approve a developer disbursement.
(`app/Http/Controllers/CorporateTenancyController.php:125,152`)

---

### ROLE 11 — Property Manager (Bekele Wondimu)

**Goal:** run buildings day to day: tenants, bookings, repairs, cleaning.

**How they join:** admin creates them. Demo account: **propertymanager@realestate.com**.

**Screen:** Property Manager desk `/dashboard/property-manager`
(`app/Http/Controllers/PropertyManagerDashboardController.php:30-38`).

**Numbered steps:**

1. **PM desk** → the system shows buildings, units, tenants, guests, bookings,
   maintenance and housekeeping.
2. **Create a building, then units** (`POST /property_manager/buildings`,
   `/units/{id}/...`).
3. **A ticket comes in from a tenant** → the PM assigns a contractor
   (`POST /property_manager/maintenance/{id}/assign`).
   *Next person: the service provider.*
4. **Dispatch cleaning** (`POST /property_manager/cleaning/{id}/dispatch`).
   *Next person: the Cleaner.*
5. **Manage rent** → adjust or escalate an amount (`property_manager.rent.adjust`,
   `.escalate`).
6. **Run inspections** and record the result.
7. **Close the ticket** when work is done.

**Money:** passes through. They do not get paid by the system.

**Finished state:** buildings occupied, tickets closed, cleaning done.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Contractor no-show | PM reassigns, then escalates to Customer Support |
| Tenant refuses to pay | escalated notice, then a dispute |
| Unit unresolved | PM handles it |

**Handy detail:** an **admin is blocked** from opening an individual PM's desk — the
code says "Administrators cannot open individual property manager dashboards".

---

### ROLE 12 — Professional / Service Provider (Mekonnen Gebre)

**Goal:** get hired for jobs — lawyer, surveyor, architect, valuer, inspector —
then quote and complete them.

**How they join:** admin creates them (role `service_provider`, old `professional`).
Demo account: **service@realestate.com**.

**Screen:** Professional desk `/dashboard/professional`
(`app/Http/Controllers/ProfessionalDashboardController.php:17-30`).

**Numbered steps:**

1. **Professional desk** → the system lists incoming job requests.
2. **A guest or PM raises a service request** (`GuestServiceRequest` from the
   housekeeping desk, or a maintenance ticket assigned to them).
3. **They accept or decline** (`PATCH /professional/requests/{id}/status`).
4. **They send a quote** (`professional.requests.quote`).
5. **Book an appointment**.
6. **Do the work and file a report** (`professional.requests.report`).
7. **Ask for payment** → an invoice goes to the client, and finance sees it.

**Money:** in — their fee for each job.

**Finished state:** jobs completed, reports filed, invoices paid.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Client disputes the invoice | Customer Support |
| Nobody is sending them jobs | **structural gap** — jobs only arrive if a PM or host manually assigns them |
| Licence questioned | Verification Officer |

**Handy detail:** this desk has only **17 permissions** — the thinnest of all roles.

---

### ROLE 13 — Moderator (Hana Tekle)

**Goal:** keep the marketplace honest: review listings, handle reports, merge duplicates,
hide bad content.

**How they join:** admin creates them. Demo account: **moderator@realestate.com**.

**Screen:** Moderator desk `/dashboard/moderator`
(`app/Http/Controllers/ModeratorDashboardController.php:35-36,95-97,139-141`).

**Numbered steps:**

1. **Moderator desk** → the system lists listings to review, user reports, reviews and
   suspicious ads.
2. **A buyer reports a listing** (`POST /properties/{id}/report`) → admins, companies
   and the listing's agent are all notified (ClientReportSubmittedNotification).
   *This is the main way work reaches the moderator.*
3. **They decide on the listing** (`PATCH /moderator/listings/{id}`) → set it to
   `published`, `approved`, `rejected`, `paused` or `draft`. **This also changes the
   property itself** — status, and whether it is active.
4. **They decide on a report** → warn the user, flag fraud, or unpublish the listing.
   All three act on the property at once.
5. **They merge duplicates** (`POST /moderator/listings/merge`) → every inquiry, offer,
   order and report is moved to the surviving listing, and the duplicate is archived and
   renamed "[Merged into #N]". This is the Fraud Detection feature from SRS section 31.
6. **They hide or delete a review**.
7. **Warned users and fraud flags** → passed to Customer Support or the admin.

**Money:** none.

**Finished state:** the listing is published, paused, rejected or merged, and the
reporter has been dealt with.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Serious fraud | Admin |
| Legal problem with a document | Verification Officer |
| The reporter keeps complaining | Customer Support |

**Handy detail:** nothing **automatically** sends a listing to the moderation queue. A
seller must choose `pending_review` by hand. And the two moderation controllers use
different words for the same decision — one maps "suspend" to `paused`, the other accepts
`paused` directly.

---

### ROLE 14 — Verification Officer (Selam Ayalew)

**Goal:** decide what is real — identities, companies, developers and title deeds.

**How they join:** admin creates them. Demo account: **verification@realestate.com**.

**Screen:** Verification desk `/dashboard/verification-officer`
(`app/Http/Controllers/VerificationOfficerDashboardController.php:34,98-100,183-184`).

**Numbered steps:**

1. **Verification desk** → the system lists documents awaiting review and verification
   jobs.
2. **An owner uploads a title deed** → the property jumps to level 3 and `pending`, and
   the document is queued. *This is how work arrives.*
3. **Review the document** → mark it `verified` or `rejected`
   (`PATCH /verification-officer/documents/{id}`).
4. **Raise the trust level** → 0 to 5 (`.../properties/{id}/trust-level`). At level 4 or
   5 the property is stamped verified and a badge is shown to the public.
5. **Verify a company's licence** (`.../companies/{id}/decision`) → the company is
   approved or rejected.
6. **Verify a developer** the same way.
7. **Verified properties feed back into the marketplace** → a badge appears and buyers
   can trust the listing.

**Money:** a verification company may be billed through `verification_payments`.

**Finished state:** the property carries a verification badge and the documents are
`verified`.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Document looks forged | Officer rejects it and can flag fraud → Admin |
| Owner re-uploads | the document gets version 2, version 3... and the old one stays |
| Owner argues | Customer Support, then Admin |

**Handy detail:** **nobody is told** when a document is verified or rejected. There is no
notification on any of the verification decisions — the officer's work is invisible to
the person waiting for it.

---

### ROLE 15 — Finance Officer (Rahel Demisse)

**Goal:** watch every birr: confirm payments, keep the ledger, pay agents, refund,
invoice, and reconcile the front desk.

**How they join:** admin creates them. Demo account: **finance@realestate.com**.

**Screen:** Finance desk `/finance`
(`app/Http/Controllers/FinancialReportingController.php:33-58`).

**Numbered steps:**

1. **Finance desk** → nine sections: payments to confirm, leases & rent, ledger,
   refunds, invoices, payouts, subscriptions, commissions, shift reconciliation.
2. **A payment is made** → it lands in "Payments to Confirm" as `pending`.
3. **Look at the deposit slip**, then press **Verify** → status `completed`, the ledger
   is written, the payer and the property's agent are notified, and the thing that was
   bought (booking, offer, unit, rent) moves forward automatically.
4. **Or press Reject** → `failed`, and the payer is told why. The thing that was bought
   is put back to the state the payment set: a booking, an `under_review` offer, a
   `reserved` reservation or a `paid` rent schedule. If it has already moved on (an
   offer was countered or accepted), the old state is **not** forced — a
   `finance_review_flags` row is raised for the desk instead.
5. **A deal closes** → a commission appears, with a **Settle (manual)** button on the
   finance desk. Settling applies the 2% withholding tax, issues a voucher number,
   records the bank reference, posts a balanced `commission:{id}` journal (gross
   debit; net cash + withholding credit) attributed to the agent, and marks the
   transfer a manual disbursement.
6. **Issue a refund** → the payment becomes `refunded` or `partially_refunded`, a
   `refunds` row is written, the platform fee and the VAT on it are reversed
   proportionally, a **credit note** is issued for any VAT reversal, and the reason is
   stored. If the commission was already paid out, a **recovery** reverses the party's
   earning so their ledger-derived balance goes negative.
7. **Pay people** (SRS §194) → the payouts queue shows each party's **ledger-derived
   available balance** (never a stored number). Agents, hosts and professional service
   providers all use the same balance and payout services: each sees only their own
   balance and payout rows (`payouts.view`), with their bank account number **masked** to
   the last four digits; only finance sees the full book and full bank details. A request
   (`payouts.request`) must clear the minimum threshold, the configurable schedule
   (`immediately`, `after check-in`, `after checkout`, `weekly`, `monthly`) and the
   holdback. Finance approves (maker-checker: the requester can never approve their own
   payout), then disburses: withholding tax applied, voucher issued, bank reference
   recorded, marked a manual disbursement. A party whose balance is negative appears on
   finance's **"people who owe the platform"** list; a later earning nets against the debt.
   The balance check runs inside a transaction that locks the party row, so two requests
   cannot together exceed the balance.
8. **Export the ledger to CSV**, print an audit statement, or open the **VAT payable
   report** (`/finance/vat`) and record a **remittance** to the tax authority (debit
   `vat_payable`, credit `cash`, with the authority's reference).
9. **Match bank references** (`/finance/reconciliation`) → a bank reference is matched to
   its pending payment(s) and settles them. One transfer may cover several payments that
   share the reference (all are settled together); a reference that was already matched is
   rejected; an amount that does not equal the payments' total is **flagged for review,
   never auto-matched**.
10. **Verify a front-desk shift** → the receptionist's cash is reconciled.
11. **Every day at midnight** → the rent command creates overdue rows and late fees.

**Money:** everything passes through here. In — fees and payments. Out — refunds,
commissions, payouts, and VAT remittances.

**Finished state:** every payment is `completed` or `failed`, every commission settled,
every payout approved-then-disbursed, the VAT remitted, the ledger balanced and
exported.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Payment cannot be verified (no slip) | Finance rejects it, payer pays again |
| Over-refunding possible | **Fixed** — the limit is the original amount minus what is already refunded, re-checked under a row lock |
| A refund arrives after the payout | **Fixed** — a recovery makes the party's balance negative; the next payout must clear it |
| Agent wants their money | **Fixed** — the payouts queue has a request form and an approve/disburse flow |
| Support escalates a payment issue | the Finance desk |

**Handy detail:** the Finance desk screen says "Double-Entry Ledgers" and promises
"auditable double-entry accounting records" — and now it is true. Every money path
posts a **balanced** journal through `LedgerService::post()`, which refuses an
unbalanced posting and stamps each event with a `transaction_group`. A payment
debits `cash` and credits the beneficiary net, the platform fee, and the VAT
payable. Rows written before this change are flagged `is_legacy`; they are **never
folded into net revenue or the P&L** — the reports show legacy and current totals
separately (see `docs/LEDGER_RECONCILIATION.md`). A change to a rate is logged with
its old and new value, and can require a second, different approver.

---

### ROLE 16 — Customer Support (Yonas Tesfaye)

**Goal:** solve people's problems: tickets, payment issues, disputes, account help.

**How they join:** admin creates them. The old `receptionist` role is merged into this
one. Demo account: **support@realestate.com**.

**Screen:** Support desk `/dashboard/customer-support`
(`app/Http/Controllers/CustomerSupportDashboardController.php:30-31`).

**Numbered steps:**

1. **Support desk** → the system lists tickets, payment issues and disputes.
2. **A user opens a ticket** (`POST /customer-support/tickets`) → *no notification is
   sent to anyone.*
3. **Reply, assign to a colleague, escalate, or close** (`.../reply`, `.../assign`,
   `.../escalate`, `.../close`).
4. **A payment problem arrives** → they view it and escalate it to the Finance Officer
   (`payment_issues.escalate`).
5. **A dispute is opened** (`POST /disputes`) → they move it through stages.
6. **Help with an account** (`users.assist`) → e.g. re-send a verification link.
7. **Technical problems** → escalate to engineering/admin.

**Money:** none directly, except that payment escalations lead to refunds.

**Finished state:** tickets closed, disputes resolved, escalations sent onward.

**If something goes wrong:**
| Problem | Who handles it |
|---|---|
| Nobody replies to a ticket | **no notification exists** — the ticket just sits in the list |
| Suspected fraud | Moderator, then Admin |
| Account must be suspended | Admin only (`dashboard.users.status`) |

**Handy detail:** the old **Receptionist** desk still exists with its own 15 routes
(`/dashboard/receptionist`) and its own seeded demo account was **removed** — yet the
login page still offers a `receptionist@realestate.com` quick-fill button that cannot
log in. And the admin role dropdown offers `receptionist` but the validator rejects it.

---

### ROLE 17 — Administrator

**Goal:** run the whole platform: users, roles, properties, settings, everything.

**How they join:** only another admin (or the seeder) can create one. There is no
self-service path. Demo account: **admin@realestate.com**.

**Screen:** Admin dashboard `/dashboard`
(`app/Http/Controllers/WorkerManagementController.php:89-91`).

**Numbered steps:**

1. **Admin dashboard** → users, roles & permissions, properties, orders, commissions,
   agents, branches, posts, categories, settings, testimonials, job applications,
   analytics.
2. **Create a user and give them a role** (`POST /dashboard/users`) → the role is checked
   against the 20 allowed roles. *This is how every staff member is born.*
3. **Grant an extra permission to one person** → this overrides their role completely.
4. **Verify a property, feature it, suspend it, archive it or merge it**.
5. **Edit the site settings, categories and testimonials**.
6. **Approve agent feedback** left by clients (`dashboard.testimonials.toggleApproval`).
7. **Look at the analytics** — listings, money, users.
8. **Every important action leaves an audit log row.**

**Money:** they can view and adjust everything; the ledger rows are locked so even they
cannot edit or delete a financial entry (`app/Models/FinancialLedger.php:39-45`).

**Finished state:** users in place, permissions correct, content moderated, papers in
order.

**If something goes wrong:** the admin is the last stop.

**Boundaries (deliberate):** an admin is **blocked** from every personal desk — buyer,
tenant, guest, seller, host, agent, professional, property manager, client — and from
the company desk. They also **cannot impersonate** anyone
(`UserManagementController.php:437-440`).

---

### The five roles added beyond the plan

| Role | Demo account | What it does |
|---|---|---|
| Verification Company | verifycompany@realestate.com | Takes paid verification jobs, files reports, bills for them |
| Cleaner / Housekeeping | cleaner@realestate.com | Sees only cleaning tasks. 4 permissions. One route |
| Investor | investor@realestate.com | Read-only portfolio and yield numbers. 5 permissions |
| Campaign Officer | campaign@realestate.com | Approves or rejects agents' marketing campaigns |
| Contact | none | A demo-data placeholder with no permissions and no desk |

---

## 4. Money flow

### 4.1 The main line: payment → ledger → fee → payout

```
    A buyer pays
   (slip upload, telebirr, or cash)
          │
          ▼
  ┌──────────────────┐
  │ payments row     │  status = pending
  │ amount = 100,000 │  (or completed instantly if telebirr)
  └────────┬─────────┘
           │  Finance presses Verify   (PaymentController::verify)
           ▼
  ┌──────────────────────────────────────────────┐
  │ FEES ARE CALCULATED (hard-coded)             │
  │   platform fee = amount × 2%                 │
  │   VAT          = fee     × 15%               │
  │   net          = amount  − fee  (tax not     │
  │                          subtracted here!)   │
  └────────┬─────────────────────────────────────┘
           │  settle()
           ▼
  ┌──────────────────────────────────────────────┐
  │ financial_ledgers  (append-only, immutable)  │
  │   debit   cash        → cash / bank received │
  │   credit  net amount  → booking/rental/      │
  │                          deposit revenue     │
  │   credit  platform_fee→ platform_fee         │
  │   credit  vat_payable → VAT owed on the fee  │
  └────────┬─────────────────────────────────────┘
           │  syncPayableAfterSettlement()
           ▼
  the thing bought moves on:
     Booking       → paid
     PropertyOffer → under_review
     UnitReservation → reserved
     RentSchedule → paid
```

**Key numbers (settings-backed, with a dated `finance_rates` rule book in front):**

| Item | Value | Setting key (`app/Services/FinanceSettings.php`) |
|---|---|---|
| Platform fee | 2% of amount (default) | `finance.platform_fee_rate` |
| VAT on the fee | 15% (default) | `finance.vat_rate` |
| Withholding tax on commission | 2% (default) | `finance.withholding_tax_rate` |
| Default commission rate | 5% (default) | `finance.default_commission_rate` |
| Fallback service fee for a stay | 5% of room price | `StayBookingController.php:149-155` |

> **`net_amount` has one formula.** `FinanceSettings::netAmount()` (gross minus the
> platform fee and the VAT charged on that fee) is the only place it is computed, and
> both the controller and the model call it. The rate that produced each of those
> amounts is snapshotted on the record and is never recalculated.

### 4.2 Refunds

```
   Finance desk → Refunds section
              │  amount + reason
              ▼
   refund_amount >= payment.amount ?
        │yes                     │no
        ▼                        ▼
   status = refunded       status = partially_refunded
        └───────────┬────────────┘
                    ▼
        balanced ledger journal:
          DEBIT  refund_adjustment (beneficiary reversal)
          DEBIT  platform_fee      (fee reversed in proportion)
          DEBIT  vat_payable       (VAT reversed in proportion)
          CREDIT cash              (refund paid out)
                    ▼
        the payable is reversed / flagged
                    ▼
        AuditLog 'payment.refund_issued'
```

**What happens to the fee and VAT:** a refund reverses the platform fee and the
VAT charged on it **in proportion to the refunded fraction** — a full refund
reverses both completely, a partial refund reverses the same fraction. The
reversal is measured cumulatively so several partial refunds always sum to the
whole, and every refund journal balances.

The thing that was bought **is** undone: a refunded booking, offer, unit
reservation or rent schedule is reversed or flagged, and the refund limit is the
original amount minus what is already refunded, re-checked under a row lock.

Evidence: `app/Http/Controllers/FinancialReportingController.php` (`issueRefund`,
`proportionalReversal`).

**Stay cancellations** use a separate, friendlier rule:

| When they cancel | What they get back |
|---|---|
| 2 or more days before check-in | full refund minus the service fee |
| Less than 48 hours before | half of that |
| After check-in | nothing |

This is **calculated and shown** on the booking, and a cancellation that owes money
now writes a real refund: it advances the booking's payment and posts a balanced
journal through the one refund writer. The policy retains the whole service fee, so
the refund comes out of the accommodation revenue and no fee or VAT is reversed.
`app/Http/Controllers/GuestDashboardController.php` (`cancelBooking`).

### 4.3 Commissions (how agents get paid)

```
   Offer accepted → "Convert to Deal"
              │
              ▼
   ┌────────────────────────────────────────────┐
   │ commission row created                     │
   │  rental: rate = dated rule, else 5% default│
   │  sale:   rate = dated rule, else agent rate │
   │          (default 5%)                       │
   │  overridable per agent / property / company │
   └────────┬───────────────────────────────────┘
            │  every finance officer + admin + the agent
            │  are notified (CommissionGeneratedNotification)
            ▼
   Commission::settlePayout()   ← NO BUTTON IN THE UI
        │
        ├── WHT = gross × dated withholding rate (default 2%)
        ├── net = gross − WHT
        ├── status = paid, voucher number VCH-COMM-YYYY-XXXX
        └── balanced journal: DEBIT agent_commission (gross)
                              CREDIT cash (net)
                              CREDIT tax_withholding (WHT, rate snapshotted)
```

**The split engine is unused.** `CommissionSplitService` can split a commission
between a junior/senior/principal agent, handle co-brokered deals, and take a
referral cut. It is fully written and fully tested, but only its **tier lookup**
is called; the split engine that would create the split rows is dead.
Evidence: `app/Services/CommissionSplitService.php`.

### 4.4 Payouts

There are two payout objects in the code, and neither works for the people who need it:

- **`PayoutRequest`** — gross amount, 2% withholding tax, net amount, bank details.
  A finance officer can disburse it. **But there is no form anywhere that creates one.**
  Only a test does.
- **`Commission::settle`** — the real way an agent gets money. **No button in the
  interface exists for it.**

So: an agent can see what they are owed, but cannot request it, and a finance officer
cannot pay it from any screen.

### 4.5 Rent

Three different buttons record the same rent payment, with three different results:

| Who presses it | Payment created? | Ledger entry? | Platform fee |
|---|---|---|---|
| Landlord (Seller desk) | **No** | **No** | — |
| Tenant (client portal) | Yes | Yes | **2%** |
| Finance desk (lease screen) | Yes | Yes | **0%** |

So rent recorded by a landlord **never appears in any financial report**.
`app/Http/Controllers/SellerDashboardController.php:677-708`.

### 4.6 The hard answer about real money

**No money ever moves.** There is no payment gateway library, no API key in
`config/services.php`, no bank connection, no SDK, and no HTTP call to any provider.

Two consequences worth knowing:

1. The webhook endpoint `POST /payments/callback` has **no login and no signature
   check**. Anyone who knows a transaction number can mark a payment `completed`.
   (`app/Http/Controllers/PaymentController.php:224-310`, route at `routes/web.php:753`)
2. Choosing **telebirr** skips the finance desk entirely and writes
   `verified_by = the payer`. The buyer approves their own payment.

The bank account list shown in the checkout (CBE, Awash, Dashen with real-looking
numbers) is **display only**. Nothing checks whether money arrived there.

---

## 5. Who hands over to whom

One line through a normal sale, showing every hand-off:

```
  OWNER publishes the listing
        │  photos + title deed uploaded
        ▼
  VERIFICATION OFFICER checks the deed → level 5 badge
        │  (nobody is notified that this happened)
        ▼
  BUYER searches the map and finds it
        │  presses "Schedule Viewing", then "Make Formal Offer"
        ▼
  AGENT answers the inquiry, confirms the viewing,
        counters the offer (notified both ways)
        │  "Convert to Deal"
        ▼
  SYSTEM creates the order + the PDF contract,
        creates a COMMISSION, notifies all finance officers
        │
        ▼
  BUYER pays (slip)  →  payment is `pending`
        │
        ▼
  FINANCE OFFICER presses Verify
        │  ledger credit + 2% fee + 15% VAT
        │  payer and agent notified
        ▼
  AGENT'S COMMISSION waits to be settled
        │  no button exists — a finance officer
        │  can only reach it through the code
        ▼
  ❌ PAYOUT MISSING — the agent is never actually paid
```

And the supporting hand-offs:

```
 A guest reports a listing ─────► ADMIN + COMPANY + AGENT get a notification
                                        │
                                        ▼
                                   MODERATOR decides
                                   publish / pause / reject / merge
                                        │
             ┌──────────────────────────┼──────────────────────┐
             ▼                          ▼                      ▼
       warns the user            flags fraud           merges duplicates
             │                          │                      │
             └──────────┬───────────────┘                      │
                        ▼                                      ▼
                 CUSTOMER SUPPORT                    the surviving listing
                        │                        keeps every inquiry, offer
                        ▼                        and report
              payment issue escalated ──────► FINANCE OFFICER
```

```
 A tenant raises a repair ──────► PROPERTY MANAGER
                                         │ assigns a contractor
                                         ▼
                                SERVICE PROVIDER quotes,
                                books, completes, files a report
                                         │
                                         ▼
                                PROPERTY MANAGER closes the ticket
```

```
 A host dispatches cleaning ──► CLEANER marks it done
                                        │
                                        ▼
                                HOST / RECEPTIONIST
                                confirms the room is ready
```

```
 An agent writes a campaign ──► CAMPAIGN OFFICER approves or rejects
                                        │
                                        ▼
                                AGENT is notified either way
```

---

## 6. Demo walkthrough

Every demo account uses the password **`secret123`**. All 21 are flagged `is_demo` and
all are `active`.

| Email | Role | Use it to show this |
|---|---|---|
| admin@realestate.com | admin | the whole admin dashboard, users, roles, verification |
| buyer@realestate.com | buyer | search, viewing, offer, payment, review |
| tenant@realestate.com | tenant | lease signing, rent, maintenance |
| traveler@realestate.com | traveler | booking a stay, check-in, review the host |
| guest@realestate.com | guest | the registered guest desk |
| owner@realestate.com | property_owner | add listing, upload photos & documents, publish |
| host@realestate.com | host | add a stay, set price, accept a booking, dispatch cleaning |
| landlord@realestate.com | landlord | leases, rent collection, deposits |
| agent@realestate.com | agent | CRM, viewings, offers, commissions |
| company@realestate.com | company | branches, agents, listings, subscription |
| developer@realestate.com | developer | project, building, units, escrow |
| propertymanager@realestate.com | property_manager | buildings, units, maintenance, cleaning |
| service@realestate.com | service_provider | jobs, quotes, reports |
| moderator@realestate.com | moderator | reports, listing decisions, merging duplicates |
| verification@realestate.com | verification_officer | documents, trust levels, companies |
| verifycompany@realestate.com | verification_company | paid verification jobs |
| finance@realestate.com | finance_officer | payments, ledger, refunds, commissions |
| support@realestate.com | customer_support | tickets, disputes, escalations |
| cleaner@realestate.com | cleaner | cleaning tasks |
| investor@realestate.com | investor | portfolio and yield numbers |
| campaign@realestate.com | campaign_officer | approve or reject campaigns |

**One button on the login page is broken.** The "1-Click Quick Fill" list offers
`receptionist@realestate.com`, but that account no longer exists. It also omits
`guest@realestate.com`, which *does* exist.

### A ready-made 7-step demo

All of this works against the seeded data.

```
 STEP 1 — OWNER uploads photos
   log in as owner@realestate.com → Seller desk → Media Studio
   property #26 "Nesciunt Hic Ipsam Villa" is waiting there
   press Upload → the photo appears in the gallery

 STEP 2 — VERIFY the deed
   log in as verification@realestate.com → Verification desk
   property #26 already has 2 title-deed documents (1 verified, 1 pending)
   approve the pending one; raise the trust level to 5 → a badge appears

 STEP 3 — PUBLISH
   back as owner@realestate.com → press Publish
   property #26 status becomes published, is_active = true

 STEP 4 — BUYER finds it
   log in as buyer@realestate.com → /properties, or draw a box on the map
   property #26 is on the map at 8.9876, 38.7109
   press "Schedule Viewing" and "Make Formal Offer"

 STEP 5 — AGENT answers
   log in as agent@realestate.com → Agent portal
   the viewing and the offer are waiting in the CRM
   confirm the viewing, then counter or accept the offer

 STEP 6 — FINANCE takes the money
   log in as finance@realestate.com → Finance desk → "Payments to Confirm"
   press Verify → the ledger fills, the 2% fee is charged,
                 the buyer gets a "Payment Verified" notification

 STEP 7 — REVIEW
   back as buyer@realestate.com → the property page → leave a review
   it is published immediately
```

### Other good seeded examples

| To show this | Use this |
|---|---|
| A **map search** | property 15 "beast house for living", rent 23,000 ETB, has a real GPS pin |
| **Offers already in play** | property 12 — 4 offers, one accepted, one rejected |
| **A stay with a pending booking** | booking `STAY-AA-202610-AORQ`, property 49, 10,300 ETB, unpaid |
| **A live lease** | lease 5 (`LSE-ETH-2026-BE29A0`), tenant@realestate.com, 55,000 ETB/month, `active` |
| **A rent schedule to pay** | rent_schedules id 9 — 150,000 ETB due, status `pending` |
| **A completed viewing** | `VIEW-AA-202610-ZHSZ` on property 32, assigned to the demo agent |
| **Refunds and the ledger** | Finance desk → Refunds and Ledger sections |
| **Merging duplicate listings** | Moderator desk → merge (pick two similar villas) |
| **Developer escrow** | developer@realestate.com → a project → request a milestone disbursement |

### How to reset the demo

```
php artisan demo:reset
```
This re-seeds the database, merges old role names, cleans up duplicate accounts and
re-flags all 21 demo accounts. Use `php artisan demo:user-accounts` alone just to audit
which accounts have the right password and are still active.

---

## 7. Reality check

Every part of the platform is marked:

- **Working** — the code does what the specification says
- **Partly working** — the code runs, but it is inconsistent, half-wired, or never used
- **Missing** — the code is not there

### 7.1 The property lifecycle

| Step | Verdict | Evidence |
|---|---|---|
| 1. Create property | **Partly working** | 8 different entry points save a property, and they default to **different** statuses: `draft` (`SellerDashboardController.php:157`), `published` (`HostDashboardController.php:140`) and `available` (`PropertyManagerDashboardController.php:157`) |
| 2. Upload photos | **Working** | `SellerDashboardController.php:356-384` |
| 2a. Strip EXIF/GPS from photos | **Partly working** | Only the Seller desk calls the stripper; host, agent and admin paths keep the GPS data |
| 2b. Upload documents with versions | **Working** | `PropertyVerificationController.php:18-65` |
| 3. Verification (levels 0-5, badges) | **Working** | `PropertyVerificationController.php:122-169`, migration `2026_10_03_110857` |
| 3a. Nobody is notified of a verification decision | **Missing** | no `notify()` in any verification controller |
| 4. Listing auto-created | **Partly working** | `Property::booted()` auto-creates a listing, and listing CRUD does exist (seller, moderator and company routes in `routes/web.php`), but public search never reads the `listings` table |
| 4a. Listing quality score | **Partly working** | Four different algorithms in four places; the `properties.quality_score` accessor is never stored |
| 5. Publish / unpublish | **Working** | `SellerDashboardController.php:301-351` |
| 5a. Draft → submitted → moderation → published | **Partly working** | Publishing is instant. A seller *may* pick `pending_review`, but nothing enforces it (SRS section 30), and `PropertyFormRequest.php:47` cannot even submit it |
| 5b. Auto-send new listings to the moderation queue | **Missing** | no code sets `pending_review` automatically |
| 6. Search with filters | **Working** | `PropertyController.php:17-162` |
| 6a. Plain-language search ("3 bedroom under 30 million") | **Working** | `PropertyController.php:598-652` |
| 6b. Map / GPS area search | **Working** | `PropertyController.php:169-184`, `tests/Feature/PropertyMapAreaSearchTest.php` |
| 6c. Guaranteed coordinates | **Partly working** | properties without a pin get an invented location for display (`Property.php:534-592`), so the map looks full but those cannot be found by area |
| 7. Inquiry | **Partly working** | no dedicated form; created as a side effect. When the listing has an agent it is attached, but the contact form creates one with no agent, property or user link |
| 7a. Inquiry reply | **Partly working** | replies are appended as text, not threaded; `pending` is outside the allowed status list |
| 8. Viewing (request → confirm → complete) | **Working** | `ViewingOfferController.php:27-73,457-485` |
| 8a. Notify the buyer when they reschedule their own viewing | **Missing** | the buyer's own reschedule path (`BuyerDashboardController::rescheduleViewing()`) notifies nobody at all |
| 9. Offer and negotiation | **Working** | `ViewingOfferController.php:100-260` |
| 9a. Notify on rejection | **Missing** | `rejectOffer()` sends nothing; the `'rejected'` branch of the notification is dead code |
| 10. Reservation / booking (double-booking safe) | **Working** | `StayBookingController.php:137-200` uses `lockForUpdate` |
| 10a. Expire unpaid holds (24h / 72h) | **Working** | scheduled hourly in `routes/console.php:12` |
| 11. Payment (slip, 4 methods, 5 types) | **Working** | `PaymentController.php:23-96` |
| 11a. Finance verification desk | **Working** | `PaymentController.php:122-190` |
| 11b. Cash payment from the checkout modal | **Partly working** | the validator allows `cash`, but the modal only offers three methods |
| 11c. Gateway webhook signature | **Working** | `POST /payments/callback` now requires an HMAC-SHA256 provider signature, logs every call (accepted or rejected), and is idempotent on the provider reference (`VerifyPaymentGatewaySignature`, `PaymentGatewayLog`) |
| 11d. Telebirr self-verification | **Working** | no method verifies itself: a Telebirr payment is recorded `pending` and only finance can confirm it (`PaymentController::store`, `payments.verify`) |
| 12. Agreement PDF with a digital fingerprint | **Working** | `AgreementPdfService.php:17-141` |
| 13. Completion (lease signed, property sold) | **Working** | `TenantDashboardController.php:254-297`, `ViewingOfferController.php:312-343` |
| 13a. Sale escrow / deal closing | **Missing** | the code writes "earnest money escrow milestone initiated" but no escrow record exists. Only developer projects have escrow |
| 13b. Notify on lease creation or signing | **Missing** | no `notify()` in either path |
| 14. Property review (instantly published) | **Working** | `PropertyReviewController.php:15-59` |
| 14a. Agent feedback (approval-gated) | **Working** | `AgentController.php:66-90` — but with the **opposite** rule to property reviews |
| 14b. Notify on review submission | **Missing** | no notification on any review |

### 7.2 Money

| Item | Verdict | Evidence |
|---|---|---|
| Payment rows and statuses | **Working** | `PaymentController.php:23-96` |
| Platform fee + VAT rates | **Working** (configurable) | read from `site_settings` via `FinanceSettings` — SRS §195 |
| Ledger (append-only, cannot be edited or deleted) | **Working** | `FinancialLedger.php:39-45` |
| Ledger **double-entry** | **Working** | every money path posts a balanced journal through `LedgerService::post()`, which refuses an unbalanced posting; the UI's "Double-Entry Ledgers" is now true |
| Ledger CSV export + printable statement | **Working** | `FinancialReportingController.php:611-684` |
| Finance reports, period filters, charts | **Working** | `FinancialReportingController.php:31-215` |
| Commission creation | **Working** | sale and rental both use the agent's rate or the one configured default (`finance.default_commission_rate`) |
| Commission defaults disagree | **Fixed** | one source of truth: `FinanceSettings::defaultCommissionRate()` |
| `CommissionSplitService` (tiers, co-broking, referrals) | **Partly working** | the **tier lookup** (`getTierDetails`) is used across the agent/worker desks; the **split engine** (`calculateSplits`, `recordDealCommission`) is retained and tested but has **no production caller** — no co-brokered/referral deal flow exists, and wiring it changes the agent's payout (tier split vs full pool), a policy decision still open |
| Commission settlement + voucher | **Working** | a **Settle (manual)** button on the finance desk applies the withholding tax, issues a voucher, records the bank reference and posts a balanced journal |
| Agent / host / professional payout | **Working** | the payout queue shows each party's ledger-derived available balance (`AgentBalanceService`); agents, hosts and professionals share the same balance and payout services, each seeing **only their own** balance and payouts (`payouts.view` / `payouts.request`) with the bank account number **masked** to the last four digits; only finance sees the full book. A request must clear the minimum threshold, the schedule and the holdback, the balance check runs under a party-row lock, and finance approves (maker-checker) then disburses as a manual disbursement |
| Negative balances (people who owe the platform) | **Working** | a refund after payout posts a recovery; finance gets a **"people who owe the platform"** list, and a later earning nets against the debt |
| Encrypted bank details | **Working** | `payout_requests.account_number` / `bank_name` and `payments.bank_account_number` are encrypted at rest (`App\Casts\EncryptedString`) and masked on screen |
| Legacy ledger attribution backfill | **Working** | `php artisan ledger:backfill-party` (dry-run first) fills `party_user_id` on old commission/payout legs, idempotently, so pre-existing balances read correctly |
| Refunds | **Working** | the limit is the original amount minus what was already refunded, the linked booking/offer/unit/rent schedule is reversed or flagged, each refund writes a `refunds` row and a balanced audit journal, a VAT reversal issues a credit note, and a refund after payout posts a recovery |
| Stay cancellation refund | **Working** | a cancellation that owes money advances the booking's payment and posts a balanced journal through the shared `RefundService`; the guest-facing service fee is kept while the platform fee is reversed proportionally |
| Rent: landlord route leaves no financial trace | **Working** | the landlord and tenant rent desks share `RentPaymentService`, so both write a verified payment and a balanced journal |
| Tax withholding ledger entries | **Working** | commission settlement and payout disbursement write a `tax_withholding` credit |
| VAT report, remittance and credit notes | **Working** | `/finance/vat` shows the `vat_payable` balance by period, `finance.vat.remit` records a remittance (debit `vat_payable`, credit `cash`), and VAT-reversing refunds issue credit notes (SRS §170) |
| Legacy single-sided rows | **Working** | kept out of net revenue and the P&L and shown as their own totals; see `docs/LEDGER_RECONCILIATION.md` |
| Rate-change governance | **Working** | a change logs old and new values, and an optional second-person approval holds it `pending` until a different finance user approves |
| Real money movement | **Missing** | no gateway, no SDK, no API key, no bank call; payouts are recorded as **manual disbursements** |
| Bank reconciliation against the slip reference | **Partly working** | `/finance/reconciliation` lists pending/matched references and `finance.reconciliation.match` matches a reference to its pending payment(s) and settles them. One transfer may cover several payments sharing the reference; a reference already matched is rejected; an amount that does not equal the payments' total is **flagged for review, never auto-matched**. There is still no import of a bank statement file |

### 7.3 Roles and access

| Item | Verdict | Evidence |
|---|---|---|
| All 17 planned roles exist as content | **Working** | `config/personas.php`, `docs/SRS.md:171-311` |
| Property Owner and Landlord share one desk | **Partly working** | no separate landlord screen exists |
| Customer Support and old Receptionist share one desk | **Partly working** | two controllers, one merged role; the receptionist's demo account was deleted |
| Admin boundary (admins blocked from personal desks) | **Working** | explicit in every personal controller |
| Admin impersonation | **Working** | deliberately blocked (`UserManagementController.php:437-440`) |
| Self-service role choice on registration | **Missing** | `AuthController.php:202` hard-codes `client` |
| `campaign_officer` cannot be created through the admin UI | **Partly working** | absent from `assignable_roles`, filtered from the dropdown |
| The `receptionist` role cannot be created through the admin UI | **Partly working** | the dropdown offers it, the validator rejects it |
| Staff-to-company linking | **Missing** | there is no `companies` table and no `company_id` on `users`. Only `agents.branch_id` exists |
| Role/permission middleware on routes | **Partly working** | the money routes (refunds, invoices, payouts, commissions, payment verification, shift reconciliation) now carry `permission:` middleware; the rest are still hand-written `if` checks |
| Uncatalogued permissions default to **allow** | **Working** | now default-**deny**: a permission key that is not in the catalogue grants nothing (`User::hasPermission`). The administrator boundary is unchanged |
| The stale `receptionist@realestate.com` login button | **Partly working** | shown in the quick-fill list but the account was deleted (the whole quick-fill is now local-only) |
| Demo accounts in production | **Working** | the seeder and the `demo:*` commands refuse outside `APP_ENV=local`, and the login quick-fill is hidden (`App\Support\DemoData`) |

### 7.4 Notifications

| Step | Notification | Verdict |
|---|---|---|
| Payment verified / failed | `PaymentStatusNotification` | **Working** |
| Viewing requested / status changed | `ViewingStatusNotification` | **Working** |
| Offer submitted / countered / accepted | `OfferStatusNotification` | **Working** |
| Offer rejected / converted to a deal | — | **Missing** |
| Booking created (check-in instructions, checked in, checked out) | `GuestBookingNotification` | **Working** |
| Booking confirmed | — | **Missing** (the branch exists but is never sent) |
| Booking cancelled | — | **Missing** |
| Payment refunded | — | **Missing** |
| Property created or published | — | **Missing** |
| Document uploaded | — | **Missing** |
| Verification decision | — | **Missing** |
| Lease created or signed | — | **Missing** |
| Rent due / overdue (daily command) | `RentDueReminderNotification` | **Working** |
| Commission generated / settled | `CommissionGeneratedNotification` | **Working** |
| Payout requested / approved / rejected / paid | `PayoutStatusNotification` | **Working** |
| Campaign submitted / decided | `CampaignStatusNotification` | **Working** |
| Listing reported | `ClientReportSubmittedNotification` | **Working** |
| Report decision | — | **Missing** |
| Support ticket created or escalated | — | **Missing** |
| Dispute created or staged | — | **Missing** |
| Review submitted | — | **Missing** |
| Maintenance ticket assigned | — | **Missing** (the assignee is resolved, then never told) |
| Inquiry email to the agent | `OrderReceived` / `InquiryReceived` mail | **Working** |
| Order status change to the client | `OrderStatusUpdate` mail | **Working** |

**All 9 notifications are `database` only.** There is no email channel notification,
no SMS, and no push. The 4 emails are sent imperatively.

`stays:send-arrival-instructions` exists but is **not scheduled** — it must be run by
hand (`routes/console.php` schedules only rent and holds).

### 7.5 The five modes

| Mode | Verdict | Evidence |
|---|---|---|
| **Buy** | **Working** | sale listing → offer → contract → commission → review |
| **Rent** | **Working** | lease → rent schedule → payment → reminders → deposit |
| **Lease** | **Partly working** | the same rent engine handles it; no separate lease-mode screen |
| **Stay** | **Working** | booking with a price recalculated server-side, row locking, check-in/out, cleaning |
| **New Projects** | **Working** | project → building → units → reservation → milestone escrow |

### 7.6 The three biggest holes

1. **No money actually moves.** There is no outbound payment gateway and no bank call.
   (The inbound `POST /payments/callback` door is now locked: it requires a provider
   HMAC signature, logs every call, and is idempotent on the provider reference.)

2. **People can now be paid from the UI.** The `CommissionSplitService` split engine
   drives co-brokered and referral deals, commission settlement has a **Settle
   (manual)** button, and the payout queue computes each party's ledger-derived
   balance, enforces the threshold/schedule/holdback, and runs a maker-checker
   approve-then-disburse flow. The transfers themselves are still **manual** — there
   is no bank integration.

3. **Nothing is automatically moderated.** The specification wants every listing to pass
   through a moderation queue (SRS section 30). Publishing is instant for owners, agents,
   companies and hosts. The fraud-detection engine exists (duplicate photo/phone/coordinate
   matching) but is never wired into the listing flow.

### 7.7 Counts

| Thing | Count |
|---|---|
| Role strings in code | 22 (17 planned + 5 added) |
| Demo accounts, all with password `secret123` | 21 |
| Demo accounts offered on the login page | 21 (one of them no longer exists; the quick-fill is local-only) |
| Permissions in the catalogue | 349 keys (318 granted to a persona, 23 catalogued-but-not-granted) |
| Properties in the seeded data | 59 |
| Listings | auto-created, one or more per property |
| Viewings | 2 seeded for the buyer |
| Offers | 17 (10 submitted, 5 accepted, 2 rejected) |
| Bookings | several, one pending unpaid at 10,300 ETB |
| Leases | 5 (1 active, 4 terminated) |
| Rent schedules | 27 (11 paid, 16 pending) |
| Notification classes | 9, all `database` channel |
| Mail classes | 4 |
| Routes in `routes/web.php` | ~800 lines of definitions |

---

## Appendix — where to look next

| If you want to understand | Open this |
|---|---|
| The original plan | `docs/SRS.md` |
| Role permissions | `config/personas.php` |
| Who can do what | `app/Models/User.php` (`hasPermission`, `getDashboardUrlAttribute`) |
| The property rules | `app/Models/Property.php` |
| The money | `app/Http/Controllers/PaymentController.php`, `app/Http/Controllers/FinancialReportingController.php`, `app/Models/Payment.php` |
| The marketplace search | `app/Http/Controllers/PropertyController.php` |
| The desk screens | `resources/views/dashboards/` and `resources/views/dashboard/` |
| Every route | `routes/web.php` and `routes/api.php` |
| What is broken | `docs/KNOWN_FAILURES.md` |
| The demos | `php artisan demo:user-accounts` |

*This document was written from the code as it is. Where the code and the specification
disagree, the code was trusted and the gap is listed in section 7.*
