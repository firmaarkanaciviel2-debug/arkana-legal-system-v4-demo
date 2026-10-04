# Arkana Civiel — WordPress Local V7 Test Package

This branch is prepared specifically for Local WordPress QA.

## Correct folder
Copy this entire folder:

`wordpress-plugin/arkana-civiel-legal-management/`

to:

`wp-content/plugins/arkana-civiel-legal-management/`

## Important
Do **not** activate `arkana-civiel-legal-management.php` directly when using the Local V7 Test Bundle. Activate:

`Arkana Civiel Legal Management — Local V7 Test Bundle`

The bundle boots the V5 core first and then loads the `v7-*.php` modules in filename order.

## Why this bundle exists
The repository also contains the older V4/Next.js application at the repository root. The root ZIP is therefore not the correct artifact for a WordPress Local plugin test. This bundle removes that ambiguity.

## Minimum environment
- WordPress 6.4+
- PHP 8.1+
- Local WordPress site running

## First QA
1. Activate the Local V7 Test Bundle.
2. Confirm `Legal Management` appears in the WordPress admin sidebar.
3. Confirm the seven V5 objects are available: Client, Matter, Request, Task, Deadline, Document, Retainer.
4. Confirm the V5.1 relationship meta box appears on those objects.
5. Test with dummy data only.
6. Do not upload real client/litigation documents until V6 security backlog is completed.

## V7 shortcode smoke tests
- `[arkana_ai_agent]`
- `[arkana_financial_intelligence]`
- `[arkana_deadline_intelligence]`

Access to management/AI signals remains role-gated and the V7.16/V7.17 intelligence modules are read-only foundations.
