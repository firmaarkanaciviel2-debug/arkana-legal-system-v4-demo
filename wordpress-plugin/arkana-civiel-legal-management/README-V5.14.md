# V5.14 Final QA

## Release gate

V5 is considered a QA baseline only after the following are verified in a clean WordPress Local environment with test data.

### Functional
- Login/logout and role routing
- Managing Partner dashboard
- Partner/Lawyer access
- Client portal isolation
- Matter CRUD and assignment
- Legal Request lifecycle
- Tasks and deadlines
- Private document metadata
- Retainer and invoice records
- Notifications
- Audit events
- V4 migration inventory/dry-run

### Authorization
- Client A cannot read Client B's Matter
- Client A cannot read Client B's documents
- Lawyer without Matter assignment cannot access restricted Matter data
- Internal documents remain unavailable to clients
- Notification feeds are user-scoped
- Billing records respect finance/management permissions

### Security
- Nonce/CSRF checks on writes
- Capability checks
- Object-level authorization
- Input sanitization and allow-lists
- No raw IP persistence in audit records
- Private files are not exposed by a public URL
- Upload validation before confidential-document use

### Migration
- V4 backup exists
- Inventory counts reconciled
- Dry-run reviewed
- Source IDs preserved
- Relationships validated after each batch
- Rollback procedure documented

### Performance
- No fatal PHP errors
- No repeated unbounded queries on dashboards
- Pagination for growing lists
- Cron/notification jobs do not block requests

## Known release blockers

Do not call V5 production-ready until secure document delivery is implemented, every V5 read/write/delete path is covered by object-level authorization, role/capability mappings are verified on the actual WordPress installation, and the complete QA matrix passes.

## Test data rule

Use synthetic clients, matters, documents and invoices during Local testing. Do not import real client confidential information into the development environment until security gates pass.
