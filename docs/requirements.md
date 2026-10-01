# DARUSO Requirements

## Project Overview

DARUSO is a centralized digital communication, information management, leadership coordination, and student engagement platform for a university student government organization.

## Core Objectives

1. Simplify information flow from DARUSO leadership to students
2. Facilitate communication between DARUSO leaders
3. Enable ministry/committee communication with members
4. Support targeted communication to specific groups
5. Provide private, individual communication
6. Enable student feedback through complaints and requests

## User Categories

### Students
- Receive announcements and targeted notifications
- View relevant events and meetings
- Submit complaints and track status
- View student information and representatives
- Manage profile and mark notifications as read

### DARUSO Leaders
- Publish announcements and send targeted notifications
- Communicate internally
- Manage ministry/committee communication
- Organize meetings and events
- Receive and manage student complaints
- View relevant student information per permissions
- Manage documents and monitor communication activity

## Key Features (Phase 1)

### Authentication
- User registration and login
- Password reset
- Session management

### User Management
- Central users table
- Student profiles (academic info)
- Leader profiles (position info)
- Basic role assignment

### Dashboards
- Student dashboard (welcome, notifications, announcements, complaints)
- Leader dashboard (announcements, complaints, meetings, activity)

### Navigation
- Student nav: Dashboard, Announcements, Notifications, Events, Meetings, Complaints, Documents, Representatives, Profile
- Leader nav: Dashboard, Announcements, Notifications, Students, Complaints, Meetings, Events, Documents, Ministries, Committees, Leadership, Reports, Audit Logs

## Non-Functional Requirements

- **Security:** CSRF protection, authorization policies, validation, mass assignment protection, secure file uploads
- **Performance:** Efficient queries, pagination, indexing
- **Usability:** Responsive design, mobile-friendly for students
- **Maintainability:** Clean architecture, Laravel conventions, documented decisions
- **Extensibility:** Designed for future notification channels, API, Redis queues

## Technology Constraints

- PHP 8.4+, Laravel 13+
- PostgreSQL 18+
- Blade + Bootstrap 5 + Bootstrap Icons
- No SPA framework
- No unnecessary packages
