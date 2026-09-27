# V6.6 Communication Center

## Objective
Create a Matter-linked communication layer so client, lawyer and firm communications can be associated with the relevant Matter instead of being scattered across unrelated channels.

## Shortcode
`[arkana_communication_360 matter_id="123"]`

## Foundation
Messages are stored as `ac_communication` and linked to a Matter through `_aclm_matter_id`.

Visibility modes:
- `internal` — firm/internal communication
- `client` — communication intended for the client side

Optional `_aclm_recipient_user_id` supports recipient-specific delivery.

## Security
Matter access is checked through the V5.12 authorization service when available. Communication creation is audit logged. Client-visible recipient-specific messages are filtered for the current user unless the user is a privileged firm role.

## Important limitation
This is a foundation, not a complete chat system. Production work must add nonce-protected send forms/endpoints, centralized object-level authorization, attachment security, message editing/deletion policy, notification delivery, read receipts, threading, rate limits, and secure handling of confidential documents/links.
