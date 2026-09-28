# V7.13 — Matter Intelligence & Risk Dashboard

V7.13 adds a non-mutating intelligence layer over the V7.12 dashboard snapshot.

## Capabilities
- Normalises matter/request records.
- Calculates a simple risk tier from status and due date.
- Aggregates Critical / Warning / Normal counts.
- Creates an Attention Required metric.
- Provides a Priority Queue for non-normal matters.

## Safety boundary
This is a foundation, not a legal-risk decision engine. Risk labels are operational signals and must not be treated as legal advice or a final case assessment. No record is modified by this module.

## Integration
The module listens to `aclm_v712_dashboard_snapshot` and augments it. The existing V7.12 provider remains the source of truth for live data.

Shortcode: `[arkana_matter_risk_dashboard]`

## QA when Local WordPress is available
1. Activate the plugin/module.
2. Confirm the dashboard renders for authenticated users.
3. Verify unauthenticated access is rejected.
4. Inject test matters with past, <=3-day, and future due dates.
5. Confirm Critical/Warning/Normal counts.
6. Verify no matter/client/request record is mutated.
7. Confirm empty-state behaviour when V7.12 has no live records.
