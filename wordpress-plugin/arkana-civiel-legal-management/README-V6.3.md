# V6.3 Litigation Management

## Objective
Provide a litigation-specific operational layer for individual litigation matters while reusing the V5 Matter and security model.

## Foundation
`[arkana_litigation_360 matter_id="123"]` renders a Matter's litigation event timeline.

Events are stored as `ac_litigation_event` and linked to an `ac_matter` through `_aclm_matter_id`.

Supported event concept is intentionally extensible, including:
- filing / registration
- hearing
- mediation
- evidence submission
- legal submission
- decision
- appeal
- execution
- settlement
- other case milestones

## Security
Event creation and retrieval call the V5.12 Matter authorization service when available. Creation is audit-logged.

## Production limitations
This is a foundation, not a complete court-case system. Before production it needs centralized authorization, nonce-protected admin/frontend write forms, structured date validation, court/case-number fields, parties, advocates/team members, hearing reminders, document/evidence links, chronology rules, outcome tracking, and secure document delivery.
