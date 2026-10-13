
SOFTWARE REQUIREMENTS SPECIFICATION (SRS)
Ethiopian Integrated Real Estate Marketplace, Short-Stay, Property Management & Development Platform
Document Version:
1.0
System Type:
National Real Estate Digital Marketplace
Target Market:
Ethiopia
Primary Platforms:
Web, Android, REST API
Primary Languages:
English, Amharic, Afaan Oromo
Currency:
Ethiopian Birr (ETB)
Calendar:
Ethiopian Calendar with Gregorian interoperability
Architecture:
Modular, API-first, GIS-enabled
Document Status:
Comprehensive Product Requirements Baseline

1. EXECUTIVE SUMMARY
The proposed system is a full Ethiopian real-estate digital ecosystem, not merely a classified-advertisement website.
The platform combines the core marketplace functionality commonly associated with Jiji-style classified property listings with Airbnb-style short-term accommodation and booking, while adding professional capabilities for:
    • houses;
    • villas;
    • apartments;
    • condominiums;
    • land;
    • commercial properties;
    • offices;
    • shops;
    • warehouses;
    • industrial properties;
    • agricultural properties;
    • rental properties;
    • lease properties;
    • hotels;
    • guesthouses;
    • serviced apartments;
    • short-stay properties;
    • new real-estate developments;
    • developer projects;
    • property management;
    • agents and brokers;
    • property owners;
    • tenants;
    • buyers;
    • guests;
    • property documentation;
    • GIS;
    • verification;
    • offers and negotiations;
    • reservations;
    • booking;
    • payments;
    • CRM;
    • maintenance;
    • reviews;
    • analytics;
    • market intelligence.
The fundamental architecture is:
                         ETHIOPIAN REAL ESTATE ECOSYSTEM
                                      │
                               ┌──────▼──────┐
                               │ PROPERTY    │
                               │ CORE        │
                               └──────┬──────┘
                                      │
             ┌────────────────────────┼─────────────────────────┐
             │                        │                         │
             ▼                        ▼                         ▼
          MARKETPLACE              STAYS                   DEVELOPMENT
             │                        │                         │
        ┌────┼────┐              ┌────┼────┐              ┌─────┼─────┐
        │    │    │              │    │    │              │     │     │
       Buy  Rent Lease         Book Host Guest          Projects Units Sales
        │    │    │              │    │    │              │     │     │
        └────┴────┘              └────┴────┘              └─────┴─────┘
             │                        │                         │
             └────────────────────────┼─────────────────────────┘
                                      │
                            ┌─────────▼─────────┐
                            │ TRANSACTION CORE  │
                            └─────────┬─────────┘
                                      │
                  ┌───────────────────┼────────────────────┐
                  ▼                   ▼                    ▼
               Payments           Documents              CRM
                  │                   │                    │
                  └───────────────────┼────────────────────┘
                                      ▼
                              GIS / ANALYTICS
2. PRODUCT VISION
The platform shall become a nationwide digital marketplace where a person can:
Find a home, land, apartment, villa, office, commercial property or accommodation; view it on a map; verify the available information; contact the owner or agent; arrange a viewing; make an offer or booking; complete supported payments; manage the resulting rental/stay/transaction; and retain the associated digital records.
The system should support the complete property lifecycle:
Property Creation
      ↓
Documentation
      ↓
Verification
      ↓
Listing
      ↓
Discovery
      ↓
Inquiry
      ↓
Viewing / Booking
      ↓
Offer / Reservation
      ↓
Payment
      ↓
Agreement
      ↓
Transaction / Stay
      ↓
Management
      ↓
Reviews / Analytics
3. CORE PRODUCT PRINCIPLE
3.1 Property is the master entity
The most important architectural principle is:
A Property is not the same thing as a Listing.
A property is the underlying real-world asset.
A listing is a commercial advertisement or transaction opportunity associated with that property.
For example:
PROPERTY
Modern Villa – Bole
Property ID: ETH-AA-BOLE-00001245
          │
          ├── Sale Listing
          │     ETB 25,000,000
          │
          ├── Long-Term Rental Listing
          │     ETB 80,000/month
          │
          └── Short-Stay Listing
                ETB 8,500/night
Business rules determine whether multiple listing modes may be active simultaneously.
4. OBJECTIVES
The platform shall:
    1. Create a trusted property marketplace.
    2. Digitize property discovery.
    3. Support Ethiopian locations and administrative structures.
    4. Support property sales.
    5. Support property rentals.
    6. Support land listings.
    7. Support leases.
    8. Support Airbnb-style short stays.
    9. Support developer projects.
    10. Support property management.
    11. Support real-estate agents.
    12. Support professional property companies.
    13. Support GIS-based property discovery.
    14. Support property documentation.
    15. Support verification workflows.
    16. Support offers and negotiations.
    17. Support booking and reservations.
    18. Support payments.
    19. Support financial records.
    20. Support reviews and reputation.
    21. Reduce fraudulent listings.
    22. Provide property market analytics.
    23. Provide mobile access.
    24. Support national expansion.
5. TARGET USERS
5.1 Guest
Can:
    • browse;
    • search;
    • view property;
    • view map;
    • view photos;
    • search short stays.
Registration is required for actions such as booking or messaging.
5.2 Buyer
Can:
    • search properties;
    • save properties;
    • compare;
    • contact sellers;
    • request viewing;
    • make offers;
    • negotiate;
    • reserve;
    • upload documents;
    • track transactions.
5.3 Tenant
Can:
    • find rentals;
    • request viewing;
    • communicate with landlords;
    • submit applications;
    • sign/receive supported agreements;
    • track rent;
    • submit maintenance requests.
5.4 Guest / Traveler
Can:
    • search stays;
    • choose dates;
    • select number of guests;
    • reserve;
    • pay;
    • check in;
    • check out;
    • review host.
5.5 Property Owner
Can:
    • register property;
    • publish listings;
    • upload photos;
    • provide documents;
    • manage inquiries;
    • receive offers;
    • schedule viewings;
    • manage bookings.
5.6 Host
Can:
    • create short-stay listings;
    • define availability;
    • define prices;
    • define house rules;
    • accept bookings;
    • communicate with guests;
    • manage check-in;
    • manage cleaning;
    • receive reviews.
5.7 Landlord
Can manage:
    • properties;
    • tenants;
    • leases;
    • rent;
    • deposits;
    • maintenance.
5.8 Agent
Can manage:
    • properties;
    • clients;
    • leads;
    • viewings;
    • offers;
    • commissions;
    • CRM.
5.9 Real Estate Company
Can manage:
    • branches;
    • agents;
    • listings;
    • properties;
    • clients;
    • transactions;
    • reports.
5.10 Developer
Can manage:
    • projects;
    • buildings;
    • units;
    • construction progress;
    • pricing;
    • payment plans;
    • buyers;
    • reservations.
5.11 Property Manager
Can manage:
    • buildings;
    • units;
    • tenants;
    • guests;
    • bookings;
    • maintenance;
    • housekeeping.
5.12 Professional Service Provider
Examples:
    • lawyer;
    • surveyor;
    • architect;
    • valuer;
    • contractor;
    • inspector.
5.13 Moderator
Handles:
    • listing review;
    • reported listings;
    • inappropriate content;
    • suspicious advertisements.
5.14 Verification Officer
Handles:
    • identity verification;
    • company verification;
    • developer verification;
    • property-document review.
5.15 Finance Officer
Handles:
    • payments;
    • refunds;
    • reconciliation;
    • invoices;
    • subscriptions;
    • commissions.
5.16 Customer Support
Handles:
    • user tickets;
    • payment issues;
    • disputes;
    • technical problems.
5.17 Administrator
Has configurable platform-management permissions.
6. PROPERTY CATEGORIES
6.1 Residential
    • House
    • Villa
    • Apartment
    • Condominium
    • Townhouse
    • Duplex
    • Penthouse
    • Studio
    • Guesthouse
    • Residential compound
6.2 Land
    • Residential land
    • Commercial land
    • Industrial land
    • Agricultural land
    • Development land
6.3 Commercial
    • Office
    • Shop
    • Mall
    • Hotel
    • Restaurant
    • Warehouse
    • Commercial building
    • Business center
6.4 Industrial
    • Factory
    • Industrial compound
    • Manufacturing facility
    • Storage facility
6.5 Hospitality
    • Hotel
    • Guesthouse
    • Lodge
    • Resort
    • Serviced apartment
    • Vacation villa
    • Short-stay apartment
6.6 Special
    • Mixed-use
    • Investment property
    • Development project
    • Event center
Categories must be database-configurable.
7. LISTING TYPES
The system shall support:
    • For Sale
    • For Rent
    • For Lease
    • Short Stay
    • Long Stay
    • New Development
    • Pre-Sale
    • Off-Plan
    • Auction
    • Investment Opportunity
    • Joint Venture Opportunity
8. TRANSACTION MODES
The platform shall have five primary marketplace modes:
BUY	For purchasing property.
RENT	For long-term rental.
LEASE	For land and other lease arrangements.
STAY	For nightly/weekly/monthly accommodation.
NEW PROJECTS	For developer projects.
9. PROPERTY IDENTIFICATION
Each property shall have a unique internal ID.
Example:
ETH-AA-BOLE-RES-00001245
Components may represent:
    • country;
    • city;
    • area;
    • property category;
    • sequence.
The identifier format shall be configurable.
10. PROPERTY MASTER DATA
Each property shall support:
Basic information
    • Property ID
    • title
    • description
    • property type
    • category
    • status
    • ownership/management relationship
    • creation date
    • last updated date
Physical information
    • land area;
    • building area;
    • bedrooms;
    • bathrooms;
    • floors;
    • parking;
    • balconies;
    • rooms;
    • compound size.
Construction
    • construction year;
    • condition;
    • construction status;
    • construction material;
    • developer;
    • completion percentage.
Utilities
    • electricity;
    • water;
    • sewer;
    • internet;
    • generator;
    • solar;
    • security.
11. LOCATION CORE
Location hierarchy shall support:
Country
  ↓
Region
  ↓
Zone
  ↓
City
  ↓
Sub-city
  ↓
Woreda
  ↓
Kebele
  ↓
Neighborhood
This structure must be configurable because administrative structures may change.
12. GIS MODULE
The system shall support:
    • latitude;
    • longitude;
    • point location;
    • property boundary;
    • parcel polygon where authorized;
    • neighborhood polygons;
    • city boundaries;
    • nearby infrastructure;
    • roads;
    • landmarks.
13. MAP SEARCH
Users shall be able to:
    • search by location;
    • search within radius;
    • search visible map area;
    • draw a search area;
    • click map markers;
    • view property clusters.
14. MAP/LIST SPLIT VIEW
Desktop interface:
┌────────────────────────┬──────────────────────────┐
│ PROPERTY RESULTS       │                          │
│                        │                          │
│ Villa – Bole           │           MAP            │
│ ETB 25M                │                          │
│                        │      🏠     🏠           │
│ Apartment – Yeka       │                          │
│ ETB 8M                 │          🏠              │
│                        │                          │
└────────────────────────┴──────────────────────────┘
15. SEARCH ENGINE
Search must support natural and structured queries.
Example:
4-bedroom villa in Bole under 30 million.
The system translates this into:
Property = Villa
Location = Bole
Bedrooms >= 4
Price <= 30,000,000
Purpose = Sale
16. SEARCH FILTERS
Location
    • region;
    • city;
    • sub-city;
    • woreda;
    • kebele;
    • neighborhood;
    • map area.
Price
    • minimum;
    • maximum.
Property
    • type;
    • area;
    • bedrooms;
    • bathrooms;
    • floor;
    • parking.
Amenities
    • Wi-Fi;
    • pool;
    • garden;
    • security;
    • generator;
    • elevator;
    • furnished;
    • balcony.
Verification
    • verified seller;
    • verified company;
    • verified developer;
    • documents submitted.
17. SEARCH SORTING
    • Relevance
    • Newest
    • Lowest price
    • Highest price
    • Largest
    • Smallest
    • Most viewed
    • Most saved
    • Price per m²
18. PROPERTY DETAIL PAGE
The property page shall contain:
    1. Gallery
    2. Video
    3. Property title
    4. Price
    5. Property type
    6. Location
    7. Key specifications
    8. Description
    9. Amenities
    10. Map
    11. Seller/host
    12. Verification status
    13. Documents where authorized
    14. Contact
    15. Message
    16. Viewing
    17. Booking where applicable
    18. Offer where applicable
    19. Similar properties
    20. Reviews where applicable
19. MEDIA MANAGEMENT
Supported:
    • JPG
    • PNG
    • WebP
    • AVIF
    • video;
    • 360° imagery.
The system shall automatically:
    • resize;
    • compress;
    • create thumbnails;
    • generate optimized formats;
    • apply optional watermark.
20. VIDEO
Property owners may upload:
    • walkthrough;
    • drone video;
    • development progress;
    • project presentation.
21. VIRTUAL TOUR
Future support:
    • 360° photos;
    • room navigation;
    • virtual property tours.
22. VERIFICATION SYSTEM
The platform shall have multiple verification levels.
Level 0 — Unverified	No verification.
Level 1 — Identity Verified	Identity/phone verification completed.
Level 2 — Seller/Company Verified	Seller or organization verification completed.
Level 3 — Documents Submitted	Supporting property documents submitted.
Level 4 — Professional Review	Documents reviewed according to platform procedure.
Level 5 — Transaction Ready	All platform-required transaction checks completed.
These statuses must not be represented as government ownership certification unless the platform is officially authorized to make that determination.
23. VERIFICATION BADGES
Examples:
    • Verified Seller
    • Verified Host
    • Verified Company
    • Verified Developer
    • Documents Submitted
    • Professionally Reviewed
Each badge must have a visible explanation.
24. SELLER VERIFICATION
Fields may include:
    • legal name;
    • phone;
    • email;
    • identity information;
    • profile photo;
    • verification documents;
    • status;
    • verification history.
25. COMPANY VERIFICATION
Company information:
    • legal name;
    • trade name;
    • registration;
    • license;
    • address;
    • authorized representative;
    • documents.
26. DEVELOPER VERIFICATION
Developer profile may include:
    • legal company information;
    • registration;
    • applicable licensing information;
    • project documentation;
    • project location;
    • project status;
    • responsible representatives.
27. DOCUMENT MANAGEMENT
Documents may include, depending on the property:
    • ownership-related documentation;
    • lease documentation;
    • permits;
    • plans;
    • tax-related documents;
    • developer documents;
    • company documents;
    • contracts;
    • identification.
28. DOCUMENT ACCESS CONTROL
Each document shall have an access level:
Public
Private
Verification-only
Admin-only
Transaction Participants
Sensitive documents must never be public by default.
29. DOCUMENT VERSIONING
Document v1
     ↓
Document v2
     ↓
Document v3
Previous versions remain auditable.
30. LISTING MODERATION
Workflow:
Draft
 ↓
Submitted
 ↓
Automated Validation
 ↓
Moderation
 ↓
Approved
 ↓
Published
Rejected listings:
Rejected
 ↓
Correction
 ↓
Resubmitted
31. FRAUD DETECTION
System shall identify potential:
    • duplicate properties;
    • duplicate photos;
    • duplicate phone numbers;
    • suspicious pricing;
    • fake documents;
    • copied descriptions;
    • suspicious accounts;
    • unusual activity.
32. DUPLICATE DETECTION
Matching factors:
    • coordinates;
    • photos;
    • phone;
    • property area;
    • land size;
    • description;
    • documents;
    • owner information.
33. FAVORITES
Users can:
    • save;
    • remove;
    • organize properties.
Example:
My Saved Properties

Bole
 ├── Villa A
 └── Villa B

Investment
 ├── Land A
 └── Building B
34. COLLECTIONS
Users can create private collections:
    • My Dream Homes
    • Investment
    • Weekend Stays
    • Properties to Visit.
35. PROPERTY COMPARISON
Users can compare:
    • price;
    • area;
    • bedrooms;
    • bathrooms;
    • location;
    • amenities;
    • price/m²;
    • verification;
    • parking.
36. PROPERTY PRICE ENGINE
System calculates:
Price per m²
Example:
Price = 20,000,000 ETB
Land = 300 m²

Price/m² = 66,666.67 ETB
Different property types shall use appropriate area calculations.
37. MARKET ANALYTICS
System may calculate:
    • average asking price;
    • median asking price;
    • average price/m²;
    • rental average;
    • listing volume;
    • price trends;
    • neighborhood trends.
These should be clearly identified as platform-derived market indicators unless based on authoritative transaction data.
38. JIJI-STYLE MARKETPLACE
The platform shall support the standard classified workflow:
Register
 ↓
Post Property
 ↓
Upload Photos
 ↓
Set Price
 ↓
Submit
 ↓
Moderation
 ↓
Publish
 ↓
Receive Inquiry
 ↓
Contact Buyer
39. AIRBNB-STYLE STAY PLATFORM
The system shall separately support:
Search
 ↓
Select Destination
 ↓
Check-in
 ↓
Check-out
 ↓
Guests
 ↓
Availability
 ↓
Property
 ↓
Price Calculation
 ↓
Booking
 ↓
Payment
 ↓
Confirmation
 ↓
Check-in
 ↓
Stay
 ↓
Check-out
 ↓
Review
40. HOST MODEL
A host may be:
    • individual owner;
    • company;
    • property manager;
    • hotel;
    • guesthouse;
    • developer;
    • professional host.
Host profile includes:
    • name;
    • photo;
    • verification;
    • properties;
    • reviews;
    • response rate;
    • response time.
41. SHORT-STAY LISTING
Additional fields:
    • nightly rate;
    • weekend rate;
    • weekly rate;
    • monthly rate;
    • cleaning fee;
    • service fee;
    • deposit;
    • minimum stay;
    • maximum stay;
    • check-in;
    • check-out;
    • maximum guests.
42. GUEST CAPACITY
Fields:
    • maximum guests;
    • adults;
    • children;
    • infants;
    • pets.
43. BED CONFIGURATION
Example:
Bedroom 1
King

Bedroom 2
Queen

Bedroom 3
2 Singles

Living Room
Sofa Bed
44. AMENITIES
Categories:
Internet
    • Wi-Fi
    • Fiber
Kitchen
    • Refrigerator
    • Oven
    • Microwave
    • Cooking equipment
Outdoor
    • Garden
    • Pool
    • Terrace
    • Balcony
Building
    • Elevator
    • Parking
    • Security
Family
    • Baby bed
    • High chair
45. AVAILABILITY CALENDAR
Example:
October

01 Available
02 Available
03 Booked
04 Booked
05 Available
06 Available
The calendar must prevent overlapping confirmed reservations.
46. BOOKING ENGINE
Booking statuses:
Pending
Awaiting Payment
Confirmed
Checked In
Active
Checked Out
Completed
Cancelled
Refunded
Disputed
No Show
47. INSTANT BOOK
Hosts can enable:
Instant Booking
The system automatically confirms if:
    • dates are available;
    • guest meets configured requirements;
    • payment succeeds.
48. REQUEST TO BOOK
Alternative:
Guest Request
      ↓
Host Review
      ↓
Accept / Reject
      ↓
Payment
      ↓
Confirmation
49. TEMPORARY BOOKING HOLD
During payment:
Available
 ↓
Payment Pending
 ↓
Temporary Hold
 ↓
Payment Successful
 ↓
Confirmed
Failed/expired payment releases the inventory.
50. SHORT-STAY PRICE CALCULATION
Example:
5 nights × 8,500 ETB      42,500
Cleaning fee                2,000
Platform fee                2,500
Tax                            XXX
----------------------------------
Total                         XX,XXX
Every component must be separately recorded.
51. DYNAMIC PRICING
Support:
    • weekday;
    • weekend;
    • seasonal;
    • holiday;
    • event;
    • early booking;
    • last-minute;
    • long-stay discount.
52. MINIMUM STAY
Example:
Normal: 2 nights
Holiday: 5 nights
53. CANCELLATION POLICIES
Support configurable policies:
    • flexible;
    • moderate;
    • strict;
    • custom.
The system calculates refunds according to the active policy.
54. GUEST MANAGEMENT
Guest profile:
    • name;
    • phone;
    • email;
    • identity verification status;
    • bookings;
    • reviews;
    • saved properties.
55. HOST MANAGEMENT
Host dashboard:
Properties: 12
Upcoming Bookings: 8
Current Guests: 4
Occupancy: 72%
Revenue: 850,000 ETB
56. HOST CALENDAR
Host can:
    • block dates;
    • unblock dates;
    • add external reservation;
    • modify pricing;
    • modify minimum stay;
    • view reservations.
57. EXTERNAL CALENDAR SYNCHRONIZATION
Support iCal-based synchronization.
Future integrations can support additional booking-channel integrations.
58. CHECK-IN
Guest receives:
    • booking confirmation;
    • property address;
    • check-in instructions;
    • host contact;
    • access instructions.
Future support:
    • QR check-in;
    • smart lock;
    • digital access code.
59. CHECK-OUT
System records:
    • checkout time;
    • property condition;
    • damage reports;
    • completion.
60. HOUSE RULES
Host configures:
    • smoking;
    • pets;
    • parties;
    • visitors;
    • quiet hours;
    • children;
    • events.
61. HOUSE MANUAL
Digital property guide:
    • Wi-Fi;
    • TV;
    • AC;
    • appliances;
    • kitchen;
    • parking;
    • emergency;
    • check-in;
    • checkout.
62. LOCAL GUIDE
Host may provide:
    • restaurants;
    • shops;
    • hospitals;
    • transportation;
    • tourist attractions;
    • cultural sites.
63. HOUSEKEEPING
After checkout:
Checkout
 ↓
Cleaning Task
 ↓
Cleaner Assigned
 ↓
Cleaning
 ↓
Inspection
 ↓
Ready
64. MAINTENANCE
Guests, tenants or property managers can submit:
    • plumbing;
    • electricity;
    • internet;
    • appliance;
    • structural;
    • cleaning;
    • other.
65. MAINTENANCE WORKFLOW
Open
 ↓
Assigned
 ↓
In Progress
 ↓
Waiting
 ↓
Completed
 ↓
Closed
66. SECURITY DEPOSIT
Optional:
Held
Released
Partially Deducted
Disputed
The platform must use an appropriately authorized payment arrangement for any actual custody of funds.
67. DAMAGE CLAIM
Host can submit:
    • description;
    • amount;
    • photos;
    • evidence.
Guest can:
    • accept;
    • dispute.
68. DISPUTE MANAGEMENT
Disputes include:
    • cancellation;
    • refund;
    • property condition;
    • damage;
    • inaccurate listing;
    • host behavior;
    • guest behavior;
    • payment.
Workflow:
Dispute
 ↓
Evidence
 ↓
Review
 ↓
Resolution
 ↓
Closed
69. TWO-WAY REVIEW
Guest → Host
Host → Guest
This creates a reputation ecosystem.
70. REVIEW CRITERIA
Guest:
    • cleanliness;
    • accuracy;
    • communication;
    • location;
    • value;
    • overall experience.
Host:
    • communication;
    • property care;
    • rule compliance.
71. PROPERTY VIEWING
For sales/rentals:
Request Viewing
 ↓
Select Date
 ↓
Select Time
 ↓
Seller/Agent Confirmation
 ↓
Appointment
72. APPOINTMENT STATUS
Requested
Confirmed
Rescheduled
Completed
Cancelled
No-show
73. INQUIRY SYSTEM
Every inquiry receives an ID.
Inquiry
Buyer
Property
Seller
Message
Date
Status
Statuses:
New
Contacted
Viewing
Negotiating
Offer
Closed
Lost
74. MESSAGING
Chat supports:
    • text;
    • photos;
    • documents;
    • booking context;
    • read status;
    • notifications.
75. PHONE PRIVACY
Seller/host can configure:
    • show phone;
    • hide phone;
    • platform messaging only.
76. OFFER MANAGEMENT
Buyer can submit:
Property Price: 25,000,000
Offer: 23,000,000
Message
Expiration
Seller can:
    • accept;
    • reject;
    • counteroffer.
77. NEGOTIATION
Example:
Seller: 25M
Buyer: 22M
Seller: 24M
Buyer: 23M
Seller: 23.5M
All negotiation events are recorded.
78. RESERVATION ENGINE
Applicable to:
    • properties;
    • development units;
    • short stays.
Statuses:
Requested
Pending Payment
Reserved
Expired
Cancelled
Converted
79. TRANSACTION ENGINE
Generic transaction:
TRANSACTION
 ├── Sale
 ├── Rental
 ├── Lease
 ├── Short Stay
 ├── Reservation
 └── Service
80. PAYMENT ENGINE
Payment types:
    • booking;
    • reservation;
    • listing;
    • premium listing;
    • subscription;
    • rent;
    • lease;
    • developer installment;
    • service;
    • commission.
Potential Ethiopian payment integrations can include:
    • Telebirr;
    • CBE Birr;
    • bank transfer;
    • cards;
    • other licensed providers.
Availability depends on API access, agreements and applicable requirements.
81. PAYMENT RECORD
Each payment must have:
Transaction ID
Reference
Customer
Property
Amount
Fee
Tax
Payment Method
Status
Date
Statuses:
Pending
Processing
Successful
Failed
Refunded
Cancelled
82. FINANCIAL LEDGER
The system should maintain auditable financial records for:
    • booking revenue;
    • host payable;
    • platform fees;
    • refunds;
    • taxes;
    • commissions;
    • payment processing fees.
83. HOST PAYOUT
Where legally and commercially supported:
Booking Revenue
 ↓
Platform Fee
 ↓
Applicable Adjustments
 ↓
Host Payable
 ↓
Payout
84. RENTAL MANAGEMENT
Long-term rental workflow:
Property
 ↓
Listing
 ↓
Tenant
 ↓
Application
 ↓
Lease
 ↓
Rent Schedule
 ↓
Payment
 ↓
Maintenance
85. TENANT MANAGEMENT
Tenant profile:
    • identity;
    • contact;
    • emergency contact;
    • rental history;
    • documents.
86. LEASE MANAGEMENT
Lease record:
    • landlord;
    • tenant;
    • property;
    • start date;
    • end date;
    • rent;
    • deposit;
    • payment schedule;
    • terms.
87. RENT SCHEDULE
Example:
January     Paid
February    Paid
March       Pending
April       Upcoming
88. RENTAL PAYMENTS
Each rent payment records:
    • period;
    • amount;
    • due date;
    • payment date;
    • status;
    • method;
    • receipt.
89. DEVELOPER MODULE
Developer creates:
Project
 ↓
Buildings
 ↓
Floors
 ↓
Units
Example:
Luxury Residence
 ├── Building A
 │    ├── A101
 │    ├── A102
 │    └── A103
 │
 └── Building B
      ├── B101
      └── B102
90. PROJECT MANAGEMENT
Project fields:
    • name;
    • developer;
    • location;
    • land area;
    • project type;
    • construction status;
    • completion percentage;
    • expected completion;
    • facilities;
    • gallery;
    • documents.
91. UNIT INVENTORY
Each unit:
    • unit number;
    • floor;
    • area;
    • bedrooms;
    • bathrooms;
    • price;
    • availability;
    • orientation;
    • parking.
92. UNIT STATUS
Available
Reserved
Contracted
Sold
Blocked
93. CONSTRUCTION PROGRESS
Example:
Foundation      100%
Structure        70%
Masonry          45%
Finishing        20%
Overall           58%
94. DEVELOPER PAYMENT PLAN
Example:
10% Reservation
20% Foundation
20% Structure
20% Roofing
20% Finishing
10% Handover
The developer configures the schedule.
95. DEVELOPER SALES WORKFLOW
Project
 ↓
Unit
 ↓
Lead
 ↓
Viewing
 ↓
Reservation
 ↓
Payment Plan
 ↓
Contract
 ↓
Installments
 ↓
Handover
96. AGENT CRM
Dashboard:
Leads              126
New Leads           32
Viewings            18
Offers               9
Closed Deals         4
97. CRM LEAD SOURCES
    • Website
    • Mobile
    • Facebook
    • Google
    • Telegram
    • WhatsApp
    • Referral
    • Phone
    • Advertisement
    • Agent
98. CRM PIPELINE
New Lead
 ↓
Contacted
 ↓
Qualified
 ↓
Viewing
 ↓
Negotiation
 ↓
Offer
 ↓
Closed
99. COMMISSION ENGINE
Example:
Sale = 20,000,000
Commission = 2%

Commission = 400,000 ETB
Commission rules must be configurable by:
    • company;
    • property type;
    • agent;
    • transaction;
    • date.
100. COMPANY MANAGEMENT
Company:
Company
 ├── Head Office
 ├── Addis Branch
 ├── Harar Branch
 ├── Jijiga Branch
 └── Dire Dawa Branch
Each branch can have agents and managers.
101. AGENT PUBLIC PROFILE
Profile contains:
    • name;
    • company;
    • profile photo;
    • verification;
    • service locations;
    • languages;
    • properties;
    • reviews;
    • response rate.
102. PROPERTY MANAGEMENT COMPANY
Company can manage:
Properties
Units
Tenants
Guests
Bookings
Rent
Maintenance
Cleaning
Staff
Reports
103. MULTI-UNIT MANAGEMENT
Building
 ├── Unit 101
 ├── Unit 102
 ├── Unit 103
 └── Unit 104
Each unit may have different:
    • owner;
    • tenant;
    • booking calendar;
    • price;
    • status.
104. HOTEL/ACCOMMODATION MODEL
For hotels:
Hotel
 ↓
Room Types
 ↓
Rooms
 ↓
Availability
 ↓
Reservations
This is different from a single-villa booking.
105. EXPERIENCE MARKETPLACE
Future module:
    • tours;
    • cultural experiences;
    • photography;
    • food experiences;
    • transportation;
    • local guides.
106. EXPERIENCE BOOKING
Experience
 ↓
Date
 ↓
Participants
 ↓
Booking
 ↓
Payment
 ↓
Experience
 ↓
Review
107. TOURISM DISCOVERY
The platform may provide:
    • destination pages;
    • local attractions;
    • accommodation;
    • tours;
    • restaurants;
    • transportation.
Example:
Explore Harar
 ├── Stays
 ├── Villas
 ├── Guesthouses
 ├── Tours
 ├── Restaurants
 └── Attractions
108. PROPERTY RECOMMENDATION ENGINE
Recommendations based on:
    • search history;
    • saved properties;
    • price;
    • location;
    • property type;
    • behavior;
    • booking history.
109. SEARCH ALERTS
Example:
Location: Bole
Type: Villa
Bedrooms: 4+
Price: <30M
System notifies user when matching properties are published.
110. PRICE ALERTS
Notify user when:
    • price decreases;
    • property becomes available;
    • rental price changes.
111. NOTIFICATION ENGINE
Channels:
    • in-app;
    • push;
    • SMS;
    • email;
    • supported messaging integrations.
Events:
    • inquiry;
    • message;
    • viewing;
    • booking;
    • offer;
    • payment;
    • verification;
    • cancellation;
    • review.
112. AUTOMATED MESSAGES
Short-stay examples:
Booking Confirmation
7 Days Before
3 Days Before
1 Day Before
Check-in
Checkout
Review Request
All templates configurable.
113. CONTENT MANAGEMENT
Admin can manage:
    • homepage;
    • banners;
    • blog;
    • articles;
    • FAQs;
    • city pages;
    • categories;
    • promotional campaigns.
114. SEO
Each property should have:
    • SEO title;
    • description;
    • canonical URL;
    • OpenGraph;
    • structured metadata.
Example:
/properties/addis-ababa/bole/modern-villa-1245
115. SOCIAL SHARING
Support:
    • Facebook;
    • Telegram;
    • WhatsApp;
    • X;
    • copy link.
116. CUSTOMER SUPPORT
Ticket system:
Ticket
User
Category
Priority
Description
Attachments
Assigned Staff
Status
Categories:
    • account;
    • property;
    • booking;
    • payment;
    • verification;
    • fraud;
    • technical;
    • subscription.
117. TRUST & SAFETY
Dedicated safety center:
    • report scam;
    • report property;
    • report host;
    • report guest;
    • payment safety;
    • verification information;
    • dispute assistance.
118. PROPERTY REPORTING
Reasons:
    • scam;
    • fake property;
    • duplicate;
    • wrong price;
    • unavailable;
    • misleading;
    • suspicious documents;
    • inappropriate content.
119. REVIEW MODERATION
System should detect:
    • spam;
    • abusive content;
    • manipulation;
    • fake reviews.
Administrators can:
    • hide;
    • investigate;
    • restore;
    • remove according to policy.
120. ADMIN DASHBOARD
Main metrics:
Users
Properties
Listings
Hosts
Agents
Companies
Developers
Projects
Bookings
Transactions
Revenue
Reports
Verification Requests
121. ADMIN PROPERTY MANAGEMENT
Admin can:
    • create;
    • edit;
    • approve;
    • reject;
    • suspend;
    • archive;
    • feature;
    • expire;
    • merge duplicate records.
122. CATEGORY MANAGEMENT
Configurable:
    • categories;
    • subcategories;
    • property types;
    • amenities;
    • features;
    • listing types;
    • transaction types.
123. LOCATION MANAGEMENT
Admin can manage:
    • regions;
    • cities;
    • sub-cities;
    • woredas;
    • kebeles;
    • neighborhoods.
Each supports:
    • English;
    • Amharic;
    • Afaan Oromo;
    • coordinates;
    • polygons;
    • active/inactive status.
124. MULTILINGUAL SUPPORT
Initial languages:
    1. English
    2. Amharic
    3. Afaan Oromo
Architecture should allow:
    • Somali;
    • Tigrinya;
    • Afar;
    • Arabic;
    • others.
125. ETHIOPIAN CALENDAR
The presentation layer shall support Ethiopian dates.
Example:
2019 መስከረም 12
The database should maintain a consistent internal date representation while allowing Ethiopian-calendar display and conversion.
126. REPORTING
Reports include:
Marketplace
    • active listings;
    • new listings;
    • inquiries;
    • views;
    • favorites.
Stay
    • bookings;
    • occupancy;
    • ADR;
    • revenue;
    • cancellations.
Rental
    • rent collected;
    • outstanding;
    • occupancy.
Developer
    • units sold;
    • reservations;
    • installments;
    • outstanding balances.
Financial
    • revenue;
    • fees;
    • refunds;
    • payouts;
    • commissions.
127. MARKET INTELLIGENCE
Dashboard can show:
    • listing volume;
    • asking price trends;
    • rental trends;
    • price/m²;
    • city comparisons;
    • neighborhood activity.
All metrics must identify their data source and whether they are asking-price indicators or verified transaction data.
128. VALUATION MODULE
Future functionality:
Comparable Properties
+
Location
+
Land Area
+
Building Area
+
Condition
+
Property Type
+
Amenities
Output:
Estimated Market Range
The system must clearly identify automated estimates as estimates rather than official valuations.
129. MORTGAGE CALCULATOR
Input:
    • property price;
    • down payment;
    • loan;
    • interest;
    • term.
Output:
    • monthly payment;
    • total interest;
    • total payment.
130. INVESTMENT CALCULATOR
Input:
    • purchase price;
    • rent;
    • expenses;
    • vacancy;
    • maintenance.
Output:
    • gross yield;
    • estimated net yield;
    • annual income.
131. AI SEARCH
User:
Find a 4-bedroom villa in Harar under 15 million with parking.
AI converts this to structured search parameters.
132. AI LISTING ASSISTANT
Seller enters:
4 bedroom
Bole
600 m²
Garden
Parking
25M
AI generates a draft description.
The user must review before publication.
133. AI DUPLICATE DETECTION
AI can compare:
    • photos;
    • descriptions;
    • location;
    • property characteristics.
Potential matches are flagged for review.
134. AI FRAUD DETECTION
AI may identify unusual:
    • pricing;
    • posting patterns;
    • account behavior;
    • duplicate content.
AI flags must remain review signals rather than automatic legal conclusions.
135. GOVERNMENT/EXTERNAL INTEGRATIONS
Architecture should allow authorized future integrations with:
    • land registry;
    • building permit systems;
    • municipal GIS;
    • tax/property systems;
    • identity services;
    • business registration;
    • digital signatures;
    • payment systems.
The platform must only claim official verification when it actually has authorized access and verification.
136. API ARCHITECTURE
Example:
/api/v1/auth
/api/v1/users
/api/v1/properties
/api/v1/listings
/api/v1/categories
/api/v1/locations
/api/v1/gis
/api/v1/hosts
/api/v1/agents
/api/v1/companies
/api/v1/developers
/api/v1/projects
/api/v1/buildings
/api/v1/units
/api/v1/inquiries
/api/v1/messages
/api/v1/viewings
/api/v1/offers
/api/v1/reservations
/api/v1/bookings
/api/v1/calendar
/api/v1/payments
/api/v1/payouts
/api/v1/rentals
/api/v1/leases
/api/v1/maintenance
/api/v1/documents
/api/v1/verifications
/api/v1/reviews
/api/v1/disputes
/api/v1/notifications
/api/v1/subscriptions
/api/v1/advertising
/api/v1/reports
137. MOBILE APPLICATION
Android-first.
Primary screens:
    1. Home
    2. Search
    3. Map
    4. Property
    5. Stay
    6. Favorites
    7. Messages
    8. Bookings
    9. Notifications
    10. Post Property
    11. Dashboard
    12. Profile
138. LOW-BANDWIDTH DESIGN
The application must support Ethiopian network conditions through:
    • image compression;
    • lazy loading;
    • CDN;
    • caching;
    • pagination;
    • lightweight API;
    • offline drafts;
    • optimized mobile screens.
139. SECURITY REQUIREMENTS
Mandatory:
    • HTTPS;
    • password hashing;
    • OTP;
    • RBAC;
    • 2FA;
    • CSRF protection;
    • XSS protection;
    • SQL injection protection;
    • rate limiting;
    • API authentication;
    • secure file storage;
    • encryption for sensitive information;
    • audit logging.
140. RBAC
Permissions should be granular.
Examples:
property.view
property.create
property.update
property.delete
property.approve
property.verify

booking.view
booking.create
booking.cancel
booking.refund

payment.view
payment.create
payment.refund
payment.reconcile

verification.review
verification.approve
verification.reject
141. ADMIN ROLES
Super Admin
Platform Admin
Property Admin
Verification Officer
Moderator
Finance Officer
Customer Support
Content Manager
Developer Manager
System Administrator
Property Manager
142. AUDIT LOG
Every sensitive action is recorded:
User
Action
Entity
Old Value
New Value
Timestamp
IP
Device
Reason
Example:
Admin 24
Changed Price
20,000,000 → 18,500,000
143. DATA PRIVACY
Sensitive data:
    • ID;
    • phone;
    • email;
    • financial information;
    • documents.
Access must be controlled.
144. DOCUMENT SECURITY
Use:
    • private object storage;
    • signed URLs;
    • expiring access;
    • permission checking;
    • download logs.
145. ACCOUNT SECURITY
Support:
    • OTP;
    • password;
    • 2FA;
    • trusted devices;
    • login history;
    • suspicious-login detection.
146. BACKUP
Database	Daily backup.
Incremental	Configurable.
Documents	Versioned storage.
Disaster Recovery	Separate backup environment.
147. PERFORMANCE
Target:
    • normal page load: approximately 2–3 seconds;
    • common API response: generally <500 ms;
    • indexed search: approximately <1 second under normal load.
Actual performance must be validated through load testing.
148. SCALABILITY
Architecture should be capable of scaling toward:
100,000+ users
1,000,000+ users
10,000,000+ listings
Large booking inventory
Nationwide GIS data
149. RECOMMENDED TECHNOLOGY STACK
Given the intended Laravel-centered development approach:
Backend	Laravel 12
PHP	PHP 8.4+
Database	PostgreSQL + PostGIS
Cache	Redis
Queue	Laravel Queue + Redis
Queue monitoring	Laravel Horizon
Search	OpenSearch / Elasticsearch / Meilisearch
Web	Laravel Blade + Tailwind CSS + Alpine.js
A separate SPA can be introduced later if required.
Mobile	Flutter or native Android.
Storage	S3-compatible object storage.
Maps
Provider abstraction supporting:
    • OpenStreetMap;
    • Mapbox;
    • Google Maps.
150. RECOMMENDED LARAVEL ARCHITECTURE
Use domain modules:
app/
└── Domains/
    ├── Identity/
    ├── User/
    ├── Organization/
    ├── Property/
    ├── Listing/
    ├── Location/
    ├── GIS/
    ├── Marketplace/
    ├── Host/
    ├── Guest/
    ├── Agent/
    ├── Developer/
    ├── Project/
    ├── Building/
    ├── Unit/
    ├── CRM/
    ├── Inquiry/
    ├── Messaging/
    ├── Viewing/
    ├── Offer/
    ├── Reservation/
    ├── Booking/
    ├── Calendar/
    ├── Payment/
    ├── Payout/
    ├── Rental/
    ├── Lease/
    ├── Maintenance/
    ├── Cleaning/
    ├── Document/
    ├── Verification/
    ├── Review/
    ├── Dispute/
    ├── Moderation/
    ├── Notification/
    ├── Subscription/
    ├── Advertising/
    ├── Reporting/
    ├── Analytics/
    └── Administration/
151. DATABASE CORE
Core tables:
users
roles
permissions
user_roles
user_permissions

properties
property_types
property_categories
property_features
property_amenities
property_images
property_videos

locations
regions
zones
cities
subcities
woredas
kebeles
neighborhoods

companies
branches
agents
hosts
developers
professionals

listings
listing_types
listing_statuses

accommodation_units
unit_types
bed_configurations
unit_amenities

availability_calendars
availability_rules
blocked_dates
pricing_rules
seasonal_prices

favorites
saved_searches
saved_collections

inquiries
messages
message_attachments

viewings
offers
counter_offers

reservations
bookings
booking_guests
booking_items

check_ins
check_outs

cancellation_policies
cancellation_rules

leases
tenants
rent_schedules
rent_payments

projects
buildings
units
construction_progress
developer_payment_plans

documents
document_types
document_versions

verifications
verification_documents
verification_reviews

reviews
guest_reviews
host_reviews

disputes
damage_claims

maintenance_tickets
cleaning_tasks
cleaners

payments
payment_transactions
payment_methods
refunds

payout_accounts
payouts
ledger_entries

subscriptions
subscription_plans
advertisements
promotions

commissions
commission_rules

notifications
notification_templates

reports
moderation_actions
audit_logs

experiences
experience_bookings
experience_participants

system_settings
152. PROPERTY/LISTING SEPARATION
This relationship is fundamental:
PROPERTY
    │
    └── LISTING
A property exists independently of its advertising.
This prevents duplicate records.
153. LISTING STATUS
Draft
Pending Review
Approved
Published
Paused
Rejected
Expired
Suspended
Archived
154. PROPERTY STATUS
Draft
Available
Under Offer
Reserved
Sold
Rented
Leased
Occupied
Inactive
Archived
155. BOOKING STATUS
Pending
Payment Pending
Confirmed
Checked In
Active
Checked Out
Completed
Cancelled
Refunded
Disputed
No Show
156. PROJECT STATUS
Planning
Pre-Construction
Under Construction
Near Completion
Completed
Delivered
Cancelled
157. UNIT STATUS
Available
Reserved
Contracted
Sold
Blocked
158. FULL USER JOURNEY — BUY
Register
 ↓
Verify
 ↓
Search
 ↓
Map
 ↓
Property
 ↓
Save
 ↓
Contact
 ↓
Viewing
 ↓
Offer
 ↓
Negotiation
 ↓
Reservation
 ↓
Documents
 ↓
Agreement
 ↓
Payment
 ↓
Completion
159. FULL USER JOURNEY — RENT
Search
 ↓
Property
 ↓
Contact
 ↓
Viewing
 ↓
Application
 ↓
Document Review
 ↓
Lease
 ↓
Deposit
 ↓
Move In
 ↓
Monthly Rent
 ↓
Maintenance
 ↓
Move Out
160. FULL USER JOURNEY — SHORT STAY
Destination
 ↓
Dates
 ↓
Guests
 ↓
Search
 ↓
Property
 ↓
Availability
 ↓
Price
 ↓
Booking
 ↓
Payment
 ↓
Confirmation
 ↓
Check-in
 ↓
Stay
 ↓
Checkout
 ↓
Review
161. FULL USER JOURNEY — DEVELOPER
Developer
 ↓
Project
 ↓
Buildings
 ↓
Units
 ↓
Pricing
 ↓
Publication
 ↓
Lead
 ↓
Viewing
 ↓
Reservation
 ↓
Payment Plan
 ↓
Installments
 ↓
Handover
162. FULL HOST JOURNEY
Register
 ↓
Host Verification
 ↓
Create Property
 ↓
Create Stay Listing
 ↓
Photos
 ↓
Amenities
 ↓
Pricing
 ↓
Calendar
 ↓
House Rules
 ↓
Publish
 ↓
Booking
 ↓
Guest
 ↓
Check-in
 ↓
Cleaning
 ↓
Checkout
 ↓
Payout
 ↓
Review
163. HOME PAGE
The home page should allow the user to select:
BUY
RENT
STAY
LAND
NEW PROJECTS
Example:
┌──────────────────────────────────────────────────┐
│ LOGO       Buy Rent Stay Land Projects   Login   │
├──────────────────────────────────────────────────┤
│                                                  │
│          FIND YOUR PROPERTY IN ETHIOPIA         │
│                                                  │
│ [ Buy ] [ Rent ] [ Stay ] [ Land ] [ Projects ] │
│                                                  │
│ [ Where? _____________________________ ]         │
│ [ Property Type ] [ Price ] [ Search ]           │
│                                                  │
├──────────────────────────────────────────────────┤
│ Featured Properties                              │
├──────────────────────────────────────────────────┤
│ Popular Stays                                    │
├──────────────────────────────────────────────────┤
│ New Developments                                 │
├──────────────────────────────────────────────────┤
│ Popular Cities                                   │
└──────────────────────────────────────────────────┘
164. MOBILE HOME
Mobile navigation:
Home
Search
Map
Bookings
Messages
Profile
For hosts:
Host Dashboard
Calendar
Bookings
Properties
Messages
Revenue
165. LISTING QUALITY SCORE
Example:
Photos             ✓
Description        ✓
Location           ✓
Price              ✓
Amenities          ✓
Documents          △

Completeness: 92%
This score is separate from legal verification.
166. MARKETPLACE MONETIZATION
Revenue streams:
Free Listings	Basic listing allowance.
Premium Listings
    • featured;
    • top;
    • homepage;
    • urgent.
Agent subscriptions
Developer subscriptions
Company subscriptions
Host subscriptions
Advertising
Booking service fees
Professional services
Data/analytics products	Subject to applicable law and commercial arrangements.
167. SUBSCRIPTION ENGINE
Plans contain:
Plan Name
Price
Duration
Listing Limit
Image Limit
Featured Credits
CRM
Analytics
Team Members
Property Management
Booking Tools
168. ADVERTISING ENGINE
Placements:
    • homepage;
    • search;
    • city page;
    • category page;
    • property page;
    • mobile;
    • newsletter.
169. PROMOTED LISTINGS
Example:
Standard
Featured
Premium
Homepage
Urgent
Pricing and availability are configurable.
170. INVOICE ENGINE
Invoice:
Invoice Number
Customer
Service
Amount
Tax
Total
Payment Status
Date
171. TAX ENGINE
Tax configuration should be database-driven:
Tax Rule
Service
Rate
Effective Date
Location
The application should not hard-code one permanent tax rate.
172. NOTIFICATION PREFERENCES
Users can control:
    • email;
    • SMS;
    • push;
    • in-app;
    • marketing notifications.
Transactional notifications may remain mandatory where necessary.
173. PROPERTY ALERTS
User can save:
Bole
Villa
4+ Bedrooms
Under 30M
System automatically sends matching properties.
174. RECOMMENDATIONS
Sections:
    • Similar properties;
    • Recently viewed;
    • Recommended for you;
    • New in your area;
    • Price reduced;
    • Popular nearby.
175. SEO CITY PAGES
Example:
Properties in Addis Ababa
Properties in Bole
Houses for Sale in Harar
Villas for Rent in Jijiga
Land for Sale in Dire Dawa
Short Stay in Harar
New Apartments in Addis Ababa
176. NATIONAL STRUCTURE
ETHIOPIA
│
├── Addis Ababa
│
├── Harari Region
│   └── Harar
│
├── Somali Region
│   └── Jijiga
│
├── Dire Dawa
│
├── Oromia
│
├── Amhara
│
├── Tigray
│
├── Sidama
│
├── Afar
│
└── Other Regions
All geographic information must be database-driven.
177. ETHIOPIAN REAL-ESTATE LEGAL DESIGN
The platform should have a configurable compliance layer.
It should accommodate applicable requirements concerning:
    • real-estate development;
    • marketing;
    • valuation;
    • property documentation;
    • licensing;
    • contracts;
    • taxation;
    • identity;
    • payments.
Ethiopia's Real Estate Development and Real Property Marketing and Valuation Proclamation No. 1357/2024 should be considered when defining the applicable compliance and professional workflows. The system should be updated when laws or regulations change. Ethiopian Ministry of Justice — Proclamation No. 1357/2024
The platform must not present itself as a government land registry or legal ownership authority unless an authorized integration and legal mandate actually exists.
178. GOVERNMENT INTEGRATION ARCHITECTURE
Future connectors:
National ID
     │
Business Registration
     │
Land Registry
     │
Building Permit
     │
Municipal GIS
     │
Property Tax
     │
Payment Systems
     │
Digital Signature
All integrations should be API-based where officially available.
179. DATA OWNERSHIP
The system must clearly define:
    • user-generated content;
    • property information;
    • uploaded documents;
    • platform-generated analytics;
    • transaction records;
    • audit records.
180. DATA RETENTION
Configurable retention policies should apply to:
    • accounts;
    • listings;
    • messages;
    • documents;
    • payments;
    • bookings;
    • audit logs.
Legal requirements should override ordinary deletion rules where applicable.
181. DISASTER RECOVERY
The system should have:
    • automated backups;
    • separate backup location;
    • database recovery;
    • media recovery;
    • disaster recovery plan;
    • recovery testing.
182. SYSTEM OBSERVABILITY
Production should monitor:
    • server health;
    • database;
    • queue;
    • cache;
    • API latency;
    • errors;
    • payment failures;
    • booking failures;
    • storage;
    • search.
183. LOGGING
Separate logs:
Application
Security
Authentication
Payment
Booking
Admin
Audit
API
Integration
184. API RATE LIMITING
Rate limits shall be applied to:
    • login;
    • OTP;
    • search;
    • messaging;
    • APIs;
    • payment;
    • document upload.
185. FILE SECURITY
Uploaded documents and images should undergo:
    • file type validation;
    • size validation;
    • malware/virus scanning;
    • access control;
    • secure naming;
    • storage isolation.
186. SEARCH PERFORMANCE
For large-scale deployment:
PostgreSQL/PostGIS
        +
Search Engine
        +
Redis
        +
CDN
This separates transactional storage from high-volume search.
187. GIS PERFORMANCE
Spatial indexes should be used for:
    • property coordinates;
    • boundaries;
    • radius search;
    • polygon search.
188. DATABASE PRINCIPLES
Use:
    • foreign keys;
    • indexes;
    • unique constraints;
    • soft deletion where appropriate;
    • audit history;
    • normalized core data;
    • event/history tables.
189. IMPORTANT FINANCIAL PRINCIPLE
Never overwrite financial history.
Incorrect:
Payment = 100,000
then simply change it to:
Payment = 120,000
Instead:
Original Payment
       +
Adjustment
       +
Reason
       +
Authorized User
       +
Timestamp
190. BOOKING CONCURRENCY
The booking engine must protect against:
Two users booking the same inventory at the same time.
The database must enforce appropriate:
    • transactions;
    • locks;
    • unique constraints;
    • inventory validation.
This is a critical production requirement.
191. TRANSACTION IDEMPOTENCY
Payment callbacks and booking requests must be idempotent.
If the same payment callback arrives twice, the system must not create two payments or two bookings.
192. PAYMENT WEBHOOKS
Payment providers may send:
Payment Initiated
Payment Successful
Payment Failed
Payment Reversed
Payment Refunded
Each webhook must be:
    • authenticated;
    • logged;
    • validated;
    • idempotent.
193. BOOKING EXPIRATION
Unpaid booking holds must automatically expire.
Example:
Payment Pending
 ↓
15 minutes
 ↓
Expired
 ↓
Inventory Released
Duration must be configurable.
194. HOST PAYOUT SCHEDULE
Configurable:
Immediately
After Check-in
After Checkout
Weekly
Monthly
Actual payout behavior depends on payment-provider capabilities and applicable requirements.
195. SHORT-STAY TAX/FEES
The platform should support configurable:
    • service fee;
    • cleaning fee;
    • tax;
    • tourism/hospitality-related charges where applicable.
Rates must not be hard-coded.
196. PROPERTY MANAGEMENT DASHBOARD
For landlords/property managers:
Properties
Units
Occupied
Vacant
Tenants
Guests
Bookings
Rent
Maintenance
Cleaning
Revenue
197. ENTERPRISE PROPERTY MANAGEMENT
Large company:
Company
 ↓
Branches
 ↓
Managers
 ↓
Buildings
 ↓
Units
 ↓
Tenants / Guests
Permissions should follow organizational hierarchy.
198. MULTI-TENANCY
The system should support:
    • individual users;
    • companies;
    • branches;
    • teams.
Each organization has isolated data and permissions.
199. WHITE-LABEL CAPABILITY
Future enterprise version:
A developer/company can have:
company.example.com
with:
    • logo;
    • brand;
    • colors;
    • properties;
    • agents;
    • custom domain.
200. MOBILE-FIRST PROPERTY POSTING
User should be able to:
    1. Take photos.
    2. Detect location.
    3. Enter property details.
    4. Set price.
    5. Submit listing.
201. PHOTO LOCATION PRIVACY
The system should strip sensitive metadata such as unnecessary EXIF/GPS data from public images where appropriate.
202. HOST PHOTO GUIDANCE
System can warn:
Upload clear photos of the living room, bedrooms, bathrooms, kitchen and exterior.
203. LISTING QUALITY ASSISTANT
System can identify missing:
Photos
Price
Location
Bedrooms
Bathrooms
Description
Amenities
and suggest completing them.
204. PROPERTY INSPECTION
Future professional service:
Inspection Request
 ↓
Inspector Assigned
 ↓
Inspection
 ↓
Report
 ↓
Property Record
205. PROFESSIONAL MARKETPLACE
Professionals can create profiles for:
    • lawyers;
    • valuers;
    • surveyors;
    • architects;
    • engineers;
    • inspectors;
    • contractors;
    • property managers;
    • interior designers.
206. PROFESSIONAL SERVICE BOOKING
Example:
Need Property Valuation
 ↓
Search Valuers
 ↓
Select Professional
 ↓
Request Service
 ↓
Appointment
 ↓
Service
 ↓
Payment
 ↓
Review
207. PROPERTY INVESTMENT OPPORTUNITIES
Future category:
    • land investment;
    • rental investment;
    • development investment;
    • commercial investment.
208. INVESTOR DASHBOARD
Investor may track:
Portfolio
Properties
Purchase Cost
Rental Income
Expenses
Estimated Yield
Occupancy
Market Indicators
209. PROPERTY PORTFOLIO
User can own/manage:
Portfolio
 ├── Villa A
 ├── Apartment B
 ├── Land C
 └── Shop D
210. INVESTMENT ANALYTICS
Possible indicators:
    • rental income;
    • occupancy;
    • estimated yield;
    • expenses;
    • price changes.
These should be clearly presented as estimates or platform analytics where applicable.
211. PROPERTY HISTORY
Property can maintain:
Created
Listed
Price Changed
Viewed
Reserved
Sold
Rented
Renovated
Relisted
Access must respect privacy and legal restrictions.
212. PRICE HISTORY
Example:
Jan: 30M
Mar: 28M
Jun: 27M
Sep: 25M
Only show historical information to users where platform policy and applicable privacy/legal requirements permit.
213. PROPERTY STATUS HISTORY
Every important change should be auditable.
214. ADMIN ANALYTICS
National dashboard:
Total Users
Total Properties
Active Listings
Bookings
Sales Leads
Rental Leads
Developments
Revenue
Filter by:
    • date;
    • region;
    • city;
    • property type;
    • transaction type.
215. CITY ANALYTICS
Example:
Addis Ababa
Harar
Jijiga
Dire Dawa
Hawassa
Bahir Dar
Compare:
    • listings;
    • rentals;
    • stays;
    • inquiries;
    • prices.
216. MVP — PHASE 1
The first release should contain:
Marketplace
    1. Registration
    2. Login
    3. Profiles
    4. Property categories
    5. Property creation
    6. Listings
    7. Photos
    8. Search
    9. Filters
    10. Map
    11. Property details
    12. Favorites
    13. Messaging
    14. Inquiry
    15. Seller dashboard
    16. Admin dashboard
    17. Moderation
    18. Basic verification
    19. Notifications
    20. Reports
Stay
    21. Host registration
    22. Stay listings
    23. Availability calendar
    24. Date search
    25. Booking
    26. Booking status
    27. Guest profile
    28. Host dashboard
    29. Reviews
217. PHASE 2
Add:
    • agents;
    • companies;
    • CRM;
    • developer projects;
    • units;
    • viewing;
    • offers;
    • negotiation;
    • reservations;
    • subscriptions;
    • promoted listings;
    • payment integration.
218. PHASE 3
Add:
    • rental management;
    • tenant management;
    • lease management;
    • rent schedules;
    • maintenance;
    • housekeeping;
    • payout;
    • advanced booking;
    • calendar synchronization.
219. PHASE 4
Add:
    • valuation;
    • AI search;
    • price intelligence;
    • mortgage;
    • professional services;
    • investment analytics;
    • advanced GIS.
220. PHASE 5
Add:
    • authorized government integrations;
    • national property intelligence;
    • institutional marketplace;
    • advanced developer finance;
    • digital signatures;
    • advanced transaction workflows;
    • international expansion.
221. CORE SUCCESS CRITERIA
Buyer
Register
→ Search
→ Map
→ View
→ Save
→ Contact
→ Viewing
→ Offer
→ Negotiate
→ Reserve
Seller
Register
→ Verify
→ Create Property
→ Upload Photos
→ Submit
→ Approval
→ Publish
→ Receive Leads
Guest
Search
→ Dates
→ Guests
→ Property
→ Availability
→ Booking
→ Payment
→ Check-in
→ Stay
→ Checkout
→ Review
Host
Register
→ Verify
→ Create Property
→ Create Listing
→ Calendar
→ Pricing
→ Booking
→ Guest
→ Checkout
→ Payout
→ Review
Developer
Project
→ Building
→ Unit
→ Publish
→ Lead
→ Reservation
→ Payment Plan
→ Installments
→ Handover
222. FINAL CORE ARCHITECTURE
The complete system should ultimately be understood as:
                         ┌──────────────────────┐
                         │       USERS          │
                         └──────────┬───────────┘
                                    │
          ┌─────────────────────────┼─────────────────────────┐
          │                         │                         │
          ▼                         ▼                         ▼
       BUYERS                    SELLERS                    HOSTS
          │                         │                         │
          ▼                         ▼                         ▼
       TENANTS                    AGENTS                    GUESTS
          │                         │                         │
          └─────────────────────────┼─────────────────────────┘
                                    │
                           ┌────────▼────────┐
                           │ PROPERTY CORE   │
                           └────────┬────────┘
                                    │
                 ┌──────────────────┼───────────────────┐
                 │                  │                   │
                 ▼                  ▼                   ▼
                GIS             DOCUMENTS           MEDIA
                 │                  │                   │
                 └──────────────────┼───────────────────┘
                                    │
                           ┌────────▼────────┐
                           │ LISTING ENGINE  │
                           └────────┬────────┘
                                    │
        ┌──────────────┬────────────┼──────────────┬─────────────┐
        ▼              ▼            ▼              ▼             ▼
       SALE           RENT         LEASE          STAY        PROJECT
        │              │            │              │             │
        ▼              ▼            ▼              ▼             ▼
      OFFER          LEASE        CONTRACT      BOOKING         UNIT
        │              │            │              │             │
        └──────────────┴────────────┼──────────────┴─────────────┘
                                    │
                           ┌────────▼─────────┐
                           │ TRANSACTION CORE │
                           └────────┬─────────┘
                                    │
               ┌────────────────────┼─────────────────────┐
               ▼                    ▼                     ▼
            PAYMENTS            DOCUMENTS               CRM
               │                    │                     │
               ▼                    ▼                     ▼
           FINANCE              COMPLIANCE            ANALYTICS
               │                    │                     │
               └────────────────────┼─────────────────────┘
                                    ▼
                         NATIONAL PROPERTY PLATFORM
223. THE CORE CONCEPT IN ONE SENTENCE
The platform is a single Property Core supporting multiple real-estate business models — Buy, Rent, Lease, Stay and Development — with GIS, verification, documents, CRM, booking, payment, property management, reviews, analytics and Ethiopian-market integrations built around that core.
That is the concept I would use as the master SRS, rather than treating the Jiji and Airbnb parts as two separate systems.
Recommended implementation order
Phase 1: Property Core + Marketplace + GIS + Users + Listings + Search
Phase 2: Airbnb-style Stay + Calendar + Booking + Guest/Host + Reviews
Phase 3: Agents + CRM + Developers + Projects + Units + Offers
Phase 4: Rentals + Lease + Property Management + Maintenance + Finance
Phase 5: Payments + Payouts + Verification + Advanced GIS + Analytics
Phase 6: AI + Valuation + Investment + Professional Services + authorized government integrations
This architecture gives you a system that can start with Jiji-style property discovery, add Airbnb-style accommodation, and ultimately grow into a full Ethiopian real-estate operating platform rather than needing to rebuild the database and business architecture later.