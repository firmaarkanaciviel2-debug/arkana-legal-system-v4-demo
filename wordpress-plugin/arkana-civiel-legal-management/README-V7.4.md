# V7.4 AI Workflow Agent

## Objective
Add workflow-aware AI orchestration with a strict separation between observation, proposal, approval, and execution.

## Shortcode
`[arkana_ai_workflow_agent matter_id="123"]`

## Read context
- Matter-linked tasks
- Matter-linked deadlines
- Matter-linked workflow instances

## Proposal actions
The foundation recognizes these proposal types:
- `create_task`
- `flag_deadline`
- `request_review`
- `prepare_communication`

Proposals are stored as `ac_ai_agent_run` with `awaiting_approval` status. V7.4 does not execute these actions.

## Safety boundary
AI cannot autonomously modify Matter, Task, Deadline, Workflow, or Communication records. Explicit human approval and a future hardened execution service are required.

## Production requirements
V7.4 needs nonce-protected approval endpoints, centralized object-level authorization, immutable audit events, idempotency, action validation, approval expiry, prompt-injection defenses, rate limits, and a separate execution service before any write action is enabled.
