# V7.15 — Retainer & Client Portfolio Intelligence

V7.15 adds a read-only analytics foundation for Managing Partner portfolio oversight.

## Dashboard questions
- How many active clients are in the portfolio?
- How many active retainers exist?
- Which retainers require attention?
- Is the portfolio currently stable or needs attention?

## Design principles
- Analytics are read-only and do not mutate client, matter, retainer, invoice, or task records.
- The module accepts a canonical snapshot so database mapping can be completed during Local WordPress QA.
- No revenue/profit figure is inferred when the source data does not provide it.
- Risk/health labels are operational signals, not legal conclusions.

## Integration contract
Use the `aclm_v715_portfolio_snapshot` filter to supply the canonical client/retainer snapshot.

Expected shape:
- `clients[]`: client records with optional `status`.
- `retainers[]`: retainer records with optional `status` and `health`.

## Next QA
When WordPress Local is available, map the real client/retainer tables or canonical services, verify permissions, and test empty/normal/attention states before exposing portfolio figures to production users.
