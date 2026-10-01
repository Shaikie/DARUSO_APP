# DARUSO Communication Engine

## Overview

The communication engine is the core of the DARUSO platform. It supports targeted communication from leadership to students, between leaders, and from students back to leadership.

## Communication Types

### Announcements
Information intended for an audience.

**Properties:**
- title, content, author
- priority (low, normal, high, urgent)
- status (draft, review, published, expired, archived)
- target audience (via audience rules)
- publication status, published_at, expiry date
- attachments
- timestamps

**Lifecycle:**
```
draft → review → published → expired/archived
```

### Notifications
Direct or targeted attention.

**Properties:**
- title, message, sender, recipient
- priority (low, normal, high, urgent)
- read/unread state (read_at timestamp)
- related object (polymorphic)
- timestamps

## Targeting System

### Audience Types

| Type | Description |
|------|-------------|
| all_students | Every student |
| all_leaders | Every leader |
| college | Specific college |
| school_faculty | Specific school/faculty |
| programme | Specific programme |
| year_of_study | Specific year |
| hostel | Specific hostel |
| ministry | Specific ministry |
| committee | Specific committee |
| position | Specific position |
| individual | Specific user(s) |

### Audience Rules

Audience rules are stored as rules, not individual recipient rows:

```
audience_rules
├── audience_type (enum)
├── audience_value (string, nullable)
└── resolved_at (timestamp)
```

**Example:** "All students" = one rule with `audience_type = all_students`, `audience_value = null`

**Example:** "Hostel A students" = one rule with `audience_type = hostel`, `audience_value = Hostel A`

**Example:** "Student X, Y, Z" = one rule with `audience_type = individual`, listing specific users

### Resolution Strategy

When an announcement is published:
1. Resolve audience rules to determine recipients
2. For large audiences (all_students, college, etc.) — store as rules, resolve on read
3. For small audiences (individual, committee) — may create notification rows immediately

## Notification Delivery

### Phase 1: Database Notifications
- Notifications stored in database
- Unread count shown in UI
- Mark as read, mark all as read

### Future Channels
- Email (Phase 8)
- SMS (Phase 8)
- WhatsApp (Phase 8)
- Push notifications (Phase 8)

Notification business logic is decoupled from delivery channels.

## Read/Unread Tracking

- `read_at` timestamp on notifications
- Unread count: `WHERE recipient_id = ? AND read_at IS NULL`
- Mark as read: `UPDATE notifications SET read_at = NOW() WHERE id = ?`
- Mark all as read: `UPDATE notifications SET read_at = NOW() WHERE recipient_id = ? AND read_at IS NULL`
- Never delete notification history when read

## Privacy

- Sensitive student information is never exposed in public announcements
- Individual notifications are only visible to the recipient
- Audience rules determine visibility, not just roles
- A leader's permission to create announcements does not grant access to all student records
