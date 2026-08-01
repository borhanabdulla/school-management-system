# PR-02 — Active Year/Term Contract (SSOT Verification + Tests)

Status: Completed
Priority: High
Depends On: PR--0, PR--1, PR-00

Scope (Exact Files)
- app/Infrastructure/Context/AcademicContextService.php
- app/Domains/Academic/AcademicYear/Actions/ActivateAcademicYearAction.php
- app/Domains/Academic/Term/Actions/ActivateTermAction.php
- app/Domains/Academic/Term/Actions/ReopenTermAction.php
- database/migrations/2026_01_31_154655_add_unique_active_year_constraint_to_academic_years.php
- database/migrations/2026_02_11_000001_fix_active_year_partial_index.php
- database/migrations/2026_01_31_155915_add_unique_active_term_per_year_constraint_to_terms.php
- tests/Feature/Academic/ActiveYearTermContractTest.php

Evidence
- app/Domains/Academic/AcademicYear/Actions/ActivateAcademicYearAction.php:54
- app/Domains/Academic/AcademicYear/Actions/ActivateAcademicYearAction.php:57
- app/Domains/Academic/Term/Actions/ActivateTermAction.php:18
- app/Domains/Academic/Term/Actions/ActivateTermAction.php:38
- app/Domains/Academic/Term/Actions/ReopenTermAction.php:17
- app/Infrastructure/Context/AcademicContextService.php:122
- app/Infrastructure/Context/AcademicContextService.php:135
- app/Infrastructure/Context/AcademicContextService.php:199
- database/migrations/2026_01_31_154655_add_unique_active_year_constraint_to_academic_years.php:12
- database/migrations/2026_01_31_155915_add_unique_active_term_per_year_constraint_to_terms.php:13
- database/migrations/2026_02_11_000001_fix_active_year_partial_index.php:14

Impact
- عقد SSOT غير واضح أو غير مختبر يجعل تغييرات السنة/الترم عرضة للتنافس والـ cache stale.
- القيود الجزئية تعتمد على دعم قاعدة البيانات للـ partial indexes.

Revalidation (2026-02-18)
- Updated `docs/academic-context-audit.md` to reflect enum-based status definitions.
- Commands:
  - `rg -n "status\\s*===|status\\s*==|where\\('status'" app/Infrastructure/Context/AcademicContextService.php app/Domains/Academic/AcademicYear/Actions/ActivateAcademicYearAction.php app/Domains/Academic/Term/Actions/ActivateTermAction.php app/Domains/Academic/Term/Actions/ReopenTermAction.php`
  - `nl -ba app/Infrastructure/Context/AcademicContextService.php | sed -n '120,230p'`

Tasks
- [x] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [x] إضافة اختبارات تثبت أن تفعيل السنة/الترم لا يعتمد على cached context (stale cache gate).
- [x] تحديث AcademicContextService لاستخدام Enums في شروط status بدلاً من strings.
- [x] إضافة اختبارات DB guard لقيود active year/term.
- [x] توثيق قرار SSOT في docs/academic-context-audit.md (تحديث بسيط إن لزم).

Tests
- [x] `php artisan test --filter=AcademicYearTest`
  - Result: 13 passed (23 assertions), 2.18s.
  - Covers stale cache activation: `it_activates_pending_year_even_when_active_year_cache_is_stale`.
- [x] `php artisan test --filter=TermActivationTest`
  - Result: 5 passed (10 assertions), 1.82s.
  - Covers stale cache + single active term constraint.
- [x] `php artisan test --filter=HasAcademicScopeTest`
  - Result: 1 passed (1 assertion), 1.39s.
  - Covers stale cache autofill for academic year.
  - Warnings: PHPUnit doc-comment metadata deprecation notices (no failures).

Risks
- اختلاف دعم الـ partial index بين SQLite وMySQL قد يتطلب fallback.

Rollback
- إرجاع تغييرات AcademicContextService وإزالة الاختبارات الجديدة عند الضرورة.

Definition of Done
- اختبارات SSOT تعمل وثابتة، والـ context يستخدم enums فقط.
