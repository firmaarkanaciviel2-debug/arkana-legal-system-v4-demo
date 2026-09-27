# V7.2 AI Litigation Assistant

## Objective
Create a Matter-scoped litigation assistant that assembles authorized litigation events, deadlines, and tasks for future AI summarization and analysis.

## Shortcode
`[arkana_ai_litigation_assistant matter_id="123"]`

## Context
- Matter status/title
- Litigation events
- Deadlines
- Tasks

## Security
V7.2 requires V7.0 AI authorization and Matter-level authorization before assembling litigation context. Runs are delegated to the V7.0 auditable run layer.

## Read-only boundary
V7.2 does not change case records, hearing dates, deadlines, tasks, workflow state, or communications.

## Important limitation
The current foundation does not connect to an AI provider or provide legal advice. Production use requires provider integration, structured outputs, source attribution, confidentiality controls, prompt-injection defenses, validation, and human review. Litigation dates and legal conclusions must be verified against authoritative case records/documents by the responsible lawyer.
