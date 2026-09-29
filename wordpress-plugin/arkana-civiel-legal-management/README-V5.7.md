# V5.7 Legal Request

## Client intake

Use shortcode `[arkana_legal_request_form]` on a protected client-facing page.

The form is available only to logged-in `ac_client_admin` and `ac_client_user` users with `_aclm_client_id` assigned.

## Workflow

New → Triaged → Assigned → In Progress → Waiting Client → Resolved → Closed

Alternative terminal state: Rejected.

## Request types

- Consultation
- Contract Review
- Corporate
- Litigation
- Employment
- Other

## Priority

Low / Normal / High / Urgent.

## Security baseline

- Nonce-protected submission
- Role gate
- Client ID binding from server-side user meta, not from the form
- Title/description sanitization
- Allow-list validation for request type

## Production gaps

V5.7 is a foundation. Production must add: server-side Client↔Matter referential integrity, request ownership/visibility checks for every read/update/delete action, rate limiting or abuse controls, audit events, notification queue, attachment security, and explicit REST authorization.
