# V7.16 Financial & Billing Intelligence

## Objective
Provide a read-only financial intelligence layer for authorized management/finance users.

## Shortcode
`[arkana_financial_intelligence]`

## Signals
- invoice count
- outstanding signal
- overdue signal
- paid signal
- currency/source metadata

## Integration contract
`aclm_v716_financial_snapshot`

The filter is the canonical integration point for the actual Arkana Civiel billing/invoice domain model.

## Safety
This module does not create, edit, delete, approve, or mark invoices/payments as paid. It must not infer revenue, profit, tax, or payment status when canonical financial data is unavailable.

## Production requirements
Map the real billing schema, define accounting semantics, enforce object-level authorization, add audit logging, validate currency/tax treatment, prevent client cross-visibility, and test all financial calculations before enabling management reporting.
