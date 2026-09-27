# V6.1 Client 360

## Objective
Create a single operational view of a client while reusing V5 domain records.

## Shortcode
`[arkana_client_360 client_id="123"]`

For a client user, the shortcode can fall back to `_aclm_client_id` on the current WordPress user.

## Current foundation
The profile summarizes:
- Client name
- Matters
- Legal Requests
- Retainers
- Invoices

## Architecture
Client 360 is an orchestration/read layer. It does not duplicate Matter, Request, Retainer or Invoice records.

## Security boundary
V6.1 must use V5.12 object-level authorization consistently. The current foundation includes an access gate, but it is intentionally not the final authorization implementation for all client-linked objects. Before production, authorization must be centralized and applied per object/query, not inferred from the existence of a first Matter.

## Next enhancements
- Client profile/contact information
- Matter timeline
- Tasks and deadlines
- Documents with secure delivery
- Billing detail
- Retainer utilization
- Client communications
- Audit timeline
- client-specific KPIs
