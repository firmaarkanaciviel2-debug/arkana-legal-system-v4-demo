# V6.2 Matter 360

## Objective
Create a single operational view of a Matter while reusing the V5 Matter domain and its related records.

## Shortcode
`[arkana_matter_360 matter_id="123"]`

## Current foundation
The view summarizes:
- Matter name
- linked Client
- assigned Lawyer
- Tasks
- Deadlines
- Documents
- Legal Requests

## Security
Matter access is delegated to `ACLM_V512_Audit_Security::can_access_matter()` when available. Unauthorized users receive an access-denied response.

## Architecture
Matter 360 is an orchestration/read layer. It does not duplicate Matter, Task, Deadline, Document, or Legal Request records.

## Planned expansion
- Matter status and priority
- chronology/timeline
- litigation/court information
- team members
- document activity
- task/deadline timeline
- client communication timeline
- retainer/billing summary
- audit timeline
- KPIs and risk indicators
