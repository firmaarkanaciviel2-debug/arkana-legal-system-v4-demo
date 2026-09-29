# V5.2 implementation notes

The V5.2 permissions layer is a companion plugin so the V5.1 foundation remains isolated and reversible during Local QA.

## Local activation

1. Copy both PHP files into `wp-content/plugins/arkana-civiel-legal-management/`.
2. Activate **Arkana Civiel Legal Management**.
3. Activate **Arkana Civiel Legal Management — V5.2 Permissions**.
4. Create test accounts for each role.
5. Verify internal roles can access only the modules defined by `V5.2-ROLE-PERMISSION-MATRIX.md`.
6. Verify Client Admin and Client User cannot access internal CPT admin screens or REST objects.

The companion layer can be disabled independently if V5.2 QA finds a regression.
