<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Real Estate Marketplace — 17 Dedicated Dashboards & RBAC

This application implements 17 permission-driven dashboards for each target actor in the Ethiopian Real Estate Marketplace ecosystem, fully supported with granular RBAC, audit logging, and permission-gated media.

### Seeding & Demo Commands

- **Seed All Marketplace Data & Roles**:
  ```bash
  php artisan db:seed
  ```
- **Reset & Re-seed (Dev Only)**:
  ```bash
  php artisan migrate:fresh --seed
  ```
- **Execute Factor & Feature Test Matrix**:
  ```bash
  vendor/bin/pest tests/Feature/SeventeenUserDashboardsMatrixTest.php
  ```

---

### Demo Accounts & Credentials (Password: `secret123`)

Every role below has **exactly one seeded account** (`@realestate.com`), created by `DatabaseSeeder` and re-safe on every seed. Running `php artisan db:seed --class=DatabaseSeeder` twice creates no duplicates.

#### The 17 SRS personas

| # | User Type | Email | Dashboard Route | Core Workflow Focus |
|---|-----------|-------|-----------------|---------------------|
| 1 | **Buyer** | `buyer@realestate.com` | `/dashboard/buyer` | Saved properties, saved search alerts, inquiries, viewings, formal offers |
| 2 | **Tenant / Renter** | `tenant@realestate.com` | `/dashboard/tenant` | Active leases, rent payment schedule, maintenance tickets, tenancy documents |
| 3 | **Guest / Traveler** | `traveler@realestate.com` | `/dashboard/guest` | Bookings (pending/confirmed/completed), check-in details, reviews, saved stays |
| 4 | **Guest** | `guest@realestate.com` | `/dashboard/guest` | Guest self-service desk: bookings, check-in/out, saved stays |
| 5 | **Investor** | `investor@realestate.com` | `/dashboard/investor` | Portfolio assets, yield estimates (gross/net), rental cash flow, market indicators |
| 6 | **Seller / Property Owner** | `owner@realestate.com` | `/dashboard/seller` | Properties, published listings, lead inquiries, quality scores |
| 7 | **Landlord** | `landlord@realestate.com` | `/dashboard/seller` | Leases, rent schedules, tenants, maintenance dispatch |
| 8 | **Host (Short-Stay)** | `host@realestate.com` | `/dashboard/host` | Short-stay properties, upcoming check-ins/outs, occupancy %, payouts |
| 9 | **Agent / Broker** | `agent@realestate.com` | `/agent-portal` | CRM pipeline (New Lead → Closed), viewings, offers, commission splits |
| 10 | **Company / Branch Manager** | `company@realestate.com` | `/company` | Branches, team agents, verified listings, company performance & revenue |
| 11 | **Developer** | `developer@realestate.com` | `/developer-portal` | Construction projects, buildings, units (Available/Reserved/Sold), payment plans |
| 12 | **Property Manager** | `propertymanager@realestate.com` | `/dashboard/property-manager` | Managed properties, tenant/guest queues, rent tracking, cleaning dispatch |
| 13 | **Professional** (Valuer/Lawyer) | `service@realestate.com` | `/dashboard/professional` | Service requests, appointment calendar, report submissions, earnings |
| 14 | **Super Admin** | `admin@realestate.com` | `/dashboard` | Users, roles & permission matrix, platform metrics, security audit log |
| 15 | **Verification Officer** | `verification@realestate.com` | `/dashboard/verification-officer` | Title deed cartas, site plan mapas, company licenses, developer credentials |
| 16 | **Moderator** | `moderator@realestate.com` | `/dashboard/moderator` | Listing review queue, report triage (fraud/duplicates), review moderation |
| 17 | **Finance Officer** | `finance@realestate.com` | `/finance` | Payments, financial ledger, refunds, agent commission settlements, vouchers |
| 18 | **Customer Support** | `support@realestate.com` | `/dashboard/customer-support` | Inbound support tickets, non-sensitive customer lookup, dispute resolution |

> The `receptionist` role was merged into `customer_support` (identical permission set) and `receptionist@realestate.com` no longer exists — use `support@realestate.com`.

#### Internal admin presets (outside the 17 SRS personas)

| # | User Type | Email | Dashboard Route | Why it exists |
|---|-----------|-------|-----------------|----------------|
| 19 | **Cleaner / Housekeeping** | `cleaner@realestate.com` | `/dashboard/cleaner` | Assigned tasks, turnaround checklist (Assigned → In Progress → Inspection → Ready) |
| 20 | **Campaign Officer** | `campaign@realestate.com` | `/dashboard/campaign` | Promotions & marketing campaign review desk (`campaigns.*` permissions) |
| 21 | **Verification Company** | `verifycompany@realestate.com` | `/verification-company` | Business account for outsourced verification jobs, reports and verification payments |

Roles merged away during the one-account-per-role cleanup: `client` + `user` → `buyer`, `seller` → `property_owner`, `company_admin` + `real_estate_company` → `company`, `receptionist` → `customer_support`, `housekeeping` → `cleaner`. See `docs/USER_CLEANUP_PLAN.md`.

---

## License
The application is open-sourced software licensed under the MIT license.
