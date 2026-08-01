# PR-06 MCP Facts (Grading Historical Integrity)

Date: 2026-02-22

## Monthly category key stability
- Admin UI allows editing label and key fields:
  - resources/views/components/grading/monthly-tab.blade.php:63
  - resources/views/components/grading/monthly-tab.blade.php:70
- Save validation requires label and max_score only (key optional):
  - app/Livewire/Admin/Grading/GradingSettings.php:760
  - app/Livewire/Admin/Grading/GradingSettings.php:761
- Keys can be auto-generated from labels and de-duped on save:
  - app/Domains/Academic/Grading/Models/GradebookSettings.php:84
  - app/Domains/Academic/Grading/Models/GradebookSettings.php:103
- Monthly mappings are keyed by category_key:
  - database/migrations/2026_02_05_000003_create_monthly_category_mappings_table.php:15
  - database/migrations/2026_02_05_000003_create_monthly_category_mappings_table.php:22

Conclusion: category_key is user-editable and can change; it is not an immutable key.

## Template category stable key
- template_categories.mapping_type exists as enum, not unique:
  - database/migrations/2026_01_12_144143_add_mapping_type_to_template_categories_table.php:10

## Attendance term isolation / course_offering uniqueness
- Unique constraint includes term_id, but term_id is nullable:
  - database/migrations/2025_11_19_186400_create_course_offerings_table.php:16
  - database/migrations/2026_02_03_231357_make_course_offerings_term_aware.php:19
- DB check (2026-02-22): `SELECT COUNT(*) total, SUM(CASE WHEN term_id IS NULL THEN 1 ELSE 0 END) null_terms FROM course_offerings;` => null_terms = 0.
- Health validator warns when CourseOffering has null term_id:
  - app/Domains/Academic/Grading/Services/Validators/DataFlowSanityValidator.php:46

Conclusion: term_id is expected but not enforced as NOT NULL; invariant is application-level only.
