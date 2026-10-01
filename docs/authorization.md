# DARUSO Authorization Model

## Overview

Authorization operates at multiple levels. No single "super admin" role bypasses everything. Responsibilities are delegated.

## Roles

Roles are database-driven and configurable:

- **System Administrator** — Technical administration, users, permissions, system settings
- **Secretary General** — General communication, selected leadership information
- **Ministry Leader** — Ministry-specific complaints, announcements, student communication
- **Committee Leader** — Committee meetings, committee communication
- **Student** — Receive announcements, submit complaints, view own data

## Permissions

Granular permissions control actions:

### Announcements
- `announcement.create` — Create announcements
- `announcement.edit` — Edit own announcements
- `announcement.edit_any` — Edit any announcement
- `announcement.publish` — Publish announcements
- `announcement.delete` — Delete announcements

### Notifications
- `notification.create` — Create notifications
- `notification.send` — Send targeted notifications

### Students
- `student.view` — View student list
- `student.view_sensitive` — View sensitive student information
- `student.view_hostel` — View hostel-related student information

### Complaints
- `complaint.view` — View complaints
- `complaint.assign` — Assign complaints to ministries/leaders
- `complaint.update` — Update complaint status
- `complaint.resolve` — Resolve complaints

### Meetings
- `meeting.create` — Create meetings
- `meeting.edit` — Edit meetings
- `meeting.manage` — Manage all meetings

### Events
- `event.create` — Create events
- `event.edit` — Edit events
- `event.manage` — Manage all events

### Documents
- `document.create` — Upload documents
- `document.publish` — Publish documents
- `document.delete` — Delete documents

### Administration
- `leader.manage` — Manage leadership assignments
- `committee.manage` — Manage committee membership
- `ministry.manage` — Manage ministries
- `system.settings` — System settings
- `audit.view` — View audit logs

## Authorization Principles

1. **Access only what is necessary** — A hostel leader sees hostel-related student info, not all student data
2. **Backend enforcement** — Never assume hiding a button is authorization
3. **Policy-based** — Every resource has a policy
4. **Audit trail** — All authorization-relevant actions are logged

## Phase 1 Authorization

In Phase 1, a simplified role system:
- `is_leader` boolean on users (or a `role` column)
- Basic middleware to distinguish students from leaders
- Full permission system in Phase 2+

## Future: Permission Architecture

```
roles → permissions (many-to-many)
users → roles (many-to-many)
policies → enforce permissions per resource
gates → enforce permissions globally
```
