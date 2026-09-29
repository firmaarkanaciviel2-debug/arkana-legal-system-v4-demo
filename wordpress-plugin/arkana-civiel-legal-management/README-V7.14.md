# V7.14 — Lawyer Workload & Performance Intelligence

V7.14 adds a non-mutating workload analytics layer for management review.

## Shortcode
`[arkana_lawyer_workload]`

## Inputs
The module consumes the V7.12 snapshot filter `aclm_v712_dashboard_snapshot` and looks for common assignment fields: `lawyer`, `assigned_lawyer`, `assignee`, `owner`, `lawyer_name`, or `assigned_to`.

## Signals
- Open work
- Matters / requests / tasks per assignee
- Overdue items
- Items due within three days
- High / Medium / Normal workload heuristic

## Safety boundary
This is an operational workload signal, not an HR, disciplinary, compensation, or legal-performance decision engine. It does not modify matter, client, request, task, or user records.

## Local QA
1. Activate the module.
2. Confirm access is restricted to authenticated users with `edit_posts` capability.
3. Supply test snapshot data with 2–3 lawyers and mixed matters/requests/tasks.
4. Verify counts and sorting by workload score.
5. Verify overdue and due-soon classification using WordPress site timezone.
6. Verify unassigned records appear under `Unassigned`.
7. Verify empty-state behavior.
8. Confirm no database records are changed.

## Next integration
Replace/augment the adaptive V7.12 snapshot with the canonical Arkana Matter, Request, Task and assignment repositories after V6/V7 local integration QA.