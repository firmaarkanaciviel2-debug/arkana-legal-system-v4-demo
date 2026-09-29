# V7.0 Arkana Civiel AI Agent Core

## Objective
Introduce an internal AI Agent orchestration layer without granting the AI unrestricted database access or write permissions.

## Shortcode
`[arkana_ai_agent]`

## Principle
The agent is provider-neutral in V7.0. No external AI provider is called by this foundation.

The architecture is:

User → Role → Agent Authorization → Tool Registry → Authorized Domain Data → AI Provider Adapter (future)

## Read-only tools
- matter_summary
- matter_deadlines
- matter_tasks
- retainer_summary
- litigation_timeline
- communication_summary
- management_metrics

## Security model
- Only authenticated privileged firm users can use the foundation.
- Matter-scoped runs check the V5.12 Matter authorization service when available.
- Each run is recorded as `ac_ai_agent_run` and audit logged when the V5.12 audit service is available.
- V7.0 exposes read-only tools only. No external messages, record mutation, document deletion, or workflow transitions are available to the AI.

## Next steps
V7.1 will implement the Matter Assistant/tool adapters. Later releases can add provider integration, structured tool calls, approval-gated actions, prompt-injection defenses, output validation, rate limits, and AI-specific audit controls.
