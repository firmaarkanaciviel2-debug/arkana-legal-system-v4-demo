# V5.10 Retainer & Billing

## Retainer model

Fields:
- Client
- Start date
- End date
- Fee
- Currency (IDR/USD/SGD)
- Billing cycle (monthly/quarterly/annual/custom)
- Status (draft/active/suspended/expired/terminated)

## Invoice model

Fields:
- Client
- optional Retainer ID
- Amount
- Due date
- Status (draft/issued/partially_paid/paid/overdue/void)

## Security baseline

- Nonce-protected admin writes
- Capability checks
- Sanitized date/status inputs
- Allow-list validation
- Numeric amount validation

## Production gaps

This is a foundation, not an accounting system. Before production add server-side Client↔Retainer integrity, immutable invoice numbering, tax/PPN configuration as required, payment records, partial-payment allocation, credit notes, currency precision rules, timezone handling, finance permissions, audit trail, and integrations only after authorization/security review. Do not store card data in WordPress.
