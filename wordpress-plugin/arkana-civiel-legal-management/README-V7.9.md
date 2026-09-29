# V7.9 AI Security & Guardrails

## Objective
Define a centralized safety policy for all Arkana Civiel AI assistants before production release.

## Default policy
- Read-only by default
- External AI provider disabled by default
- Client isolation
- Matter-level object authorization
- Human approval for writes
- Audit required
- Prompt-injection review required
- Rate limiting required
- Legal-advice disclaimer required
- Source attribution required

## Foundation helpers
- `policy()` returns the centralized policy contract.
- `authorize($scope, $object_id)` performs baseline AI and Matter authorization.
- `sanitize_prompt($prompt)` removes HTML and limits prompt size.
- `audit_event($event, $context)` delegates to the V6.9 audit layer when available.

## Important limitation
This is a guardrail foundation, not a claim of production security. Final release requires integration tests, real endpoint authorization, nonce/CSRF protection, immutable audit storage, provider controls, secret management, rate limiting, file scanning, prompt-injection testing, output validation, privacy review, and penetration/security testing.
