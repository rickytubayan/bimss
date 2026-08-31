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
4. Configure DB credentials in `config/database.php` (defaults: host `localhost`, user `root`, no password, db `bims`).
5. Open **http://localhost/BIMSS/**

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
| Layouts, CSS, accessibility JS | ✅ Done |
| Residents, Households, Documents, Finance, Health, Blotter, Lupon, DRRM, Assets, Compliance, Reports | 🚧 Controllers scaffolded, views pending |
| GIS map, charts, QR, kiosk | 📝 Planned |

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
