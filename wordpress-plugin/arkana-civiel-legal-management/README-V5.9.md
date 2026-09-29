# V5.9 Private Documents

## Objective

Documents for legal matters must not rely on a guessable public uploads URL as the authorization boundary.

## Current foundation

The module adds metadata to `ac_document`:

- `_aclm_matter_id`
- `_aclm_client_id`
- `_aclm_document_access`: `internal` or `client`

It also defines a configurable private root using `ACLM_PRIVATE_DOCUMENT_ROOT`, with a fallback under `wp-content/aclm-private-documents/` for development.

## Important limitation

This commit does **not** yet implement secure file upload/download. It does not claim that the fallback directory is protected from direct HTTP access on every hosting stack. V5.12 must add an authenticated delivery endpoint, authorization checks, download logging, MIME/extension validation, size limits, safe filenames, and web-server protection or storage outside the web root.

## Intended access model

Internal document:
- Managing Partner / Partner / assigned Lawyer / authorized Paralegal according to Matter permissions.
- Never client-visible.

Client-visible document:
- Only the Client users associated with the document's Matter/Client relationship.

## Local QA

Use dummy documents only. Do not upload real client contracts, identity documents, evidence, or confidential correspondence until V5.12 security hardening is complete.
