<?php

namespace App\Enums;

/**
 * Canonical audit verbs. Free-form strings remain accepted so that additional
 * actions can be recorded without a code change, but these constants keep the
 * common cases consistent.
 */
enum AuditAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Published = 'published';
    case Archived = 'archived';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Assigned = 'assigned';
    case Forwarded = 'forwarded';
    case StatusChanged = 'status_changed';
    case RoleAssigned = 'role_assigned';
    case RoleRemoved = 'role_removed';
    case PermissionGranted = 'permission_granted';
    case PermissionRevoked = 'permission_revoked';
    case SettingsUpdated = 'settings_updated';
    case Login = 'login';
    case Logout = 'logout';
    case LoginFailed = 'login_failed';
    case PasswordChanged = 'password_changed';
    case DocumentUploaded = 'document_uploaded';
    case NotificationSent = 'notification_sent';
}
