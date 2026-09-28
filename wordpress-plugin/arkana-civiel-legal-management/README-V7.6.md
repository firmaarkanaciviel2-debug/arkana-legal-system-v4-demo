# V7.6 AI Legal Knowledge Base

## Objective
Create a controlled internal legal-knowledge retrieval layer that can later ground AI responses in approved Arkana Civiel sources.

## Shortcode
`[arkana_ai_knowledge_base query="..." ]`

## Current foundation
- Internal `ac_legal_knowledge` content type
- Search/retrieval registry
- Source type/reference metadata
- Provider-neutral AI run handoff through V7.0

## Intended source classes
- Firm-approved legal precedents/templates
- Internal SOPs and policies
- Client/matter materials only where explicitly authorized
- Public legal materials captured with source and version metadata

## Safety
Knowledge retrieval is not legal advice. Production RAG must enforce source authorization, document versioning, jurisdiction/date metadata, citation/page references, confidentiality boundaries, prompt-injection defenses, retrieval filtering, and human verification of legal propositions.
