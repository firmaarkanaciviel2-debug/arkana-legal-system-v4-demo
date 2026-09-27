# V6.5 Legal Workflow Automation

## Objective
Create a controlled workflow layer connecting a Matter/Legal Request to operational states and auditable transitions.

## Workflow

`intake → triage → assigned → in_progress → review → partner_approval → client_update → closed`

Only the explicitly defined next state is accepted. Invalid state jumps are rejected.

## Shortcode
`[arkana_workflow_360 workflow_id="123"]`

## Foundation API
- `ACLM_V65_Workflow_Automation::create($matter_id, $request_id, $state)`
- `ACLM_V65_Workflow_Automation::transition($workflow_id, $next_state)`

Workflow instances use `ac_workflow_instance` and link to Matter/Legal Request through metadata.

## Security
Matter authorization is checked before workflow creation and transition when the V5.12 security service is available. Transitions are audit logged.

## Important limitation
This is workflow orchestration foundation, not unattended automation. It does not yet auto-create tasks, assign lawyers, send client messages, calculate SLA timers, or perform partner approvals. Those are planned additions and must retain explicit authorization and auditability.
