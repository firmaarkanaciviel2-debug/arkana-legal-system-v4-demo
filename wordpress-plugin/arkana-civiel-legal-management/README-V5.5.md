# V5.5 Client Portal

## Scope

A WordPress shortcode foundation for the Arkana Civiel client portal.

Shortcode:

`[arkana_client_portal]`

## Access model

- Login is required.
- Only `ac_client_admin` and `ac_client_user` roles can render the portal.
- A user must be linked to a Client post via user meta `_aclm_client_id`.
- Matters are displayed only when `_aclm_client_id` on the Matter matches the logged-in user's client ID.

## Current UI

- Portal header
- Client-account binding state
- Matter list
- Matter title and current status
- Empty-state handling

## Not production-ready yet

This is a foundation. It does not yet expose documents, messages, invoices, appointments, or arbitrary matter IDs. Before production, add server-side capability checks to every object action, stronger client/matter referential integrity, private document delivery, audit logging, CSRF protection for write actions, and explicit authorization on REST endpoints.

## Local setup

Create a WordPress page such as `Client Portal`, place `[arkana_client_portal]` in the content, create a test client user, and set that user's `_aclm_client_id` to the ID of a test `ac_client` post. Create test Matters with the same `_aclm_client_id` and verify only those matters appear.
