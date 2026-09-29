# V5 Local QA Matrix

| ID | Test | Expected | Status |
|---|---|---|---|
| AUTH-01 | Client A opens Matter A | Allowed | PENDING |
| AUTH-02 | Client A opens Matter B | Denied | PENDING |
| AUTH-03 | Client A opens Client B document | Denied | PENDING |
| AUTH-04 | Client A opens internal document | Denied | PENDING |
| AUTH-05 | Lawyer A opens Matter A | Allowed | PENDING |
| AUTH-06 | Lawyer A opens Matter B | Denied unless assigned | PENDING |
| AUTH-07 | Managing Partner opens Matter A/B | Allowed | PENDING |
| NOTIF-01 | Client A sees own notifications | Allowed | PENDING |
| NOTIF-02 | Client A sees Client B notifications | Denied | PENDING |
| BILL-01 | Client A sees own billing | Allowed | PENDING |
| BILL-02 | Client A sees Client B billing | Denied | PENDING |
| AUDIT-01 | Authorized mutation creates audit event | Logged | PENDING |
| MIG-01 | V4 inventory runs | Counts returned | PENDING |
| MIG-02 | Dry run does not mutate source | No source changes | PENDING |
| DOC-01 | Public/guessable document URL cannot bypass auth | Denied | BLOCKED until secure delivery endpoint |
| SEC-01 | Unauthorized POST rejected | Denied | PENDING |
| SEC-02 | Privilege escalation attempt rejected | Denied | PENDING |
| PERF-01 | Dashboard loads with demo dataset | No fatal/error | PENDING |
