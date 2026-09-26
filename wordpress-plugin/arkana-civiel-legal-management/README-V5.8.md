# V5.8 Tasks & Deadlines

## Task model

Each Task can be linked to:
- Matter
- optional Legal Request
- Assignee
- Status
- Priority
- Due date

Statuses: To Do, In Progress, Blocked, Done, Cancelled.

## Deadline model

Each Deadline can be linked to:
- Matter
- Owner
- Deadline date
- Status

Statuses: Open, Completed, Extended, Missed.

## Shortcode

`[arkana_task_dashboard]` renders the logged-in user's assigned tasks.

## Security baseline

- Nonce-protected admin writes
- Capability checks
- Integer validation for IDs
- Allow-list validation for statuses and priorities
- Date format validation
- Dashboard reads tasks assigned to the current user

## Production gaps

Before production: enforce Matter↔Client integrity for every relation; verify Task/Deadline assignees have appropriate roles; add server-side object-level authorization for reads/updates/deletes; implement timezone-aware deadline calculations; add reminders/notification queue; audit status changes; and ensure client users cannot access internal task/deadline data.
