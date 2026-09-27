# V6.8 Reporting

## Objective
Create a management-reporting layer over V5/V6 operational data.

## Shortcode
`[arkana_reports_360]`

## Foundation report
The current management report summarizes:
- Clients
- Matters
- Open workflows
- Active retainers
- Tasks
- Deadlines
- Litigation events

## Access
The foundation is restricted to administrators, Managing Partners, and Partners.

## Architecture
Reporting is a read-only orchestration layer and does not duplicate domain records.

## Planned report families
- Matter portfolio report
- Litigation status report
- Retainer performance report
- Lawyer workload report
- SLA report
- Deadline report
- Billing/revenue report
- Client activity report
- Monthly Managing Partner report

## Production requirements
Before production, reports need centralized object-level authorization, date/range filters, normalized status definitions, pagination/export controls, efficient aggregate queries/indexes, audit logging for exports, and privacy controls for confidential legal data.
