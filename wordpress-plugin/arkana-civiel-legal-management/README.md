# Arkana Civiel Legal Management — V5

WordPress-centered foundation for the Arkana Civiel Legal Management System.

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

## Current foundation: V5.0.0-alpha.1

The plugin currently provides:

- Custom Post Types: Clients, Matters, Legal Requests, Tasks, Deadlines, Documents, Retainers.
- Custom roles: Managing Partner, Partner, Lawyer, Paralegal, Finance, Client Admin, Client User.
- WordPress admin menu: **Legal Management**.
- Initial dashboard metrics.
- REST visibility for registered objects, ready for later portal/API work.

## Install for development

1. Zip the `arkana-civiel-legal-management` directory.
2. In WordPress: **Plugins → Add New Plugin → Upload Plugin**.
3. Activate the plugin.
4. Open **Legal Management** in the WordPress admin menu.

## Important production rule

This alpha does **not** yet implement private document delivery, matter-level authorization, audit logging, encryption/key management, client-to-matter isolation, or production-grade notifications. Those controls must be completed before sensitive client or litigation documents are stored in the system.

## V5 roadmap

- V5.0 Foundation
- V5.1 Data model and relations
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
