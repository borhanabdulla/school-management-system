# PR-06 — Grading Historical Integrity (Term-Aware + Deterministic)

Status: Completed
Priority: High
Depends On: PR--0, PR--1, PR-04

Scope (Exact Files)
- app/Domains/Academic/Grading/Services/GradeSyncService.php
- app/Domains/Academic/Grading/Actions/AggregateGradebookToTemplateMarksAction.php
- app/Domains/Academic/Grading/Actions/RecordMonthlyGradeAction.php
- app/Domains/Academic/Grading/Models/MonthlyGrade.php
- app/Livewire/Teacher/Grading/SmartGradeBook.php
- database/migrations/2026_02_22_000001_add_template_category_id_to_monthly_grades.php
- database/migrations/2026_02_22_000002_update_monthly_grades_unique_index_for_template_category.php
- tests/Feature/Domains/Academic/Grading/Services/GradeSyncServiceTest.php
- docs/decisions/grading-monthly-category-and-attendance-term.md

Evidence
- app/Domains/Academic/Grading/Services/GradeSyncService.php:220
- app/Domains/Academic/Grading/Actions/AggregateGradebookToTemplateMarksAction.php:67
- app/Domains/Academic/Grading/Actions/RecordMonthlyGradeAction.php:39
- database/migrations/2026_02_22_000001_add_template_category_id_to_monthly_grades.php
- database/migrations/2026_02_22_000002_update_monthly_grades_unique_index_for_template_category.php
- docs/decisions/grading-monthly-category-and-attendance-term.md
- docs/grading/PR-06_mcp_facts.md
- docs/grading/stop_report_monthly_category_key.md

Impact
- مطابقة الفئات بالاسم أو مفاتيح غير ثابتة قد تغيّر النتائج التاريخية بعد إعادة تسمية الفئة.

Tasks
- [x] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [x] تنفيذ MCP Gate كما في docs/decisions/grading-monthly-category-and-attendance-term.md وتوثيق الأدلة.
- [x] إذا لم يتوفر: كتابة Stop Report واقتراح schema change لحفظ template_category_id.
- [x] إضافة template_category_id إلى monthly_grades مع backfill.
- [x] تحديث التجميع ليستخدم template_category_id بدلاً من category_key.
- [x] تحديث مسار كتابة الدرجات لتثبيت template_category_id ومنع الكتابة بدون مابينغ.
- [x] تحديث unique index ليعتمد template_category_id.
- [x] ضمان term-aware aggregation في أي تجميع يكتب نتائج سنوية.

Tests
- [x] Test يثبت أن إعادة تسمية الفئة لا تغيّر التجميع.
- [x] Test يفشل عند وجود تطابق متعدد للفئة (no silent fallback).

Risks
- قد يتطلب migration + backfill إذا لم يتوفر مفتاح ثابت.

Rollback
- إيقاف التغييرات مع استمرار تسجيل تحذيرات حتى توقيع القرار.

Definition of Done
- التجميع تاريخي وحتمي ولا يعتمد على أسماء قابلة للتغيير.
