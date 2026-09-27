# V6.0 Legal Operations Core

## Objective

V6.0 begins the Legal Operations Platform layer above the V5 domain modules. It provides a common operational summary without replacing Matter, Client, Request, Task, Deadline, Document, Retainer, Invoice, Notification, or Audit modules.

## Current foundation

Use `[arkana_legal_operations]` on a protected WordPress page to render an operational summary for the logged-in user.

The summary currently exposes counts for:
- Clients
- Matters
- Legal Requests
- Tasks
- Deadlines
- Retainers
- Invoices
- Notifications for the current user

## Architecture principle

V6 should orchestrate V5 rather than duplicate its data. Future V6 modules should consume shared services and object-level authorization from V5.12.

## Next V6 work

- V6.1 Client 360
- V6.2 Matter 360
- V6.3 Litigation Management
- V6.4 Corporate Retainer Management
- V6.5 Legal Workflow Automation
- V6.6 Communication Center
- V6.7 Analytics
- V6.8 Reporting
- V6.9 Advanced Security
- V6.10 Release Candidate

## Current limitation

This is a foundation only. Counts are not yet permission-filtered for every role/object and should not be treated as a production management dashboard until the V5 authorization services are consistently applied to each underlying query.
