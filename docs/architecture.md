# DARUSO System Architecture

## Overview

DARUSO is a centralized digital communication, information management, leadership coordination, and student engagement platform for a university student government organization.

## Technology Stack

- **Backend:** PHP 8.4+, Laravel 13+
- **Frontend:** Blade, Bootstrap 5, Bootstrap Icons, Vanilla JS, Alpine.js (lightweight)
- **Database:** PostgreSQL 18+
- **Authentication:** Laravel's standard authentication
- **Authorization:** Laravel Policies, Gates, Role/Permission architecture
- **Infrastructure:** Nginx, PHP-FPM, PostgreSQL, Git

## Core Architectural Principles

1. **Targeted Communication First** — Every communication must answer "Who needs to see this?"
2. **Privacy by Design** — Student information is sensitive; access only what is necessary
3. **Database-Driven Organization** — No hard-coded ministries, positions, or structures
4. **Historical Preservation** — Leadership terms preserve historical context
5. **Thin Controllers** — Business logic in Services, Actions, and Form Requests
6. **Authorization at Every Level** — Never assume hiding a button is authorization

## Application Structure

```
app/
├── Models/              # Eloquent models with relationships, casts, scopes
├── Http/
│   ├── Controllers/     # Thin controllers
│   ├── Requests/        # Form Request validation
│   └── Resources/       # API resources (future)
├── Policies/            # Authorization policies
├── Services/            # Business logic services
├── Actions/             # Single-action classes
├── Notifications/       # Laravel notifications
└── Enums/               # PHP 8.1+ enums for statuses, priorities

database/
├── migrations/          # PostgreSQL migrations
├── seeders/             # Development seed data
└── factories/           # Model factories for testing

resources/
├── views/
│   ├── layouts/         # Base layouts (app, auth)
│   ├── components/      # Reusable Blade components
│   ├── student/         # Student-facing views
│   └── leader/          # Leader-facing views
└── css/, js/            # Bootstrap 5, custom styles

routes/
├── web.php              # Main routes
└── (future api.php)     # API routes for mobile app
```

## User Model Design

Single `users` table for authentication. Profiles extend users:

```
users (authentication)
├── student_profiles (1:1) — academic info, registration number
└── leader_profiles (1:1) — position, ministry, committee assignments
```

A user may be a student, a leader, or both. No duplicate authentication.

## Organizational Structure

```
leadership_terms
├── positions (President, VP, Secretary General, etc.)
├── ministries (Academic, Hostel, Welfare, etc.)
├── committees (Finance, Disciplinary, etc.)
├── leader_assignments (user_id, position_id, term_id)
└── committee_members (user_id, committee_id, term_id)
```

All organizational entities are database-driven and configurable.

## Communication Engine

The core of the platform. Two primary communication objects:

1. **Announcements** — Information intended for an audience (public, targeted)
2. **Notifications** — Direct/targeted attention (individual, group)

Both support:
- Target audience rules (not individual recipient rows for large audiences)
- Priority levels
- Read/unread tracking
- Attachments
- Timestamps and lifecycle

## Targeting System

Audience resolution uses rules, not individual rows:

```
audience_rules
├── audience_type (all_students, college, programme, year, hostel, ministry, committee, individual)
├── audience_value (foreign key or null for "all")
└── resolved_at (timestamp when rule was last resolved)
```

For "all students" — store as a rule, not 30,000 recipient rows.
For individuals — store as rules with `audience_type = individual`.

## Security

- CSRF protection (Laravel default)
- Authorization policies on every resource
- Form Request validation
- Mass assignment protection
- Secure password hashing (bcrypt)
- Rate limiting on authentication
- Secure file upload validation
- IDOR protection through policies
- Audit logging for sensitive actions

## Future Extensibility

- Redis queues (designed for, not implemented yet)
- Email/SMS/WhatsApp notification channels
- API for mobile application
- Advanced search with PostgreSQL full-text
- Reporting and analytics
