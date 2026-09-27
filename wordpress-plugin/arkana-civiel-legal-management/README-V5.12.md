# V5.12 Audit & Security

## Foundation

V5.12 introduces a centralized audit-event model and reusable Matter/document authorization checks.

### Audit events

`ACLM_V512_Audit_Security::log( $action, $object_type, $object_id, $meta )`

Records:
- acting user ID
- action
- object type and ID
- sanitized metadata
- UTC timestamp
- HMAC-SHA256 hash of the remote IP using the WordPress auth salt

The raw IP is not stored.

### Matter authorization

`can_access_matter()` allows administrators, Managing Partners and Partners; assigned lawyer access; and client access when the user's `_aclm_client_id` matches the Matter's `_aclm_client_id`.

### Document authorization

`secure_document_download_allowed()` requires login, an accessible Matter, and then applies internal-vs-client visibility rules.

## Important production boundary

This is security foundation code, not a complete security certification. Existing V5 modules must be migrated to call these object-level authorization and audit functions on every read/write/delete endpoint. A real file delivery endpoint still needs to be implemented before confidential documents are served. WordPress roles/capabilities must be reviewed against the exact installed V5 role definitions.

Before production, run a full authorization matrix test, CSRF/nonce test, IDOR test, upload/download test, privilege-escalation test, audit integrity review, backup/restore test, and web-server/private-storage configuration review.
