# Admin Guide

Access the admin panel at `/admin`. Only users with the admin role can access it.

## Dashboard

Overview statistics:
- **Users** - Total count with breakdown by role
- **Projects** - Total count (MART vs standard)
- **Cases** - Total participant cases
- **Entries** - Total data submissions
- **Media** - Total uploaded files

Stats are cached for 5 minutes.

## Settings

### Project Settings

| Setting | Description |
|---------|-------------|
| Enable MART Projects | Allow/disallow MART project creation for all users |
| Max Studies Per User | Limit how many projects each user can create |
| API V2 Cutoff Date | Projects before this date use legacy API format |

### Security Settings

| Setting | Description |
|---------|-------------|
| API Max Login Attempts | Failed attempts before lockout (web/API) |
| API Lockout Duration | Lockout time in minutes |
| MART Max Login Attempts | Failed attempts before lockout (mobile app) |
| MART Lockout Duration | Lockout time in minutes |

## Activity Log

Read-only log of user actions. Cannot be modified or deleted through the admin panel.

## Granting Admin Access

Admin access is controlled via database roles. Contact a system administrator to grant admin privileges to a user account.
