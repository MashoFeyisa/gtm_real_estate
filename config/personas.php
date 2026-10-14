<?php

/**
 * Persona & Permission Catalog
 *
 * The platform's actors (personas) and the capability bullets each one
 * receives. Seeded into the `permissions` table by RolePermissionSeeder
 * and managed by admins from the Roles & Permissions desk.
 *
 * - key:          catalog identity of the persona (also the permission `group`)
 * - label:        display name
 * - description:  what this persona is, shown on the admin form & persona portal
 * - role:         the users.role value whose default grants are edited by the
 *                 admin form (null = display-only persona, e.g. Guest)
 * - legacy_roles: older role strings that receive the same defaults
 * - portal:       route name of the persona's dashboard (null = no portal)
 * - permissions:  permission key => label (the capability bullets)
 */
return [

    'personas' => [

        // ── 1. Guest (unauthenticated visitor) ──────────────────────────
        [
            'key' => 'guest',
            'label' => 'Guest',
            'description' => 'Visitors without an account: browse, search, view properties, maps and photos, and search short stays. Registration is required for actions such as booking, messaging, saving, comparing, or making offers.',
            'role' => null,
            'legacy_roles' => [],
            'portal' => null,
            'permissions' => [
                'properties.browse' => 'Browse property listings',
                'properties.search' => 'Search properties',
                'properties.view' => 'View property details',
                'map.view' => 'View location maps',
                'photos.view' => 'View property photos',
                'stays.search' => 'Search short stays',
            ],
        ],

        // ── 2. Buyer ────────────────────────────────────────────────────
        [
            'key' => 'buyer',
            'label' => 'Buyer',
            'description' => 'Searches, saves and compares properties, contacts sellers, requests viewings, makes and negotiates offers, reserves, uploads documents, and tracks transactions.',
            'role' => 'buyer',
            'legacy_roles' => ['client', 'user'],
            'portal' => 'buyer.dashboard',
            'permissions' => [
                // Property browsing
                'properties.search' => 'Search properties',
                'properties.view' => 'View property details',
                'properties.save' => 'Save properties to favorites & collections',
                'properties.compare' => 'Compare properties side by side',

                // Seller & agent interaction
                'sellers.contact' => 'Contact sellers & agents',
                'inquiries.create' => 'Send inquiries',
                'inquiries.view' => 'View own inquiries',
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',

                // Viewings
                'viewings.create' => 'Request property viewings',
                'viewings.view' => 'View own viewings',
                'viewings.update' => 'Reschedule viewings',
                'viewings.cancel' => 'Cancel viewings',

                // Offers & negotiation
                'offers.create' => 'Make formal offers',
                'offers.view' => 'View own offers',
                'offers.update' => 'Update offers',
                'offers.negotiate' => 'Negotiate offers over multiple rounds',
                'offers.cancel' => 'Cancel offers',

                // Reservations
                'reservations.create' => 'Reserve properties',
                'reservations.view' => 'View own reservations',
                'reservations.cancel' => 'Cancel reservations',

                // Documents
                'documents.create' => 'Upload documents',
                'documents.view' => 'View own documents',
                'documents.delete' => 'Remove own documents',

                // Transactions
                'transactions.view' => 'View own transactions',
                'payments.view' => 'View own payments',

                // Reviews
                'reviews.create' => 'Write property reviews',
                'reviews.view' => 'View reviews',

                // Reports
                'reports.create' => 'Report listings',
            ],
        ],

        // ── 3. Tenant ───────────────────────────────────────────────────
        [
            'key' => 'tenant',
            'label' => 'Tenant',
            'description' => 'Finds rentals, requests viewings, communicates with landlords, submits applications, signs agreements, tracks rent, and submits maintenance requests.',
            'role' => 'tenant',
            'legacy_roles' => ['client', 'renter'],
            'portal' => 'tenant.dashboard',
            'permissions' => [
                // Rental search
                'rentals.search' => 'Find rental properties',
                'properties.view' => 'View property details',
                'properties.save' => 'Save properties to favorites',

                // Viewings
                'viewings.create' => 'Request property viewings',
                'viewings.view' => 'View own viewings',
                'viewings.update' => 'Reschedule viewings',
                'viewings.cancel' => 'Cancel viewings',

                // Landlord communication
                'landlords.communicate' => 'Communicate with landlords',
                'inquiries.create' => 'Send inquiries',
                'inquiries.view' => 'View own inquiries',
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',

                // Applications & agreements
                'applications.create' => 'Submit rental applications',
                'applications.update' => 'Edit rental applications',
                'applications.withdraw' => 'Withdraw rental applications',
                'applications.view' => 'View own applications',
                'agreements.view' => 'View lease agreements',
                'agreements.sign' => 'Sign & receive supported agreements',
                'leases.view' => 'View lease agreements',
                'leases.sign' => 'Digitally accept & sign leases',

                // Rent tracking
                'rent.view' => 'View rent schedule & history',
                'rent.pay' => 'Make rent payments',

                // Maintenance
                'maintenance.create' => 'Submit maintenance requests',
                'maintenance.view' => 'View own maintenance tickets',
                'maintenance.update' => 'Add info to own tickets',
                'maintenance.close' => 'Close resolved maintenance tickets',

                // Documents
                'documents.create' => 'Upload documents',
                'documents.view' => 'View own documents',
                'documents.delete' => 'Delete own documents',

                // Reviews
                'reviews.create' => 'Write reviews',
                'reviews.view' => 'View reviews',
            ],
        ],

        // ── 4b. Guest / Traveler (short-stay) ────────────────────────────
        [
            'key' => 'traveler',
            'label' => 'Guest / Traveler',
            'description' => 'Short-stay traveler: searches stays, chooses dates and guest counts, reserves, pays, checks in and out, and reviews hosts.',
            'role' => 'traveler',
            'legacy_roles' => ['client', 'user', 'guest'],
            'portal' => 'guest.dashboard',
            'permissions' => [
                // Stay search & browse
                'stays.search' => 'Search short stays',
                'stays.view' => 'View stay details',
                'properties.save' => 'Save properties to favorites',

                // Booking
                'stays.dates_select' => 'Choose stay dates',
                'stays.guests_select' => 'Select number of guests',
                'stays.reserve' => 'Reserve stays',
                'bookings.create' => 'Create bookings',
                'bookings.view' => 'View own bookings',
                'bookings.update' => 'Modify booking dates',
                'bookings.cancel' => 'Cancel bookings',

                // Payment
                'stays.pay' => 'Pay for stays',
                'payments.view' => 'View own payments',

                // Check-in & check-out
                'stays.check_in' => 'Check in',
                'stays.check_out' => 'Check out',

                // Communication
                'messages.create' => 'Send messages to hosts',
                'messages.view' => 'View messages',
                'inquiries.create' => 'Send inquiries',

                // Reviews
                'stays.review_host' => 'Review hosts',
                'reviews.create' => 'Write reviews',
                'reviews.view' => 'View reviews',
                'reviews.update' => 'Edit reviews',
                'reviews.delete' => 'Delete reviews',

                // Disputes
                'disputes.create' => 'Open disputes',
                'disputes.view' => 'View own disputes',
            ],
        ],

        // ── 4a. Guest (registered, self-service desk) ────────────────────
        [
            'key' => 'guest_account',
            'label' => 'Guest',
            'description' => 'Registered guest on the self-service desk: manages reservations, check-in/check-out, saved stays, reviews and disputes.',
            'role' => 'guest',
            'portal' => 'guest.dashboard',
            'permissions' => [
                // Stay search & browse
                'stays.search' => 'Search short stays',
                'stays.view' => 'View stay details',
                'properties.save' => 'Save properties to favorites',

                // Booking
                'stays.dates_select' => 'Choose stay dates',
                'stays.guests_select' => 'Select number of guests',
                'stays.reserve' => 'Reserve stays',
                'bookings.create' => 'Create bookings',
                'bookings.view' => 'View own bookings',
                'bookings.update' => 'Modify booking dates',
                'bookings.cancel' => 'Cancel bookings',

                // Payment
                'stays.pay' => 'Pay for stays',
                'payments.view' => 'View own payments',

                // Check-in & check-out
                'stays.check_in' => 'Check in',
                'stays.check_out' => 'Check out',

                // Communication
                'messages.create' => 'Send messages to hosts',
                'messages.view' => 'View messages',
                'inquiries.create' => 'Send inquiries',

                // Reviews
                'stays.review_host' => 'Review hosts',
                'reviews.create' => 'Write reviews',
                'reviews.view' => 'View reviews',
                'reviews.update' => 'Edit reviews',
                'reviews.delete' => 'Delete reviews',

                // Disputes
                'disputes.create' => 'Open disputes',
                'disputes.view' => 'View own disputes',
            ],
        ],

        // ── 5. Property Owner ───────────────────────────────────────────
        [
            'key' => 'property_owner',
            'label' => 'Property Owner',
            'description' => 'Registers and publishes own properties, uploads photos and documents, manages inquiries, receives offers, schedules viewings, and manages bookings.',
            'role' => 'property_owner',
            'legacy_roles' => ['seller'],
            'portal' => 'role.portal',
            'permissions' => [
                // Properties CRUD
                'properties.create' => 'Create properties',
                'properties.view' => 'View own properties',
                'properties.update' => 'Update own properties',
                'properties.delete' => 'Delete own properties',

                // Listings CRUD
                'listings.create' => 'Create listings',
                'listings.view' => 'View own listings',
                'listings.update' => 'Update own listings',
                'listings.delete' => 'Delete own listings',
                'listings.publish' => 'Publish listings',
                'listings.pause' => 'Pause listings',

                // Photos & media
                'photos.create' => 'Upload photos',
                'photos.view' => 'View photos',
                'photos.delete' => 'Delete own photos',

                // Documents
                'documents.create' => 'Upload documents',
                'documents.view' => 'View own documents',
                'documents.update' => 'Update own documents',
                'documents.delete' => 'Delete own documents',

                // Inquiries
                'inquiries.view' => 'View received inquiries',
                'inquiries.update' => 'Respond to inquiries',

                // Offers
                'offers.view' => 'View received offers',
                'offers.update' => 'Accept/reject/counter offers',

                // Viewings
                'viewings.view' => 'View viewing requests',
                'viewings.update' => 'Confirm/reschedule viewings',
                'viewings.cancel' => 'Cancel viewings',

                // Bookings
                'bookings.view' => 'View bookings',
                'bookings.update' => 'Accept/reject bookings',
                'bookings.cancel' => 'Cancel bookings',

                // Messages
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',

                // Reviews
                'reviews.view' => 'View reviews on properties',
            ],
            'sections' => [
                ['key' => 'properties', 'label' => 'My Properties', 'icon' => '🏠', 'permissions' => ['properties.create', 'properties.view', 'properties.update']],
                ['key' => 'inquiries', 'label' => 'Inquiries', 'icon' => '📨', 'permissions' => ['inquiries.view']],
                ['key' => 'offers', 'label' => 'Offers', 'icon' => '🤝', 'permissions' => ['offers.view']],
                ['key' => 'viewings', 'label' => 'Viewings', 'icon' => '👁️', 'permissions' => ['viewings.view']],
                ['key' => 'bookings', 'label' => 'Bookings', 'icon' => '📅', 'permissions' => ['bookings.view']],
            ],
        ],

        // ── 6. Host ─────────────────────────────────────────────────────
        [
            'key' => 'host',
            'label' => 'Host',
            'description' => 'Creates short-stay listings, defines availability, pricing and house rules, accepts bookings, communicates with guests, manages check-in and cleaning, and receives reviews.',
            'role' => 'host',
            'legacy_roles' => [],
            'portal' => 'host.dashboard',
            'permissions' => [
                // Stay listings CRUD
                'stays.listing_create' => 'Create short-stay listings',
                'stays.listing_view' => 'View own stay listings',
                'stays.listing_update' => 'Update stay listings',
                'stays.listing_delete' => 'Delete stay listings',

                // Availability & pricing
                'stays.availability_manage' => 'Define availability',
                'stays.pricing_manage' => 'Define prices',
                'stays.house_rules_manage' => 'Define house rules',
                'calendar.view' => 'View availability calendar',
                'calendar.update' => 'Update calendar & blocked dates',

                // Bookings CRUD
                'bookings.view' => 'View bookings',
                'bookings.accept' => 'Accept bookings',
                'bookings.update' => 'Update booking status',
                'bookings.cancel' => 'Cancel bookings',

                // Guests
                'guests.view' => 'View guest profiles',
                'guests.communicate' => 'Communicate with guests',
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',

                // Check-in & check-out
                'checkin.manage' => 'Manage check-ins',
                'checkout.manage' => 'Manage check-outs',

                // Cleaning & housekeeping
                'cleaning.create' => 'Create cleaning tasks',
                'cleaning.view' => 'View cleaning tasks',
                'cleaning.update' => 'Update cleaning task status',

                // Reviews
                'reviews.view' => 'View guest reviews',
                'reviews.create' => 'Write guest reviews',

                // Payouts
                'payouts.view' => 'View payout history',

                // Photos & documents
                'photos.create' => 'Upload photos',
                'photos.delete' => 'Delete photos',
                'documents.create' => 'Upload documents',
                'documents.view' => 'View own documents',
            ],
        ],

        // ── 7. Landlord ─────────────────────────────────────────────────
        [
            'key' => 'landlord',
            'label' => 'Landlord',
            'description' => 'Manages properties, tenants, leases, rent, deposits, and maintenance.',
            'role' => 'landlord',
            'legacy_roles' => [],
            'portal' => 'role.portal',
            'permissions' => [
                // Properties CRUD
                'properties.create' => 'Create properties',
                'properties.view' => 'View own properties',
                'properties.update' => 'Update own properties',
                'properties.delete' => 'Delete own properties',

                // Listings CRUD
                'listings.create' => 'Create listings',
                'listings.view' => 'View own listings',
                'listings.update' => 'Update own listings',
                'listings.delete' => 'Delete own listings',
                'listings.publish' => 'Publish listings',
                'listings.pause' => 'Pause listings',

                // Tenants CRUD
                'tenants.create' => 'Add tenants',
                'tenants.view' => 'View tenants',
                'tenants.update' => 'Update tenant records',
                'tenants.delete' => 'Remove tenants',

                // Leases CRUD
                'leases.create' => 'Create lease agreements',
                'leases.view' => 'View leases',
                'leases.update' => 'Update lease terms',
                'leases.terminate' => 'Terminate leases',

                // Rent management
                'rent.view' => 'View rent schedules',
                'rent.create' => 'Create rent schedules',
                'rent.update' => 'Record rent payments',
                'rent.manage' => 'Manage rent',

                // Deposits
                'deposits.view' => 'View deposits',
                'deposits.create' => 'Record deposits',
                'deposits.update' => 'Update deposit status',
                'deposits.manage' => 'Manage deposits',

                // Maintenance
                'maintenance.view' => 'View maintenance tickets',
                'maintenance.create' => 'Create maintenance tickets',
                'maintenance.update' => 'Update ticket status',
                'maintenance.manage' => 'Manage maintenance',

                // Inquiries & messages
                'inquiries.view' => 'View inquiries',
                'inquiries.update' => 'Respond to inquiries',
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',

                // Documents
                'documents.create' => 'Upload documents',
                'documents.view' => 'View documents',
                'documents.update' => 'Update documents',

                // Listings
                'listings.create' => 'Create listings',
                'listings.view' => 'View listings',
                'listings.update' => 'Update listings',
                'listings.publish' => 'Publish listings',
            ],
            'sections' => [
                ['key' => 'properties', 'label' => 'Properties', 'icon' => '🏠', 'permissions' => ['properties.view', 'properties.create']],
                ['key' => 'tenants', 'label' => 'Tenants', 'icon' => '🧑‍🤝‍🧑', 'permissions' => ['tenants.view']],
                ['key' => 'leases', 'label' => 'Leases', 'icon' => '📄', 'permissions' => ['leases.view']],
                ['key' => 'rent', 'label' => 'Rent & Deposits', 'icon' => '💰', 'permissions' => ['rent.view', 'deposits.view']],
                ['key' => 'maintenance', 'label' => 'Maintenance', 'icon' => '🔧', 'permissions' => ['maintenance.view']],
            ],
        ],

        // ── 8. Agent ────────────────────────────────────────────────────
        [
            'key' => 'agent',
            'label' => 'Agent',
            'description' => 'Manages properties, clients, leads, viewings, offers, commissions, and CRM pipeline.',
            'role' => 'agent',
            'legacy_roles' => [],
            'portal' => 'agent.portal',
            'permissions' => [
                // Properties CRUD
                'properties.create' => 'Create properties',
                'properties.view' => 'View properties',
                'properties.update' => 'Update properties',
                'properties.delete' => 'Delete properties',

                // Listings CRUD
                'listings.create' => 'Create listings',
                'listings.view' => 'View listings',
                'listings.update' => 'Update listings',
                'listings.delete' => 'Delete listings',
                'listings.publish' => 'Publish listings',

                // Clients CRUD
                'clients.create' => 'Add clients',
                'clients.view' => 'View clients',
                'clients.update' => 'Update client records',
                'clients.delete' => 'Remove clients',

                // Leads CRUD
                'leads.create' => 'Add leads',
                'leads.view' => 'View leads',
                'leads.update' => 'Update lead status',
                'leads.delete' => 'Remove leads',
                'leads.manage' => 'Manage leads',

                // Viewings CRUD
                'viewings.create' => 'Schedule viewings',
                'viewings.view' => 'View viewings',
                'viewings.update' => 'Reschedule/confirm viewings',
                'viewings.cancel' => 'Cancel viewings',

                // Offers CRUD
                'offers.create' => 'Make offers',
                'offers.view' => 'View offers',
                'offers.update' => 'Accept/reject/counter offers',
                'offers.negotiate' => 'Negotiate offers',
                'offers.manage' => 'Manage offers',

                // Commissions
                'commissions.view' => 'View commissions',
                'commissions.manage' => 'Manage commissions',

                // CRM
                'crm.view' => 'View CRM pipeline',
                'crm.manage' => 'Use CRM tools',

                // Inquiries
                'inquiries.view' => 'View inquiries',
                'inquiries.update' => 'Respond to inquiries',

                // Messages
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',

                // Documents
                'documents.create' => 'Upload documents',
                'documents.view' => 'View documents',

                // Photos
                'photos.create' => 'Upload photos',
                'photos.delete' => 'Delete photos',

                // Payments
                'payments.view' => 'View payments',

                // Reviews
                'reviews.view' => 'View reviews',
            ],
        ],

        // ── 9. Real Estate Company ──────────────────────────────────────
        [
            'key' => 'real_estate_company',
            'label' => 'Real Estate Company',
            'description' => 'Manages branches, agents, listings, properties, clients, transactions, and reports.',
            'role' => 'company',
            'legacy_roles' => ['company_admin', 'real_estate_company', 'branch_manager'],
            'portal' => 'company.dashboard',
            'permissions' => [
                // Branches CRUD
                'branches.create' => 'Create branches',
                'branches.view' => 'View branches',
                'branches.update' => 'Update branches',
                'branches.delete' => 'Delete branches',

                // Agents CRUD
                'agents.create' => 'Add agents',
                'agents.view' => 'View agents',
                'agents.update' => 'Update agent profiles',
                'agents.delete' => 'Remove agents',

                // Listings CRUD
                'listings.create' => 'Create listings',
                'listings.view' => 'View listings',
                'listings.update' => 'Update listings',
                'listings.delete' => 'Delete listings',
                'listings.publish' => 'Publish listings',
                'listings.approve' => 'Approve listings',

                // Properties CRUD
                'properties.create' => 'Create properties',
                'properties.view' => 'View properties',
                'properties.update' => 'Update properties',
                'properties.delete' => 'Delete properties',
                'properties.merge' => 'Merge duplicate properties',

                // Clients CRUD
                'clients.create' => 'Add clients',
                'clients.view' => 'View clients',
                'clients.update' => 'Update client records',
                'clients.delete' => 'Remove clients',

                // Transactions
                'transactions.view' => 'View transactions',
                'transactions.create' => 'Create transactions',
                'transactions.update' => 'Update transactions',

                // Payments
                'payments.view' => 'View payments',

                // Commissions
                'commissions.view' => 'View commissions',
                'commissions.manage' => 'Manage commissions',

                // Reports
                'reports.view' => 'View reports',
                'reports.create' => 'Generate reports',

                // Analytics
                'analytics.view' => 'View analytics',

                // Orders
                'orders.view' => 'View orders',
                'orders.create' => 'Create orders',
                'orders.update' => 'Update orders',
            ],
        ],

        // ── 10. Developer ───────────────────────────────────────────────
        [
            'key' => 'developer',
            'label' => 'Developer',
            'description' => 'Manages projects, buildings, units, construction progress, pricing, payment plans, buyers, and reservations.',
            'role' => 'developer',
            'legacy_roles' => [],
            'portal' => 'developer.portal',
            'permissions' => [
                // Projects CRUD
                'projects.create' => 'Create projects',
                'projects.view' => 'View projects',
                'projects.update' => 'Update projects',
                'projects.delete' => 'Delete projects',

                // Buildings CRUD
                'buildings.create' => 'Create buildings',
                'buildings.view' => 'View buildings',
                'buildings.update' => 'Update buildings',
                'buildings.delete' => 'Delete buildings',

                // Units CRUD
                'units.create' => 'Create units',
                'units.view' => 'View units',
                'units.update' => 'Update units',
                'units.delete' => 'Delete units',

                // Marketplace Listings
                'listings.create' => 'Create listings',
                'listings.view' => 'View listings',
                'listings.update' => 'Update listings',
                'listings.publish' => 'Publish listings',

                // Construction
                'construction.view' => 'View construction progress',
                'construction.update' => 'Update construction milestones',
                'construction.manage' => 'Track construction progress',

                // Pricing
                'pricing.view' => 'View pricing',
                'pricing.update' => 'Update pricing',
                'pricing.manage' => 'Manage pricing',

                // Payment plans CRUD
                'payment_plans.create' => 'Create payment plans',
                'payment_plans.view' => 'View payment plans',
                'payment_plans.update' => 'Update payment plans',
                'payment_plans.delete' => 'Delete payment plans',
                'payment_plans.manage' => 'Manage payment plans',

                // Buyers
                'buyers.view' => 'View buyers',
                'buyers.update' => 'Update buyer records',
                'buyers.manage' => 'Manage buyers',

                // Reservations CRUD
                'reservations.create' => 'Create reservations',
                'reservations.view' => 'View reservations',
                'reservations.update' => 'Update reservation status',
                'reservations.cancel' => 'Cancel reservations',
                'reservations.manage' => 'Manage reservations',

                // Documents
                'documents.create' => 'Upload project documents',
                'documents.view' => 'View documents',
                'documents.update' => 'Update documents',

                // Gallery
                'photos.create' => 'Upload photos',
                'photos.delete' => 'Delete photos',

                // Inquiries
                'inquiries.view' => 'View inquiries',
                'inquiries.update' => 'Respond to inquiries',

                // Messages
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',
            ],
        ],

        // ── 11. Property Manager ────────────────────────────────────────
        [
            'key' => 'property_manager',
            'label' => 'Property Manager',
            'description' => 'Manages buildings, units, tenants, guests, bookings, maintenance, and housekeeping.',
            'role' => 'property_manager',
            'legacy_roles' => [],
            'portal' => 'role.portal',
            'permissions' => [
                // Buildings CRUD
                'buildings.create' => 'Create buildings',
                'buildings.view' => 'View buildings',
                'buildings.update' => 'Update buildings',
                'buildings.delete' => 'Delete buildings',

                // Units CRUD
                'units.create' => 'Create units',
                'units.view' => 'View units',
                'units.update' => 'Update units',
                'units.delete' => 'Delete units',

                // Tenants CRUD
                'tenants.create' => 'Add tenants',
                'tenants.view' => 'View tenants',
                'tenants.update' => 'Update tenant records',
                'tenants.delete' => 'Remove tenants',

                // Guests
                'guests.view' => 'View guests',
                'guests.update' => 'Update guest records',
                'guests.manage' => 'Manage guests',

                // Bookings CRUD
                'bookings.create' => 'Create bookings',
                'bookings.view' => 'View bookings',
                'bookings.update' => 'Update booking status',
                'bookings.cancel' => 'Cancel bookings',
                'bookings.update' => 'Manage bookings',

                // Maintenance CRUD
                'maintenance.create' => 'Create maintenance tickets',
                'maintenance.view' => 'View maintenance tickets',
                'maintenance.update' => 'Update ticket status',
                'maintenance.delete' => 'Close tickets',
                'maintenance.manage' => 'Manage maintenance',

                // Housekeeping CRUD
                'housekeeping.create' => 'Create cleaning tasks',
                'housekeeping.view' => 'View cleaning tasks',
                'housekeeping.update' => 'Update cleaning status',
                'housekeeping.manage' => 'Manage housekeeping',

                // Leases
                'leases.view' => 'View leases',
                'leases.update' => 'Update lease records',

                // Rent
                'rent.view' => 'View rent schedules',
                'rent.update' => 'Record rent payments',

                // Properties
                'properties.view' => 'View managed properties',
                'properties.update' => 'Update property details',

                // Messages
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',
            ],
            'sections' => [
                ['key' => 'buildings_units', 'label' => 'Buildings & Units', 'icon' => '🏢', 'permissions' => ['buildings.view', 'units.view']],
                ['key' => 'guests', 'label' => 'Guests & Tenants', 'icon' => '🧑‍🤝‍🧑', 'permissions' => ['tenants.view', 'guests.view']],
                ['key' => 'bookings', 'label' => 'Bookings', 'icon' => '📅', 'permissions' => ['bookings.view']],
                ['key' => 'maintenance', 'label' => 'Maintenance', 'icon' => '🔧', 'permissions' => ['maintenance.view']],
                ['key' => 'housekeeping', 'label' => 'Housekeeping', 'icon' => '🧹', 'permissions' => ['housekeeping.view']],
            ],
        ],

        // ── 12. Professional Service Provider ───────────────────────────
        [
            'key' => 'service_provider',
            'label' => 'Professional Service Provider',
            'description' => 'Lawyers, surveyors, architects, valuers, contractors, and inspectors: keeps a professional profile, receives job invitations, submits documents, and receives payments.',
            'role' => 'service_provider',
            'legacy_roles' => ['professional'],
            'portal' => 'role.portal',
            'permissions' => [
                // Profile CRUD
                'services.profile_create' => 'Create professional profile',
                'services.profile_view' => 'View professional profile',
                'services.profile_update' => 'Update professional profile',
                'services.profile_manage' => 'Manage professional profile',

                // Jobs
                'services.jobs_view' => 'View job invitations',
                'services.jobs_accept' => 'Accept job invitations',
                'services.jobs_decline' => 'Decline job invitations',
                'services.jobs_receive' => 'Receive job invitations',

                // Documents
                'services.documents_create' => 'Upload professional documents',
                'services.documents_view' => 'View professional documents',
                'services.documents_update' => 'Update professional documents',
                'services.documents_submit' => 'Submit professional documents',

                // Payments
                'services.payments_view' => 'View payment history',
                'services.payments_receive' => 'Receive payments',

                // Messages
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',

                // Reviews
                'reviews.view' => 'View reviews',
            ],
            'sections' => [
                ['key' => 'profile', 'label' => 'Professional Profile', 'icon' => '👤', 'permissions' => ['services.profile_manage']],
                ['key' => 'jobs', 'label' => 'Job Invitations', 'icon' => '🧰', 'permissions' => ['services.jobs_receive']],
                ['key' => 'documents', 'label' => 'Documents', 'icon' => '📎', 'permissions' => ['services.documents_submit']],
                ['key' => 'payments', 'label' => 'Payments', 'icon' => '💰', 'permissions' => ['services.payments_receive']],
            ],
        ],

        // ── 13. Moderator ───────────────────────────────────────────────
        [
            'key' => 'moderator',
            'label' => 'Moderator',
            'description' => 'Handles listing reviews, reported listings, inappropriate content, suspicious advertisements, review moderation, and fraud detection flags.',
            'role' => 'moderator',
            'legacy_roles' => [],
            'portal' => 'role.portal',
            'permissions' => [
                // Listings moderation
                'listings.view' => 'View listings',
                'listings.review' => 'Review listings',
                'listings.approve' => 'Approve listings',
                'listings.reject' => 'Reject listings',
                'listings.suspend' => 'Suspend listings',

                // Reports CRUD
                'reports.view' => 'View reported listings',
                'reports.update' => 'Update report status',
                'reports.resolve' => 'Resolve reports',
                'reports.handle' => 'Handle reported listings',

                // Content moderation
                'content.view' => 'View content queue',
                'content.moderate' => 'Moderate inappropriate content',
                'content.remove' => 'Remove content',

                // Review moderation
                'reviews.view' => 'View reviews',
                'reviews.moderate' => 'Moderate reviews',
                'reviews.hide' => 'Hide reviews',
                'reviews.restore' => 'Restore hidden reviews',

                // Suspicious ads
                'ads.view' => 'View advertisements',
                'ads.suspicious_review' => 'Review suspicious advertisements',
                'ads.suspend' => 'Suspend advertisements',

                // Fraud
                'fraud.view' => 'View fraud flags',
                'fraud.review' => 'Review fraud reports',

                // Properties
                'properties.view' => 'View properties',
                'properties.suspend' => 'Suspend properties',
                'properties.merge' => 'Merge duplicate properties',
            ],
            'sections' => [
                ['key' => 'listings_review', 'label' => 'Listing Reviews', 'icon' => '📋', 'permissions' => ['listings.review']],
                ['key' => 'reports', 'label' => 'Reported Listings', 'icon' => '🚨', 'permissions' => ['reports.handle']],
                ['key' => 'content', 'label' => 'Content Moderation', 'icon' => '🧹', 'permissions' => ['content.moderate']],
                ['key' => 'suspicious_ads', 'label' => 'Suspicious Ads', 'icon' => '🚩', 'permissions' => ['ads.suspicious_review']],
                ['key' => 'duplicates', 'label' => 'Duplicate Detection & Merge', 'icon' => '👥', 'permissions' => ['properties.merge']],
            ],
        ],

        // ── 14. Verification Officer ────────────────────────────────────
        [
            'key' => 'verification_officer',
            'label' => 'Verification Officer',
            'description' => 'Handles identity, company, and developer verification, reviews property documents, and manages verification workflows.',
            'role' => 'verification_officer',
            'legacy_roles' => [],
            'portal' => 'role.portal',
            'permissions' => [
                // Identity verification
                'verifications.identity' => 'Verify identities',
                'verifications.identity_view' => 'View identity requests',
                'verifications.identity_approve' => 'Approve identity verifications',
                'verifications.identity_reject' => 'Reject identity verifications',

                // Company verification
                'verifications.company' => 'Verify companies',
                'verifications.company_view' => 'View company verification requests',
                'verifications.company_approve' => 'Approve company verifications',
                'verifications.company_reject' => 'Reject company verifications',

                // Developer verification
                'verifications.developer' => 'Verify developers',
                'verifications.developer_view' => 'View developer requests',
                'verifications.developer_approve' => 'Approve developer verifications',
                'verifications.developer_reject' => 'Reject developer verifications',

                // Property documents
                'documents.view' => 'View property documents',
                'documents.review' => 'Review property documents',
                'documents.approve' => 'Approve documents',
                'documents.reject' => 'Reject documents',
                'documents.review_property' => 'Review property documents',

                // Verification levels & badges
                'verifications.manage' => 'Manage verification badges and levels',

                // Users
                'users.view' => 'View user profiles',
            ],
            'sections' => [
                ['key' => 'property_documents', 'label' => 'Property Documents', 'icon' => '📎', 'permissions' => ['documents.review_property']],
                ['key' => 'identity_verifications', 'label' => 'Identity Verifications', 'icon' => '🪪', 'permissions' => ['verifications.identity']],
                ['key' => 'trust_levels', 'label' => 'Trust Levels & Badges', 'icon' => '🛡️', 'permissions' => ['verifications.manage']],
                ['key' => 'company_verifications', 'label' => 'Company Verifications', 'icon' => '🏢', 'permissions' => ['verifications.company']],
                ['key' => 'developer_verifications', 'label' => 'Developer Verifications', 'icon' => '🏗️', 'permissions' => ['verifications.developer']],
            ],
        ],

        // ── 15. Verification Company ────────────────────────────────────
        [
            'key' => 'verification_company',
            'label' => 'Verification Company',
            'description' => 'Licensed verification firms: accept property document verification jobs, certify ownership and boundary records, submit verification reports, and bill clients.',
            'role' => 'verification_company',
            'legacy_roles' => [],
            'portal' => 'verification_company.portal',
            'permissions' => [
                // Verification jobs CRUD
                'verification_jobs.view' => 'View verification jobs',
                'verification_jobs.accept' => 'Accept verification jobs',
                'verification_jobs.update' => 'Update job status',
                'verification_jobs.complete' => 'Complete verification jobs',
                'verification_jobs.manage' => 'Manage verification jobs',

                // Property documents
                'property_documents.view' => 'View property documents',
                'property_documents.verify' => 'Verify property documents',
                'property_documents.annotate' => 'Annotate documents',

                // Reports
                'verification_reports.create' => 'Create verification reports',
                'verification_reports.view' => 'View verification reports',
                'verification_reports.update' => 'Update verification reports',
                'verification_reports.submit' => 'Submit verification reports',

                // Payments
                'verification_payments.view' => 'View verification payments',
                'verification_payments.create' => 'Create payment invoices',
                'verification_payments.receive' => 'Receive verification payments',

                // Profile
                'services.profile_view' => 'View firm profile',
                'services.profile_update' => 'Update firm profile',

                // Messages
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',
            ],
            'sections' => [
                ['key' => 'jobs', 'label' => 'Verification Jobs', 'icon' => '🧾', 'permissions' => ['verification_jobs.manage', 'property_documents.verify']],
                ['key' => 'certifications', 'label' => 'Property Certifications', 'icon' => '✅', 'permissions' => ['verification_jobs.manage']],
                ['key' => 'reports', 'label' => 'Submitted Reports', 'icon' => '📄', 'permissions' => ['verification_reports.submit']],
                ['key' => 'payments', 'label' => 'Verification Payments', 'icon' => '💰', 'permissions' => ['verification_payments.receive']],
                ['key' => 'profile', 'label' => 'Firm Profile', 'icon' => '👤', 'permissions' => ['services.profile_view']],
            ],
        ],

        // ── 16. Finance Officer ─────────────────────────────────────────
        [
            'key' => 'finance_officer',
            'label' => 'Finance Officer',
            'description' => 'Handles payments, refunds, reconciliation, invoices, subscriptions, commissions, payouts, and financial ledger management.',
            'role' => 'finance_officer',
            'legacy_roles' => [],
            'portal' => 'finance.index',
            'permissions' => [
                // Payments CRUD
                'payments.view' => 'View payments',
                'payments.create' => 'Record payments',
                'payments.update' => 'Update payment status',
                'payments.verify' => 'Verify payments',
                'payments.reject' => 'Reject payments',
                'payments.manage' => 'Manage payments',

                // Refunds
                'refunds.view' => 'View refunds',
                'refunds.create' => 'Process refunds',
                'refunds.approve' => 'Approve refunds',
                'refunds.manage' => 'Handle refunds',

                // Reconciliation
                'reconciliation.view' => 'View reconciliation',
                'reconciliation.manage' => 'Reconcile accounts',

                // Invoices CRUD
                'invoices.create' => 'Create invoices',
                'invoices.view' => 'View invoices',
                'invoices.update' => 'Update invoices',
                'invoices.manage' => 'Manage invoices',

                // Subscriptions
                'subscriptions.view' => 'View subscriptions',
                'subscriptions.create' => 'Create subscriptions',
                'subscriptions.update' => 'Update subscriptions',
                'subscriptions.manage' => 'Manage subscriptions',

                // Commissions CRUD
                'commissions.view' => 'View commissions',
                'commissions.create' => 'Create commissions',
                'commissions.update' => 'Update commissions',
                'commissions.settle' => 'Settle commissions',
                'commissions.manage' => 'Manage commissions',

                // Payouts
                'payouts.view' => 'View payouts',
                'payouts.create' => 'Process payouts',
                'payouts.update' => 'Update payout status',

                // Ledger
                'ledger.view' => 'View financial ledger',
                'ledger.create' => 'Create ledger entries',

                // Reports
                'reports.view' => 'View financial reports',
                'reports.create' => 'Generate financial reports',
                'reports.export' => 'Export financial data',

                // Tax
                'tax.view' => 'View tax records',
                'tax.manage' => 'Manage tax configuration',
            ],
            'sections' => [
                ['key' => 'ledger', 'label' => 'Financial Ledger', 'icon' => '📒', 'permissions' => ['ledger.view']],
                ['key' => 'refunds', 'label' => 'Refunds & Adjustments', 'icon' => '↩️', 'permissions' => ['refunds.manage']],
                ['key' => 'invoices', 'label' => 'Invoices & Billing', 'icon' => '🧾', 'permissions' => ['invoices.manage']],
                ['key' => 'subscriptions', 'label' => 'Subscriptions', 'icon' => '💳', 'permissions' => ['subscriptions.manage']],
                ['key' => 'payouts', 'label' => 'Payouts & Transfers', 'icon' => '💸', 'permissions' => ['payouts.view']],
                ['key' => 'commissions', 'label' => 'Commissions', 'icon' => '🤝', 'permissions' => ['commissions.view']],
                ['key' => 'reconciliation', 'label' => 'Reconciliation & Shifts', 'icon' => '⚖️', 'permissions' => ['reconciliation.view']],
            ],
        ],

        // ── 17. Customer Support ────────────────────────────────────────
        [
            'key' => 'customer_support',
            'label' => 'Customer Support',
            'description' => 'Handles user tickets, payment issues, disputes, technical problems, and user assistance.',
            'role' => 'customer_support',
            'legacy_roles' => ['receptionist'],
            'portal' => 'role.portal',
            'permissions' => [
                // Tickets CRUD
                'tickets.create' => 'Create tickets',
                'tickets.view' => 'View tickets',
                'tickets.update' => 'Update ticket status',
                'tickets.close' => 'Close tickets',
                'tickets.manage' => 'Handle user tickets',

                // Payment issues
                'payment_issues.view' => 'View payment issues',
                'payment_issues.handle' => 'Handle payment issues',
                'payment_issues.escalate' => 'Escalate payment issues',

                // Disputes CRUD
                'disputes.view' => 'View disputes',
                'disputes.create' => 'Create disputes',
                'disputes.update' => 'Update dispute status',
                'disputes.resolve' => 'Resolve disputes',
                'disputes.handle' => 'Handle disputes',

                // Technical support
                'technical_support.view' => 'View technical issues',
                'technical_support.handle' => 'Handle technical problems',
                'technical_support.escalate' => 'Escalate technical issues',

                // Users
                'users.view' => 'View user profiles',
                'users.assist' => 'Assist users',

                // Inquiries
                'inquiries.view' => 'View inquiries',
                'inquiries.update' => 'Respond to inquiries',

                // Messages
                'messages.create' => 'Send messages',
                'messages.view' => 'View messages',
            ],
            'sections' => [
                ['key' => 'tickets', 'label' => 'User Tickets', 'icon' => '🎫', 'permissions' => ['tickets.manage']],
                ['key' => 'payment_issues', 'label' => 'Payment Issues', 'icon' => '💳', 'permissions' => ['payment_issues.handle']],
                ['key' => 'disputes', 'label' => 'Disputes', 'icon' => '⚖️', 'permissions' => ['disputes.handle']],
                ['key' => 'technical_support', 'label' => 'Technical Problems', 'icon' => '🛠️', 'permissions' => ['technical_support.handle']],
            ],
        ],

        // ── Administrator (platform management) ─────────────────────────
        [
            'key' => 'administrator',
            'label' => 'Administrator',
            'description' => 'Full platform management: settings, users, roles, content, analytics, reports, audit trail, categories, and all CRUD operations.',
            'role' => 'admin',
            'legacy_roles' => [],
            'portal' => 'dashboard',
            'permissions' => [
                // Platform management
                'platform.settings_manage' => 'Manage platform settings',
                'platform.users_manage' => 'Manage users',
                'platform.roles_manage' => 'Manage roles & permissions',
                'platform.content_manage' => 'Manage platform content',
                'platform.analytics_view' => 'View analytics',
                'platform.reports_view' => 'View platform reports',
                'platform.audit_view' => 'View the activity audit trail',

                // Properties CRUD
                'properties.create' => 'Create properties',
                'properties.view' => 'View properties',
                'properties.update' => 'Update properties',
                'properties.delete' => 'Delete properties',
                'properties.approve' => 'Approve properties',
                'properties.verify' => 'Verify properties',
                'properties.suspend' => 'Suspend properties',
                'properties.archive' => 'Archive properties',
                'properties.feature' => 'Feature properties',
                'properties.merge' => 'Merge duplicate properties',

                // Listings CRUD
                'listings.create' => 'Create listings',
                'listings.view' => 'View listings',
                'listings.update' => 'Update listings',
                'listings.delete' => 'Delete listings',
                'listings.approve' => 'Approve listings',
                'listings.reject' => 'Reject listings',
                'listings.suspend' => 'Suspend listings',
                'listings.publish' => 'Publish listings',

                // Users CRUD
                'users.create' => 'Create users',
                'users.view' => 'View users',
                'users.update' => 'Update users',
                'users.delete' => 'Delete users',
                'users.suspend' => 'Suspend users',

                // Categories CRUD
                'categories.create' => 'Create categories',
                'categories.view' => 'View categories',
                'categories.update' => 'Update categories',
                'categories.delete' => 'Delete categories',

                // Verifications
                'verifications.manage' => 'Manage all verifications',

                // Documents
                'documents.view' => 'View all documents',
                'documents.manage' => 'Manage all documents',

                // Bookings
                'bookings.view' => 'View all bookings',
                'bookings.update' => 'Manage all bookings',

                // Payments
                // Moderation
                'moderation.manage' => 'Manage moderation queue',
            ],
        ],

        // ── Cleaner / Housekeeping ───────────────────────────────────────
        [
            'key' => 'cleaner',
            'label' => 'Cleaner / Housekeeping',
            'description' => 'Staff managing room turnarounds, cleaning checklists, inspections, and guest readiness.',
            'role' => 'cleaner',
            'legacy_roles' => ['housekeeping'],
            'portal' => 'cleaner.dashboard',
            'permissions' => [
                'cleaning.view' => 'View assigned cleaning tasks',
                'cleaning.update' => 'Update task status and turnaround checklist',
                'housekeeping.view' => 'View housekeeping queue',
                'dashboard.view' => 'Access dedicated cleaner dashboard',
            ],
        ],

        // ── Investor ────────────────────────────────────────────────────
        [
            'key' => 'investor',
            'label' => 'Investor',
            'description' => 'Portfolio tracking, yield estimations, rental income monitoring, and Ethiopian property market indicators.',
            'role' => 'investor',
            'legacy_roles' => [],
            'portal' => 'investor.dashboard',
            'permissions' => [
                'properties.view' => 'View properties and market listings',
                'portfolio.view' => 'View investor portfolios',
                'portfolio.create' => 'Add properties to investor portfolio',
                'yields.view' => 'View yield estimations & indicators',
                'dashboard.view' => 'Access dedicated investor dashboard',
            ],
        ],

        // ── Campaign Officer ───────────────────────────────────────────
        [
            'key' => 'campaign_officer',
            'label' => 'Campaign Officer',
            'description' => 'Promotions and marketing review desk: editorial review of promotional campaigns, diaspora investor showcases, open house incentives, and lead distribution.',
            'role' => 'campaign_officer',
            'legacy_roles' => [],
            'portal' => 'campaign.dashboard',
            'permissions' => [
                'campaigns.view' => 'View promotional campaigns',
                'campaigns.review' => 'Review and moderate campaigns',
                'campaigns.approve' => 'Approve or reject campaigns',
                'campaigns.manage' => 'Manage marketing budgets & schedules',
                'campaigns.leads' => 'View campaign leads and inquiries',
                'dashboard.view' => 'Access dedicated campaign dashboard',
            ],
        ],

        /*
         * Reserved role for demo-data counterparties (the faker users created by
         * factories: a buyer who made an offer, a landlord on a lease, a guest
         * who booked a stay). It is deliberately NOT an operator persona: no
         * permissions, no portal, and exempt from the one-account-per-role
         * rule in users:cleanup. Without it those rows default to a persona
         * role and break the "exactly one account per role" invariant.
         */
        [
            'key' => 'contact',
            'label' => 'Contact (demo data)',
            'description' => 'Non-persona counterparties used by seeders and factories. No permissions and no dashboard access.',
            'role' => 'contact',
            'portal' => null,
            'permissions' => [],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Assignable Roles
    |--------------------------------------------------------------------------
    | Role values the admin user-management form may assign to accounts.
    */
    'assignable_roles' => [
        'admin',
        'guest',
        'agent',
        'company',
        'finance_officer',
        'host',
        'buyer',
        'tenant',
        'traveler',
        'property_owner',
        'landlord',
        'developer',
        'property_manager',
        'service_provider',
        'moderator',
        'verification_officer',
        'verification_company',
        'customer_support',
        'cleaner',
        'investor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Persona Portal Routing
    |--------------------------------------------------------------------------
    | Role values that land on the shared persona portal (personas without a
    | dedicated dashboard yet). All other roles keep their existing portal.
    */
    'generic_portal_roles' => [
        'property_owner',
        'landlord',
        'property_manager',
        'service_provider',
        'moderator',
        'verification_officer',
        'customer_support',
    ],

    /*
    |--------------------------------------------------------------------------
    | Persona Portal Quick Links
    |--------------------------------------------------------------------------
    | Permission key => [label, route name] shown as action buttons on cards
    | in the persona portal when the user holds that permission.
    */
    'portal_links' => [
        'properties.search' => ['label' => 'Explore Properties', 'route' => 'properties.index'],
        'properties.browse' => ['label' => 'Browse Listings', 'route' => 'properties.index'],
        'properties.compare' => ['label' => 'Open Compare Matrix', 'route' => 'properties.compare'],
        'stays.search' => ['label' => 'Search Short Stays', 'route' => 'properties.index'],
        'sellers.contact' => ['label' => 'Browse Agents', 'route' => 'properties.index'],
        'reports.view' => ['label' => 'Open Reports Desk', 'route' => 'company.dashboard'],
    ],
];
