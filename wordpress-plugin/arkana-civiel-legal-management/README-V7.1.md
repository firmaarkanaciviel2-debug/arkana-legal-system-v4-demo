# V7.1 AI Matter Assistant

## Objective
Create a Matter-scoped AI assistant that can assemble authorized Matter context for future AI summarization and question answering.

## Shortcode
`[arkana_ai_matter_assistant matter_id="123"]`

## Context
The foundation can collect:
- Matter title/status/client link
- related tasks
- related deadlines
- related litigation events

## Security
V7.1 first checks V7.0 agent authorization and Matter-level authorization. Context is only assembled for an authorized Matter. Agent runs are delegated to V7.0 and remain auditable.

## Read-only boundary
V7.1 does not modify Matter data, tasks, deadlines, litigation events, communications, or workflow state.

## Important limitation
This foundation does not yet connect to an AI provider or generate legal advice. Provider integration, prompt construction, structured responses, source attribution, prompt-injection defenses, confidentiality controls, and human-review safeguards must be added before real legal use.
