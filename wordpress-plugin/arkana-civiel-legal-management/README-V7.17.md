# V7.17 Deadline, SLA & Calendar Intelligence

## Objective
Provide a read-only operational layer for deadlines, SLA signals and calendar priorities.

## Shortcode
`[arkana_deadline_intelligence]`

## Current signals
- overdue
- critical: due within 1 day
- warning: due within 7 days
- upcoming
- priority event list
- WordPress timezone metadata

## Supported source candidates
`ac_deadline`, `ac_task`, `ac_hearing`, `ac_legal_request` using `_aclm_due_at` or `_aclm_deadline` metadata where present.

## Integration contract
`aclm_v717_deadline_snapshot`

## Safety
Read-only. It does not reschedule, complete, close, delete, or otherwise mutate records. It is an operational signal, not legal advice.

## Production requirements
Map the canonical deadline/hearing/SLA schema, distinguish calendar dates from exact timestamps, account for court/holiday/business-day rules where applicable, enforce matter/client authorization, define SLA semantics, add auditability, and test timezone/DST/locale behavior.
