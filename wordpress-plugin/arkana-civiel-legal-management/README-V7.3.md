# V7.3 AI Retainer Assistant

## Objective
Create a retainer-scoped AI assistant that assembles authorized retainer context for future analysis of service activity, Matters, tasks, and deadlines.

## Shortcode
`[arkana_ai_retainer_assistant retainer_id="123"]`

## Context
- Retainer title/status
- Related Matters
- Related tasks
- Related deadlines

The relationship keys `_aclm_retainer_id` and `_aclm_retainer_service_id` are supported as the current foundation evolves.

## Security
The assistant requires V7.0 AI authorization and a privileged firm role. Runs are delegated to the V7.0 auditable run layer.

## Read-only boundary
V7.3 does not modify retainer records, contracts, billing, Matters, tasks, deadlines, workflows, or client communications.

## Important limitation
The foundation does not yet call an AI provider or make billing/legal decisions. Production use requires provider integration, source attribution, confidentiality controls, prompt-injection defenses, structured validation, and human review for recommendations affecting client service or contractual matters.
