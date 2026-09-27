# V5.11 Notifications

## Foundation

The notification module provides per-user in-app notifications for:
- general
- request
- task
- deadline
- matter
- billing
- document

Use `[arkana_notifications]` on a protected page to show the current user's notification feed.

Notifications are stored with a target user ID, type, optional reference ID, and read/unread flag.

## Security baseline

- Notification reads are filtered to the logged-in user's ID.
- `mark_read()` rejects notifications owned by another user.
- Notification content is sanitized on creation.
- Notification type is allow-listed.

## Production gaps

V5.11 is an in-app foundation. V5.12 should add audit events, secure mutation endpoints, nonce/CSRF protection for UI actions, notification deduplication, retention/cleanup rules, queueing for bulk events, email/WhatsApp delivery only through approved integrations, delivery status, retry handling, and object-level authorization for referenced Matters/Requests/Tasks/Billing records.
