# V5.13 V4 Migration

## Objective

Move compatible V4 records into the V5 data model without destructive changes to the source.

## Migration principles

1. Inventory first.
2. Dry-run before write.
3. Preserve source IDs through `_aclm_v4_source_id` and `_aclm_v4_source_version`.
4. Never delete or mutate V4 source records during migration.
5. Skip records already migrated.
6. Migrate in small batches.
7. Validate relationships after each batch.
8. Keep an auditable migration log.

## Current implementation

`ACLM_V513_V4_Migration` provides:
- `inventory()` for record counts by V4 post type.
- `plan($source_id, $source_type)` for one-record migration planning.
- `dry_run_batch($source_type, $limit)` for non-destructive batch planning.
- duplicate protection via source ID/version metadata.

Supported model types:
- Client
- Matter
- Legal Request
- Document
- Retainer
- Invoice
- Notification
- Task
- Deadline

## Important limitation

This commit is a **migration foundation, not a live migration**. It does not write target records, copy confidential files, or transform arbitrary custom fields. The actual migration writer should only be enabled after the V4 schema/content has been inventoried and a backup/rollback plan is verified.

## Recommended migration order

Client → Matter → Legal Request → Task/Deadline → Retainer → Invoice → Document → Notification.

Documents should be migrated last and only after V5.12 private-storage and authorization controls are production-ready.
