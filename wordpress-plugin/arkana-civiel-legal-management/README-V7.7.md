# V7.7 AI Management Copilot

## Objective
Provide a read-only management intelligence layer for authorized Managing Partners and Partners.

## Shortcode
`[arkana_ai_management_copilot]`

## Current metrics
- Matters
- Open Tasks
- Deadlines
- Retainers
- AI Agent Runs

## Security
V7.7 is restricted to privileged management roles and delegates AI runs to the V7.0 authorization/audit layer.

## Read-only boundary
The Copilot does not alter matters, tasks, deadlines, retainers, billing, users, workflows, or communications.

## Planned intelligence
- workload and capacity signals
- overdue/at-risk Matter detection
- deadline risk summaries
- retainer activity and service-level indicators
- operational trend summaries
- management alerts

## Production requirements
Metrics need role-aware data filtering, tenant/client confidentiality boundaries, deterministic definitions, source attribution, freshness timestamps, anomaly validation, and human review before any management decision or client-impacting action.
