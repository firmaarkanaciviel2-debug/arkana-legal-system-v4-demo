# V7.5 AI Document Intelligence

## Objective
Introduce a permission-aware document intelligence layer without sending document contents to an AI provider yet.

## Shortcode
`[arkana_ai_document_intelligence document_id="123"]`

## Current capabilities
- Resolve document/post metadata
- Identify linked Matter when `_aclm_matter_id` exists
- Enforce Matter authorization before exposing Matter-linked document context
- Create an auditable provider-neutral AI run through V7.0

## Planned capabilities
- PDF/DOCX text extraction
- OCR for scans
- document classification
- structured field extraction
- clause/risk detection
- document summaries
- source/page citations
- version comparison

## Safety boundary
V7.5 does not currently upload document contents to an external AI provider and does not generate legal conclusions. Production implementation must address confidentiality, encryption, provider data retention, access control, prompt injection in document text, malicious files, file-size/type validation, redaction, source attribution, and human review.
