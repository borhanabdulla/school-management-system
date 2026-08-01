# Stop Report: Monthly Category Key Stability

Date: 2026-02-22
Owner: Grading Domain
Trigger: PR-06 MCP Gate

## Problem
Monthly aggregation relies on `category_key` (and sometimes name) to map monthly grades to template categories. The key is user-editable and can be regenerated from the label. This makes historical aggregation non-deterministic when labels or keys change.

## Evidence
- docs/grading/PR-06_mcp_facts.md
- app/Domains/Academic/Grading/Services/GradeSyncService.php:221
- app/Domains/Academic/Grading/Services/GradeSyncService.php:228

## Impact
- Renaming or changing the key can change historical totals.
- Matching by name can silently map to the wrong template category.

## Stop Decision
Do not implement a key-based fix until a stable identifier exists.

## Proposed Schema Change (Requires Approval)
Add a stable foreign key on monthly grades:
- Add `monthly_grades.template_category_id` (nullable initially).
- Backfill using existing mappings by category_key + term + subject + grade.
- Update aggregation to rely on `template_category_id` instead of label/key.
- Enforce `template_category_id` on new writes.

## Backfill Strategy (Outline)
1) For each `monthly_grades` row:
   - Resolve `term_id` via `gradebook_months`.
   - Resolve `subject_id` via `course_offerings`.
   - Resolve `grade_id` via `class_sections`.
2) Find mapping in `monthly_category_mappings`:
   - Match on `academic_year_id`, `term_id`, `grade_id`, `subject_id`, `category_key`.
3) Set `template_category_id` on `monthly_grades`.
4) Validate no missing mappings; stop if any missing.

## Alternative (Lower Impact, App-Level)
Lock `category_key` after creation and remove the editable UI field. This still requires
re-validation and does not fix historical rows where keys already drifted.

## Resolution
Implemented in PR-06:
- Added `monthly_grades.template_category_id` with backfill.
- Aggregation now uses `template_category_id` for deterministic results.
