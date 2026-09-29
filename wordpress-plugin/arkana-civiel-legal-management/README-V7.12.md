# V7.12 Live Management Dashboard

V7.12 upgrades the V7.11 visual dashboard with an adaptive live-data layer.

## What is live
The dashboard discovers public WordPress post types whose names/labels indicate Matter, Request, Client, Retainer, or Task records and queries published records directly. Existing business logic is not replaced.

## Integration hook
`aclm_v712_dashboard_snapshot` can be used by the final Arkana data layer to provide authoritative counts and recent records from custom tables or domain services.

## Security
Dashboard access is limited to management/lawyer capabilities. AI controls remain governed by V7.9 guardrails and V7.10 release gates.

## Shortcode
`[arkana_live_management_dashboard]`

## Important limitation
This is a live-data integration foundation. It does not claim that every existing Arkana V5/V6 data source is already wired into the dashboard. The final integration should connect the canonical Matter, Client, Request, Retainer, and Task repositories through the provided filter/service boundary and then run Local WordPress QA.
