# Arkana Civiel V5 — LocalWP Deployment & Test Environment

## Goal
Run V5 safely on a local WordPress installation using synthetic test data before any real client data is introduced.

## Environment
- WordPress Local (LocalWP)
- PHP version supported by the installed WordPress release
- MySQL/MariaDB supplied by Local
- HTTPS enabled locally where available

## Installation

1. Create a fresh LocalWP WordPress site named `arkana-v5-test`.
2. Log in to WordPress Admin.
3. Install/activate the Arkana Civiel Legal Management plugin from the repository plugin directory.
4. Confirm there are no PHP fatal errors.
5. Visit Settings/Permalinks and save once to refresh rewrite rules.
6. Create only synthetic users and records.

## Test users

Create separate accounts for:
- Managing Partner
- Partner
- Lawyer
- Paralegal
- Client A
- Client B

Do not reuse administrator credentials as a Client or Lawyer test account.

## Synthetic dataset

Create:
- Client A and Client B
- Matter A assigned to Lawyer A and Client A
- Matter B assigned to Lawyer B and Client B
- Internal Document A linked to Matter A
- Client-visible Document A linked to Matter A
- Retainer A and Invoice A for Client A
- Task/Deadline records for Matter A
- Notifications for Client A and Client B

## Mandatory authorization tests

1. Client A must not access Matter B.
2. Client A must not access Client B documents.
3. Client A must not access internal documents.
4. Lawyer A must not access Matter B unless assigned/authorized.
5. Managing Partner may access both matters.
6. Notification feeds must remain user-specific.
7. Billing data must not leak between clients.

## Migration test

Do not migrate production/V4 confidential data. Use a cloned synthetic dataset. Run V5.13 inventory and dry-run first. Verify source IDs and relationship mapping before any write migration.

## Security test

Record pass/fail for:
- nonce/CSRF protection
- capability checks
- object-level authorization
- IDOR attempts
- private document URL access
- upload type/size validation
- privilege escalation
- audit event creation

## Exit criteria

V5 Local is a release candidate only when all mandatory tests pass, no fatal PHP errors remain, authorization isolation passes, and secure document delivery is implemented before confidential documents are introduced.
