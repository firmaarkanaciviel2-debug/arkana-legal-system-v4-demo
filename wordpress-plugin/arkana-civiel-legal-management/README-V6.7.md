# V6.7 Legal Operations Analytics

## Objective
Turn V5/V6 operational records into a Managing Partner analytics layer without duplicating domain data.

## Shortcode
`[arkana_analytics_360]`

## Foundation metrics
- Active clients
- Matters
- Litigation events
- Active retainers
- Open workflows
- Communications
- Tasks
- Deadlines

## Access
Analytics is restricted to administrators, Managing Partners, and Partners in the foundation.

## Architecture
Analytics is a read/orchestration layer over existing V5/V6 records.

## Important limitation
These are foundation aggregate metrics. Before production, each metric must be consistently permission-filtered, time-window aware, status-normalized, and backed by indexed/query-efficient reporting services. Future versions should add SLA performance, lawyer workload, retainer utilization, revenue, Matter aging, litigation outcomes, trend charts, and date-range filters.
