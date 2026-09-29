# V7.8 AI Client Assistant

## Objective
Provide a client-scoped AI assistant foundation with strict authorization and read-only behavior.

## Shortcode
`[arkana_ai_client_assistant client_id="123"]`

## Current context
- Client identity/name
- Client-linked Matters
- Client-linked Retainers where supported

## Access model
Firm management/lawyer roles may access authorized client context. A client user may access only the Client ID linked to their WordPress user via `_aclm_client_id`.

## Safety boundary
The assistant does not automatically provide binding legal advice, change records, send communications, approve workflows, or make commitments on behalf of Arkana Civiel.

## Production requirements
Before external client release: dedicated client portal permissions, strict object-level authorization, confidential-data minimization, conversation isolation, provider retention controls, prompt-injection defenses, human escalation, disclaimers, citation/source display, rate limiting, abuse monitoring, and explicit approval for any client-impacting action.
