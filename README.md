# BIMS — Barangay Information Management System

A comprehensive, all-in-one web-based system designed to serve barangays in the Philippines. BIMS provides a **Public-Facing Portal** for citizens (of all ages, including seniors and PWDs) and an **Administrative Backend** for barangay officials.

Built with **vanilla PHP 8 + MySQL/MariaDB** (no frameworks) for easy deployment on XAMPP.

---

## Table of Contents

1. [Tech Stack](#tech-stack)
2. [Quick Start](#quick-start)
3. [Default Accounts](#default-accounts)
4. [Architecture](#architecture)
5. [Directory Structure](#directory-structure)
6. [The Two Interfaces](#the-two-interfaces)
7. [Feature Modules](#feature-modules)
8. [Database](#database)
9. [Security](#security)
10. [Accessibility & Inclusivity](#accessibility--inclusivity)
11. [Roadmap](#roadmap)

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Language | PHP 8.x (OOP, custom MVC) |
| Database | MySQL / MariaDB 10.4 (PDO, InnoDB) |
| Frontend | HTML5, CSS3, vanilla JavaScript |
| Maps | Leaflet.js + OpenStreetMap |
| Charts | Chart.js |
| QR Codes | phpqrcode (generate) + html5-qrcode (scan) |
| TTS / Voice | Web Speech API (browser-native) |
| Auth | bcrypt password hashing + email OTP |

> Note: SMS features are intentionally **not** included per requirements — notifications use email + in-app alerts.

---

## Quick Start

1. Copy `BIMSS` folder into `C:\xampp\htdocs\`.
2. Start Apache + MySQL in XAMPP.
3. Create & import the database:
   ```
   mysql -u root < migrations/001_full_schema.sql
   mysql -u root bims < seeds/seed.sql
   ```
4. *(Optional)* Load sample reference data so dropdowns/forms have selectable options (safe to re-run):
   ```
   mysql -u root bims < seeds/seed_reference.sql
   mysql -u root bims < seeds/seed_health.sql
   ```
5. Configure DB credentials in `config/database.php` (defaults: host `localhost`, user `root`, no password, db `bims`).
6. Open **http://localhost/BIMSS/**

---

## Default Accounts

| Email | Username | Role | Access |
|-------|----------|------|--------|
| admin@bims.local | admin | Captain | Full admin |
| captain@bims.local | captain | Captain | Full admin |
| secretary@bims.local | secretary | Secretary | Documents/certificates |
| treasurer@bims.local | treasurer | Treasurer | Finance/budget/tax |

**Default password for all: `Admin@12345`**

A copy is also saved in [`DEFAULT_ACCOUNTS.txt`](DEFAULT_ACCOUNTS.txt).

---

## Architecture

Single application with two portals, sharing one codebase and database:

- **Public Portal** — root `/` routes (e.g. `/`, `/auth/login`, `/public/dashboard`)
- **Admin Backend** — `/admin/*` routes (protected by RBAC)

Custom MVC pattern with a **front controller** (`index.php`) and a regex-based **Router**.

```
Browser → .htaccess → index.php → App (registers routes)
                                   → Router::dispatch
                                      → Middleware (CSRF, Auth, RBAC, Audit)
                                         → Controller
                                            → Model (PDO)
                                            → View (layout + content)
```

---

## Directory Structure

```
BIMSS/
├── index.php              # Front controller / entry point
├── .htaccess              # URL rewriting
├── config/                # database.php, app.php
├── core/                  # App, Router, Controller, Model, Database,
│                          # Auth, Session, CSRF, Validator, Response, helpers
├── middleware/            # CSRFMiddleware, AuthMiddleware, RBACMiddleware, AuditMiddleware
├── models/                # User, Resident, Household, ... (PDO models)
├── controllers/
│   ├── public/            # Citizen-facing controllers
│   ├── admin/             # Official backend controllers
│   └── api/               # (SMS/webhook endpoints - placeholder)
├── views/
│   ├── public/            # Citizen templates (+ layouts)
│   ├── admin/             # Official templates (+ layouts)
│   ├── components/        # Reusable UI (table, pagination, accessibility-bar)
│   └── errors/            # 404 page
├── public/
│   ├── css/               # main, public-portal, admin, accessibility, kiosk
│   ├── js/                # app, accessibility, tts, voice
│   └── uploads/           # complaints, dana, profiles
├── migrations/            # 001_full_schema.sql (86 tables)
├── seeds/                 # seed.sql (roles, permissions, default users)
│                          # seed_reference.sql (sample reference data)
│                          # seed_health.sql (sample health records)
├── lang/                  # en, fil, bis, ilc, bic translation files
└── DEFAULT_ACCOUNTS.txt
```

---

## The Two Interfaces

### 🏛 Public-Facing Portal
Designed for citizens of **all ages** — tech-savvy teens to seniors and PWDs.

- Picture/tile-based navigation with large icons
- **Text-to-Speech (TTS)** reads labels & announcements aloud
- **Voice-to-Text** input for dictation
- **Font scaling up to 200%**
- **Dark mode** and **high-contrast** modes
- **Multilingual**: English, Filipino, Bisaya, Ilocano, Bicolano
- **Kiosk mode** ready (large touch tiles)
- Digital resident ID, document requests, tracking, appointments, complaints, blotter, bulletin, map, transparency dashboard

### 🛠 Administrative Backend
Role-based control for barangay officials.

- Sidebar navigation grouped by module
- Dashboard with live statistics
- RBAC (Captain sees all; Treasurer only Finance, etc.)
- Audit logging on actions
- All governance modules: demographics, certificates, finance, health, peace & order, DRRM, assets, compliance, reports

---

## Feature Modules

Fully planned across **11 modules** (the master data, certificate workflow, finance, health/welfare, peace & order, DRRM, infrastructure/livelihood, admin controls, accessibility, and smart features). The foundation + multiple modules are implemented; the remainder are being built incrementally.

Currently implemented/skeleton:

| Module | Status |
|--------|--------|
| Core framework, Auth, RBAC | ✅ Done |
| Layouts, CSS (Bootstrap 5), accessibility JS | ✅ Done |
| Residents | ✅ Full CRUD (search, filter, pagination, soft delete) |
| Households | ✅ Full CRUD (list, add, edit, show, soft delete, search/filter) |
| Clearances | ✅ Workflow (process → sign → release) |
| Certificates | ✅ Create + sign/release, resident lookup |
| Appointments | ✅ List/show/complete/cancel + slot management |
| Bulletins | ✅ Create/list/delete with category + pin |
| Finance | ✅ Income, expenses, and official receipts (record, list, auto receipt #) |
| Budget | ✅ Create budget + line items, overview list, detail with utilization/balance |
| Tax | ✅ Tax ledger with assessments, status tracking (unpaid/partial/paid/delinquent), payment recording |
| Health | ✅ Maternal care, immunization, child growth, disease surveillance, program overview |
| Senior & PWD | ✅ Registry with senior/PWD profiles, pension tracking, benefits, filter views |
| Blotter | ✅ Walk-in registration (auto case #), list with status filters, detail + case update |
| Lupon | ✅ KP case workflow: mediation/pangkat/conciliation, hearings, settlements, CFA issuance |
| Tanod & CCTV | ✅ Tanod roster, duty schedules (JSON member assignments), CCTV camera registry |
| DRRM | ✅ Disaster event log, RDANA assessments (shelter + effect breakdowns), hazard zone map, relief inventory & distributions |
| Evacuation | ✅ Evacuation centers with status + capacity/utilization, resident check-in/check-out (transactional occupancy) |
| Assets | ✅ Asset registry (vehicle, equipment, facility, furniture), condition/status tracking, purchase value, maintenance log with cost and next-schedule, total investment |
| Venue Bookings | ✅ Venue booking registration (resident, schedule, purpose, amount, payment), confirm/cancel workflow with status filtering |
| Livelihood | ✅ Job postings (company, position, salary, expiry, active/inactive), farmer registry with farm size and crops (duplicate registration guarded) |
| Compliance | ✅ Transparency documents (annual budget, income/expenditure, NTA, procurement, awards, collections, reports) with file upload & public posting |
| Documents, Reports | 🚧 Controllers scaffolded, views pending |
| GIS map, charts, QR, kiosk | 📝 Planned |

---

## Fixed Issues & Resolutions

A log of notable bugs found and resolved during development:

### 1. Soft-delete silently not persisting (`core/Model.php`)
**Bug:** `Model::delete()` called `update()`, which passed the data through `filterData()`. Because `deleted_at` was not in a model's `fillable` whitelist, the column was stripped out — so soft deletes never actually saved and "deleted" records kept appearing.
**Fix:** Rewrote `delete()` to run a direct `UPDATE ... SET deleted_at = NOW(), updated_at = NOW()` against the primary key. This fixes soft deletes for every model in the app.

### 2. "Class not found" fatal errors on 30 admin/public controllers
**Bug:** Every scaffolded stub controller was corrupted on disk — the `<?php` opening tag had been removed **and** all `$` tokens were stripped (e.g. `__call(\, \)` instead of `__call($name, $args)`, `\->view...` instead of `$this->view...`). PHP served these files as plain text and never defined the classes, so routes like `/admin/households` and `/admin/reports` threw `Class "...Controller" not found`.
**Fix:** A repair pass (1) restored the stripped `$` tokens, and (2) re-added the missing `<?php` open tag to every controller. All controllers now lint clean and every module page renders.

### 3. Admin pages showing bare HTML (static assets 404)
**Bug:** `public/.htaccess` intercepted all requests for static assets (CSS/JS), returning 404 and causing layouts to render unstyled/broken.
**Fix:** Removed the offending `.htaccess`; static assets now serve correctly.

### 4. Bootstrap 5 UI redesign (framework adoption)
The original custom CSS pipeline wasn't loading reliably. The frontend was rebuilt on **Bootstrap 5 + Bootstrap Icons** (CDN) for both the public portal and the admin backend, with the custom CSS reduced to theme overrides. **Dark mode** and **high-contrast** modes (in `accessibility.css`) now override Bootstrap's CSS variables so they work with the new components.

### 5. GPS coordinates removed from household forms
The **GPS Latitude / Longitude** inputs on the household create form were not needed. They were removed across the form (`_form.php`), the field extraction (`HouseholdController`), the model `fillable`, and the detail view (`show.php`).

### 6. Household store failing — `barangay_id` could not be null
`households.barangay_id` is `NOT NULL`, but the create form has no barangay field and the config `barangay.name` was empty, so `barangay_id` was saved as `NULL` and every insert failed with `Column 'barangay_id' cannot be null`. `extractData()` now resolves `barangay_id` from the selected **purok** first (falling back to the config-based lookup), so households linked to a Purok/Sitio save correctly.

### 7. Sample reference data for testing (`seeds/seed_reference.sql`)
Added an idempotent, re-runnable seed that populates a sample geography tree (region → province → city → barangay → puroks), households, residents (+ resident links), a chart of accounts, appointment slots, health programs, notification templates, and lupon members — so every admin form's dropdowns have selectable options during development.

### 8. Finance module — "Under Construction" stub replaced
The `/admin/finance` page only rendered an "Under Construction" stub even though routes existed for income, expenses, and receipts. Implemented the full Finance module: an overview dashboard (income, expenses, net position, receipt count), record forms + lists for income and expenses, and official-receipt generation with auto-incrementing receipt numbers (validation + database inserts for `income_records`, `expense_records`, and `official_receipts`).

### 9. Budget module implemented & "cannot save budget" fix
The `/admin/budget` page was also an "Under Construction" stub. Implemented the full Budget module (list of budgets with allocated/utilized/balance, a create form with a budget header plus dynamic line items against chart-of-accounts, and a detail page with per-line utilization). The create form could not save because the chart of accounts was empty (it had been removed with the earlier test-data cleanup), so the line-item account dropdown had no options and validation always rejected the form — reseeded the standard 13 chart-of-accounts entries to fix it.

### 10. Tax module — ledger, status tracking & payments
The `/admin/tax` page was a stub. Implemented the module: overview stats, a tax ledger listing assessments with resident names, filters, and inline payment recording per ledger row. Payment amounts are capped at the remaining balance and the ledger status is recomputed (`unpaid` → `partial` → `paid`) on every insert; overdue unpaid entries are automatically flagged `delinquent`.

### 11. Health module — BHERT records implemented
The `/admin/health` page was a stub. Implemented the module: an overview dashboard with health stats (pregnant women, maternal/immunization/growth/surveillance counts, programs), a disease-surveillance feed, and CRUD-style record pages for maternal care, immunization, child growth, and disease surveillance (validation + inserts against their tables). Health pages were redesigned to match the other admin modules (stats cards + tables on the overview; side-by-side form + table on the record pages).

### 12. Health module showing empty dashboard — sample data seed
After implementation, `/admin/health` (and its record dropdowns) had nothing to show because all residents were soft-deleted during earlier test-data cleanup. Added `seeds/seed_health.sql` (idempotent, safe to re-run) that restores the soft-deleted residents, adds a sample family, and seeds maternal, immunization, child-growth, and disease-surveillance records plus health programs — so every page and form dropdown is populated during development.

### 13. Admin page rendered as a bare fragment (missing layout)
`Controller::view()` runs `extract($data)` before resolving the layout. Any controller passing a top-level view-variable named `data` (e.g. `'data' => $stats`) silently overwrote the method's own `$data` variable, making `$data['layout']` null — the page then echoed only the view content with no admin layout/sidebar. Renamed the offending key in `HealthController::index()` to `stats` so the layout resolves; this also prevents the same footgun for any future/other pages using a `data` key.

### 14. Senior & PWD module — "Under Construction" stub replaced
The `/admin/seniors` page only rendered the generic "Under Construction" placeholder. Implemented the full module based on the `senior_pwd_profiles` table: an overview with statistics (senior citizens, PWDs, active pensioners, monthly allowance), a registration form (resident, type, pension status, allowance/grocery benefits, OSCA/PWD numbers), and a filterable registry table with badge-styled type/pension statuses. Storing a profile that already exists for the same resident+type is rejected with a clear flash message (unique-key violation handled instead of a generic 500).

### 15. Blotter module — "Under Construction" stub replaced
The `/admin/blotter` page only rendered the generic "Under Construction" placeholder. Implemented the full module on the `blotters` + `blotter_witnesses` tables: an overview with case statistics (total/open/resolved/closed), a walk-in registration form (reporter, incident type, narrative, location, purok, incident date-time) that auto-assigns a `BLT-*` case number, a status-filterable entry table, and a detail page with the incident narrative, witnesses list, and a case-update panel (status, assigned tanod, case number).

### 16. Lupon (Katarungang Pambarangay) module — "Under Construction" stub replaced
The `/admin/lupon` pages only rendered the generic "Under Construction" placeholder. Implemented the full KP module on the `kp_cases`, `kp_hearings`, `kp_settlements`, and `kp_cfa` tables: an overview with Lupon members and case statistics, a cases list with status filters, a case-filing form (complainant/respondent residents, nature of dispute, cause of action) that auto-assigns a `KP-*` case number, and a case detail page with hearing scheduling/recording, settlement recording (marks the case settled), and Certificate of Arbitration issuance (auto `CFA-*` number, flips the case to `cfa_issued`) — each apply-write handled in a transaction.

### 17. Tanod & CCTV module — "Under Construction" stub replaced
The `/admin/tanod` pages only rendered the generic "Under Construction" placeholder. Implemented the module on the `tanod_schedules` and `cctv_cameras` tables plus the tanod role of `users`: an overview with the tanod roster and duty statistics, a duty-scheduling form with multi-member assignment, a filterable schedule list, and a CCTV camera registry with online/offline stats. Two sample tanod users were added as reference data so the roster and assignment dropdown are populated.

### 18. `tanod_schedules.assigned_members` is a JSON column
**Bug:** Saving a schedule stored the selected members as a comma-separated string, but `assigned_members` is declared `JSON` in `001_full_schema.sql` (stored as `LONGTEXT` with a `json_valid` CHECK constraint in MariaDB). The insert failed with `SQLSTATE[23000] ... CONSTRAINT tanod_schedules.assigned_members failed`. Storing plain text tripped the JSON validity check.
**Fix:** Encode the member array with `json_encode()` on insert and `json_decode()` back to a list for display in both the overview and the schedule views.

### 19. DRRM module — "Under Construction" stub replaced
The `/admin/drrm` pages only rendered the generic "Under Construction" placeholder. Implemented the module on the `disaster_events` and `rdana_*` tables: an overview with disaster statistics, a filterable disaster-event log with a record form (auto type/severity badges), a show page with full incident details, RDANA pages (an event's assessment reports + a multi-part assessment form that saves the report, shelter impact, and per-category effect breakdowns — affected/displaced/dead/injured/missing — in one transaction, plus a reports list linking back to events) to `hazardMap.php` (hazard zone list with risk-level badges) and `relief.php` (inventory + recent distributions). Two bugs surfaced during verification: the `rdana/{event_id}` route's named parameter must match the controller method signature (`rdana($event_id)`) or PHP 8 throws `Unknown named parameter` from `Router.php`, and `rdana_shelter.immediate_needs` is a JSON column, so the needs text is `json_encode()`d (split by line) before insert.

### 20. Evacuation module — "Under Construction" stub replaced
The `/admin/evacuation` pages only rendered the generic "Under Construction" placeholder. Implemented the module on the `evacuation_centers` and `evacuation_occupants` tables: an overview with center statistics (total/open/full/currently-evacuated), a status-filterable center list showing occupancy vs. max capacity with a utilization progress bar, a center registration form (name, address, capacity, status, facilities encoded as JSON), and a detail page with occupancy/utilization stats and transactional resident **check-in / check-out** that keeps `current_occupancy` in sync, guards against duplicate active check-ins, and records `date_out` + status. The residents dropdown query needed `FROM residents r` (the shared `NAME_SQL` references `r.last_name`, which failed with `Unknown column` without the alias). GPS latitude/longitude were later removed from the form, detail view, and store insert per requirements.

### 21. Assets module — "Under Construction" stub replaced
The `/admin/assets` pages only rendered the generic "Under Construction" placeholder. Implemented the module on the `assets` and `maintenance_logs` tables: an overview with asset statistics (total/in-use/under-repair/available + total purchase value), a status-filterable asset list with inline registration form (name, category, purchase date/cost, condition, status, next maintenance, description), and a detail page with asset info, maintenance form (date, description, cost, performed by, next maintenance), and maintenance history table. Recording maintenance is transactional — the maintenance log is inserted and the asset's `current_condition` is set to `fair`, `status` to `under_repair`, and `next_maintenance` updated in one commit. The `create()` method and `/assets/create` route were removed since the registration form is inline on the index page.

### 22. Venue Bookings module — "Under Construction" stub replaced
The `/admin/bookings` pages only rendered the generic "Under Construction" placeholder. Implemented the module on the `venue_bookings` table: an overview with booking statistics (total/pending/confirmed + collected fees), a status-filterable booking list (venue, booker resident, schedule, purpose, payment, status), and transactional **confirm / cancel** actions (pending→confirmed; pending/confirmed→cancelled with payment set to `refunded`; invalid transitions rejected). The confirm/cancel routes accept a `{id}` param that maps to the controller method's `$id`. A "New Booking" form was later added (the initial version only listed bookings) — stored via a new `POST bookings/store` handler with inline validation against the residents table, creating bookings as `pending`.

### 23. Livelihood module — "Under Construction" stub replaced
The `/admin/livelihood` pages only rendered the generic "Under Construction" placeholder. Implemented the module: an overview with statistics (job postings, active jobs, applications, farmers), a filterable **job postings** page (active/expired) with an inline post-a-job form (company, position, salary range, expiry, description, requirements) and a **farmer registry** page with statistics (total/active farmers, farm hectares), a status-filterable registry listing farm size/crops/livestock, and a registration form. Farmer registration rejects a resident already in the registry (dropdown options for registered farmers are disabled too), and requires a `\PDO::FETCH_COLUMN` global-namespace prefix — without it PHP 8 resolves `PDO` to `Controllers\Admin\PDO` and throws `Class not found`. Routes follow the `{id}`→`$id` parameter-mapping convention; a new `POST livelihood/farmers/store` route was added for the registration form.

### 24. Compliance & Transparency module — "Under Construction" stub replaced
The `/admin/compliance` pages only rendered the generic "Under Construction" placeholder. Implemented the module on the `transparency_documents` table (full-disclosure documents: annual budget, income/expenditure, NTA utilization, procurement, awards, monthly collections, annual report): an overview with statistics (documents, posted, fiscal years covered, expired needing re-post), a type-filterable document ledger (title, type badge, fiscal year/quarter, posted date, valid-until, status), and an **upload form** that validates title/type/year/status and the file extension (PDF/Word/Excel/images), moves the upload into `public/uploads/transparency/` (auto-generated filename), and records the `uploads/transparency/...` path in the DB. Two bugs surfaced: the download link must use the `asset()` helper (which prefixes `/public/`) — `url()` resolves to `/BIMSS/...` and ran through the router to a 404 — and PHP 8 namespace resolution again (fixed with `\PDO::FETCH_COLUMN`). Uploads are served directly by Apache's rewrite `!-f` rule, so posted documents open under `/BIMSS/public/uploads/transparency/...`.

---

## Database

**86 tables** across 14 domain groups, defined in `migrations/001_full_schema.sql`:

- Core/Auth (`users`, `roles`, `permissions`, `audit_logs`, `email_logs`)
- Geographic/Demographic (`regions` → `barangays`, `puroks`, `households`, `residents`)
- Relationships (`resident_links`, `move_in_out`, `pets`, `vehicles`)
- Documents (`document_types`, `document_requests`, `clearances`, `certificates`, `approval_workflow`)
- Appointments, Complaints, Blotter
- Finance (`chart_of_accounts`, `budgets`, `income_records`, `expense_records`, `official_receipts`, `tax_ledgers`)
- Health & Welfare (`maternal_records`, `immunization_records`, `senior_pwd_profiles`, `disease_surveillance`)
- Peace & Order (`kp_cases`, `kp_hearings`, `kp_cfa`, `tanod_schedules`, `cctv_cameras`)
- DRRM (`disaster_events`, `evacuation_centers`, `rdana_*`, `relief_inventory`)
- Infrastructure & Livelihood (`assets`, `venue_bookings`, `job_postings`, `farmer_registry`)
- Notifications (`notifications`, `bulletins`, `emergency_alerts`)
- Transparency (`transparency_documents`)

**Conventions:** every table has `id`, `created_at`, `updated_at`, `deleted_at` (soft delete), InnoDB, utf8mb4_unicode_ci, FK indexes, ENUMs for fixed values, `DECIMAL(12,2)` for money.

---

## Security

- **bcrypt** password hashing (`password_hash` / `password_verify`)
- **Prepared statements** (PDO) against SQL injection
- **CSRF tokens** on every form
- **RBAC** — granular role → permission checks
- **Audit logs** — actions tracked with user, timestamp, IP
- Input validation (Validator) + output escaping (`e()`)
- Soft deletes to preserve records

---

## Accessibility & Inclusivity

A core requirement — the system is built for users of all ages and abilities:

| Feature | Implementation |
|---------|----------------|
| Font scaling (to 200%) | CSS `rem`/`--font-scale` + JS toggle |
| Dark mode | CSS custom properties toggled by attribute |
| High contrast | Separate stylesheet (WCAG AAA) |
| Text-to-Speech | Web Speech API, read-aloud buttons |
| Voice input | Web Speech Recognition on text fields |
| Keyboard nav | Skip links, focus states, tab order |
| Language toggle | `lang/` JSON-style PHP files, instant switch |
| Kiosk mode | Large tile layout for touchscreens |

---

## Roadmap

- **Phase 2:** Master Data (Residents, Households, Puroks CRUD with geo-tagging, resident linking, move-in/out)
- **Phase 3:** Documents & Certificates (workflow, multi-signature, QR validation, renewals)
- **Phase 4:** Finance & Treasury (budget utilization, tax ledger, official receipts, public transparency)
- **Phase 5:** Health & Welfare (BHERT, maternal/child, senior/PWD, 4Ps, disease surveillance)
- **Phase 6:** Peace & Order (blotter, Lupon/KP full workflow, tanod scheduling, CCTV, visitor logs)
- **Phase 7:** DRRM (evacuation, RDANA, relief inventory, hazard mapping, emergency alerts)
- **Phase 8:** Infrastructure & Livelihood (assets, venue bookings, jobs, farmers)
- **Phase 9:** Full public portal + accessibility polish (GIS map, charts, kiosk)
- **Phase 10:** Smart features (captain widgets, PDF reports, backups, hardening)

---

*BIMS v1.0.0 — built with ❤️ for Philippine barangays.*
