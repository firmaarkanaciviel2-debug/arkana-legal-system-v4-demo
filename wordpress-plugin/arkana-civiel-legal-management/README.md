# Arkana Civiel Legal Management — V5

WordPress-centered Legal Management System for Arkana Civiel.

## Goal

Turn the existing Arkana Civiel public WordPress site into one platform with:

- public firm website;
- internal Legal Management dashboard;
- client portal;
- matter/case monitoring;
- legal requests;
- tasks and deadlines;
- private documents;
- retainer management;
- audit/security controls.

## V5.1 Data Model & Relationships

The foundation now includes seven WordPress-native operational objects:

- `ac_client` — Klien
- `ac_matter` — Perkara
- `ac_request` — Permintaan Hukum
- `ac_task` — Tugas
- `ac_deadline` — Deadline
- `ac_document` — Dokumen
- `ac_retainer` — Retainer

Core relationship model:

```text
Client
 ├── Matter
 │    ├── Legal Request
 │    ├── Task
 │    ├── Deadline
 │    └── Document
 └── Retainer
```

Matter also supports Partner and Lead Lawyer assignment. Tasks can have an assignee. Documents carry an access-level field that will become the basis for private document authorization in V5.9.

Relationships are currently stored as sanitized WordPress post meta IDs. This keeps the first version portable and easy to back up while we validate the workflow locally.

## Local installation

1. Create/open your WordPress Local site.
2. Copy `wordpress-plugin/arkana-civiel-legal-management/` into `wp-content/plugins/` or zip the plugin folder.
3. Activate **Arkana Civiel Legal Management** from **Plugins**.
4. Open **Legal Management** in the WordPress admin menu.
5. Create test users for the internal roles and create test Clients/Matter records.
6. Verify the **Arkana Civiel — Data & Relasi V5.1** meta box appears on each module.

## Important production rule

This alpha does **not** yet implement private document delivery, matter-level authorization, audit logging, encryption/key management, client-to-matter isolation, or production-grade notifications. Do not store real client or litigation documents until those controls are completed and security-tested.

## V5 roadmap

- V5.0 Foundation — complete
- V5.1 Data model and relations — foundation complete
- V5.2 Role and permission matrix
- V5.3 Managing Partner dashboard
- V5.4 Lawyer dashboard
- V5.5 Client portal
- V5.6 Matter management
- V5.7 Legal request workflow
- V5.8 Tasks and deadlines
- V5.9 Private document management
- V5.10 Retainer and billing
- V5.11 Notifications
- V5.12 Audit/security
- V5.13 V4 migration
- V5.14 Final QA and production readiness
