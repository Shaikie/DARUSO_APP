# DARUSO Development Roadmap

The phases below describe the original build order. They are **not** delivery
gates: the documented system is implemented as a whole, and this file is
retained as a record of what has been built.

## Phase 1: Foundation ✅

- [x] Laravel setup with PostgreSQL
- [x] Authentication (login, register, password reset)
- [x] Users, students, basic roles/permissions
- [x] Base layouts (app, auth)
- [x] Student dashboard
- [x] Leader dashboard
- [x] Basic navigation and UI structure

## Phase 2: DARUSO Organization ✅

- [x] Leadership terms
- [x] Positions
- [x] Ministries
- [x] Committees
- [x] Leader assignments
- [x] Committee membership
- [x] Organizational authorization

## Phase 3: Communication Engine ✅

- [x] Announcements (CRUD, lifecycle)
- [x] Announcement targeting
- [x] Notifications (targeted)
- [x] Read/unread state
- [x] Attachments
- [x] Priorities

## Phase 4: Student Services ✅

- [x] Complaints (submit, track)
- [x] Complaint categories
- [x] Complaint assignments
- [x] Status workflow
- [x] Complaint history
- [x] Student notifications

## Phase 5: Meetings and Events ✅

- [x] Meetings (CRUD, audience)
- [x] Events (CRUD, audience)
- [x] Target audiences
- [x] Reminders
- [x] Attachments

## Phase 6: Documents and Reports ✅

- [x] Document management
- [x] Document visibility
- [x] Reports
- [x] Activity statistics

## Phase 7: Audit and Administration ✅

- [x] Audit logs
- [x] System settings
- [x] Advanced permissions
- [x] Leadership management
- [x] Administrative controls

## Phase 8: External Notification Channels ⏳

Business logic is decoupled from delivery (`NotificationDispatcher`), and the
email/SMS toggles exist in system settings, but no external provider is
configured. These remain future work and are intentionally not implemented:

- [ ] Email notifications
- [ ] SMS notifications
- [ ] WhatsApp notifications
- [ ] Push notifications