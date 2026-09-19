# Arkana Civiel V4 — QA Stage 4 Integration Readiness

Date: 2026-09-19

## Scope
Production integration boundary between the V4 demo frontend and the planned V4 backend/infrastructure.

## Findings
- Frontend demo is intentionally mock-data only.
- No production API client/base URL is configured in the demo repository.
- No PostgreSQL connection exists in the demo repository.
- No Redis/BullMQ connection exists in the demo repository.
- No private S3/MinIO object-storage integration exists in the demo repository.
- No real authentication/session/2FA flow exists in the demo repository.
- No SMTP/WhatsApp provider integration exists in the demo repository.
- Therefore end-to-end integration tests cannot be honestly marked PASS against this demo alone.

## Integration gate matrix

| Gate | Status | Evidence |
|---|---|---|
| Frontend → API | BLOCKED | No API client/backend in demo repo |
| API → PostgreSQL | BLOCKED | No backend/DB in demo repo |
| Authentication/session | BLOCKED | UAT role selector is local simulation |
| Server-side authorization | BLOCKED | Role visibility is frontend simulation |
| Document storage | BLOCKED | No S3/MinIO client in demo repo |
| Redis/queue | BLOCKED | No queue worker in demo repo |
| Email | BLOCKED | No SMTP/provider wiring in demo repo |
| WhatsApp | BLOCKED | No WhatsApp provider wiring in demo repo |
| Audit log | BLOCKED | UI only; no persistent audit API |
| Health/readiness | BLOCKED | Not exposed by demo frontend |
| TLS/reverse proxy | NOT TESTED | Requires deployment infrastructure |
| Backup/restore | NOT TESTED | Requires production-like infrastructure |
| UAT frontend flows | PASS | Covered by QA stages 2–3 |

## QA conclusion
The V4 demo is suitable as a frontend/UAT shell. It is not yet an integrated legal management system deployment. The next production gate requires connecting the consolidated V4 backend to PostgreSQL, Redis, private object storage, authentication, notification providers, and the Portal/Admin frontends, followed by server-side authorization and end-to-end tests.

## Security note
The demo must continue to display mock-data boundaries and must not be used with real client data or production credentials.
