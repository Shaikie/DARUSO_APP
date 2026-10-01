# DARUSO Database Design

## Overview

PostgreSQL database with normalized relationships, foreign keys, indexes, and constraints. UUIDs used where justified, auto-increment for internal tables.

## Core Tables

### users
Central authentication table.
- id (UUID, PK)
- name (string)
- email (string, unique)
- password (string)
- phone (string, nullable)
- email_verified_at (timestamp)
- remember_token (string)
- created_at, updated_at

### student_profiles
Academic information for students.
- id (UUID, PK)
- user_id (UUID, FK → users, unique)
- registration_number (string, unique)
- college (string)
- school_faculty (string)
- programme (string)
- year_of_study (integer)
- hostel (string, nullable)
- gender (string, nullable)
- status (enum: active, suspended, graduated, expelled)
- created_at, updated_at

### leader_profiles
Leadership information.
- id (UUID, PK)
- user_id (UUID, FK → users, unique)
- bio (text, nullable)
- created_at, updated_at

### leadership_terms
Historical leadership terms.
- id (UUID, PK)
- name (string, e.g., "2025/2026")
- start_date (date)
- end_date (date)
- is_active (boolean)
- created_at, updated_at

### positions
Configurable positions.
- id (UUID, PK)
- name (string)
- description (text)
- hierarchy_level (integer, for ordering)
- created_at, updated_at

### ministries
Configurable ministries.
- id (UUID, PK)
- name (string)
- description (text)
- created_at, updated_at

### committees
Configurable committees.
- id (UUID, PK)
- name (string)
- description (text)
- created_at, updated_at

### leader_assignments
Links users to positions within terms.
- id (UUID, PK)
- user_id (UUID, FK → users)
- position_id (UUID, FK → positions)
- ministry_id (UUID, FK → ministries, nullable)
- leadership_term_id (UUID, FK → leadership_terms)
- created_at, updated_at

### committee_members
Links users to committees within terms.
- id (UUID, PK)
- user_id (UUID, FK → users)
- committee_id (UUID, FK → committees)
- leadership_term_id (UUID, FK → leadership_terms)
- role_in_committee (string, nullable)
- created_at, updated_at

### roles
Role definitions.
- id (UUID, PK)
- name (string, unique)
- description (text)
- created_at, updated_at

### permissions
Permission definitions.
- id (UUID, PK)
- name (string, unique)
- description (text)
- created_at, updated_at

### role_permissions
- role_id (UUID, FK → roles)
- permission_id (UUID, FK → permissions)
- PK (role_id, permission_id)

### user_roles
- user_id (UUID, FK → users)
- role_id (UUID, FK → roles)
- PK (user_id, role_id)

## Communication Tables

### announcements
- id (UUID, PK)
- title (string)
- content (text)
- author_id (UUID, FK → users)
- priority (enum: low, normal, high, urgent)
- status (enum: draft, review, published, expired, archived)
- published_at (timestamp, nullable)
- expires_at (timestamp, nullable)
- requires_approval (boolean)
- approved_by (UUID, FK → users, nullable)
- created_at, updated_at

### audience_rules
Targeting rules for announcements and notifications.
- id (UUID, PK)
- audience_type (enum: all_students, all_leaders, college, school_faculty, programme, year_of_study, hostel, ministry, committee, position, individual)
- audience_value (string, nullable — foreign key value or null for "all")
- created_at, updated_at

### announcement_audience_rules
- announcement_id (UUID, FK → announcements)
- audience_rule_id (UUID, FK → audience_rules)
- PK (announcement_id, audience_rule_id)

### notifications
- id (UUID, PK)
- title (string)
- message (text)
- sender_id (UUID, FK → users)
- recipient_id (UUID, FK → users)
- priority (enum: low, normal, high, urgent)
- read_at (timestamp, nullable)
- related_type (string, nullable — polymorphic)
- related_id (UUID, nullable)
- created_at, updated_at

### notification_audience_rules
For targeted notifications to groups.
- notification_id (UUID, FK → notifications)
- audience_rule_id (UUID, FK → audience_rules)
- PK (notification_id, audience_rule_id)

## Service Tables

### complaints
- id (UUID, PK)
- title (string)
- description (text)
- category (string)
- attachment_path (string, nullable)
- status (enum: submitted, under_review, forwarded, in_progress, awaiting_student, resolved, closed, rejected)
- creator_id (UUID, FK → users)
- assigned_ministry_id (UUID, FK → ministries, nullable)
- assigned_leader_id (UUID, FK → users, nullable)
- created_at, updated_at

### complaint_history
Audit trail for complaint workflow.
- id (UUID, PK)
- complaint_id (UUID, FK → complaints)
- actor_id (UUID, FK → users)
- action (string)
- old_status (string, nullable)
- new_status (string, nullable)
- notes (text, nullable)
- created_at

### meetings
- id (UUID, PK)
- title (string)
- description (text)
- organizer_id (UUID, FK → users)
- meeting_date (date)
- meeting_time (time)
- venue (string)
- status (enum: scheduled, ongoing, completed, cancelled)
- agenda (text, nullable)
- attachment_path (string, nullable)
- created_at, updated_at

### meeting_audience_rules
- meeting_id (UUID, FK → meetings)
- audience_rule_id (UUID, FK → audience_rules)
- PK (meeting_id, audience_rule_id)

### events
- id (UUID, PK)
- title (string)
- description (text)
- organizer_id (UUID, FK → users)
- event_date (date)
- venue (string)
- status (enum: upcoming, ongoing, completed, cancelled)
- attachment_path (string, nullable)
- created_at, updated_at

### event_audience_rules
- event_id (UUID, FK → events)
- audience_rule_id (UUID, FK → audience_rules)
- PK (event_id, audience_rule_id)

### documents
- id (UUID, PK)
- title (string)
- description (text)
- category (string)
- uploader_id (UUID, FK → users)
- visibility (enum: public, students, leaders, ministry, committee, group)
- file_path (string)
- file_name (string)
- file_mime_type (string)
- file_size (integer)
- version (integer)
- created_at, updated_at

## Audit Tables

### audit_logs
- id (UUID, PK)
- actor_id (UUID, FK → users, nullable)
- action (string)
- target_type (string)
- target_id (UUID)
- old_values (jsonb, nullable)
- new_values (jsonb, nullable)
- ip_address (string, nullable)
- user_agent (string, nullable)
- created_at

## Indexes

- users: email (unique)
- student_profiles: registration_number (unique), user_id (unique)
- leader_profiles: user_id (unique)
- announcements: status, published_at, author_id
- notifications: recipient_id, read_at, sender_id
- complaints: status, creator_id, assigned_ministry_id, assigned_leader_id
- meetings: meeting_date, organizer_id
- events: event_date, organizer_id
- audit_logs: actor_id, target_type, target_id, created_at

## Constraints

- All foreign keys with ON DELETE CASCADE where appropriate
- Check constraints on status enums
- Unique constraints on assignment tables
- NOT NULL on required fields
