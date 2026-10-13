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

| # | User Type | Email | Dashboard Route | Core Workflow Focus |
|---|-----------|-------|-----------------|---------------------|
| 1 | **Buyer** | `buyer@realestate.com` | `/dashboard/buyer` | Saved properties, saved search alerts, inquiries, viewings, formal offers |
| 2 | **Tenant / Renter** | `tenant@realestate.com` | `/dashboard/tenant` | Active leases, rent payment schedule, maintenance tickets, tenancy documents |
| 3 | **Guest (Short-Stay)** | `traveler@realestate.com` | `/dashboard/guest` | Bookings (pending/confirmed/completed), check-in details, reviews, saved stays |
| 4 | **Investor** | `investor@realestate.com` | `/dashboard/investor` | Portfolio assets, yield estimates (gross/net), rental cash flow, market indicators |
| 5 | **Seller / Landlord** | `owner@realestate.com` | `/dashboard/seller` | Properties, published listings, lead inquiries, quality scores |
| 6 | **Host (Short-Stay)** | `host@realestate.com` | `/dashboard/host` | Short-stay properties, upcoming check-ins/outs, occupancy %, payouts |
| 7 | **Agent / Broker** | `agent@realestate.com` | `/agent-portal` | CRM pipeline (New Lead → Closed), viewings, offers, commission splits |
| 8 | **Company / Branch Manager** | `company@realestate.com` | `/company` | Branches, team agents, verified listings, company performance & revenue |
| 9 | **Developer** | `developer@realestate.com` | `/developer-portal` | Construction projects, buildings, units (Available/Reserved/Sold), payment plans |
| 10 | **Property Manager** | `propertymanager@realestate.com` | `/dashboard/property-manager` | Managed properties, tenant/guest queues, rent tracking, cleaning dispatch |
| 11 | **Professional** (Valuer/Lawyer) | `service@realestate.com` | `/dashboard/professional` | Service requests, appointment calendar, report submissions, earnings |
| 12 | **Cleaner / Housekeeping** | `cleaner@realestate.com` | `/dashboard/cleaner` | Assigned tasks, turnaround checklist (Assigned → In Progress → Inspection → Ready) |
| 13 | **Super Admin** | `admin@realestate.com` | `/dashboard` | Users, roles & permission matrix, platform metrics, security audit log |
| 14 | **Verification Officer** | `verification@realestate.com` | `/dashboard/verification-officer` | Title deed cartas, site plan mapas, company licenses, developer credentials |
| 15 | **Moderator** | `moderator@realestate.com` | `/dashboard/moderator` | Listing review queue, report triage (fraud/duplicates), review moderation |
| 16 | **Finance Officer** | `finance@realestate.com` | `/finance` | Payments, financial ledger, refunds, agent commission settlements, vouchers |
| 17 | **Customer Support** | `support@realestate.com` | `/dashboard/customer-support` | Inbound support tickets, non-sensitive customer lookup, dispute resolution |

---

## License
The application is open-sourced software licensed under the MIT license.
