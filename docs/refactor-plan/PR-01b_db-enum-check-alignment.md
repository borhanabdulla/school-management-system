# PR-01b — DB Enum/CHECK Alignment (Crash-Risk Hardening)

Status: Completed
Priority: High
Depends On: PR--0, PR--1, PR-00, PR-01

Scope (Exact Files)
- app/Domains/Academic/Student/Enums/GuardianRelationship.php
- app/Domains/Academic/Student/Enums/EnrollmentStatus.php
- app/Domains/Academic/Student/Enums/EnrollmentType.php
- app/Domains/Academic/Student/Models/StudentEnrollment.php
- app/Domains/Shared/Services/Dashboard/MainDashboardDataService.php
- app/Domains/Shared/Services/Dashboard/Sections/EnrollmentDashboardService.php
- app/Domains/Academic/Promotion/Services/PromotionService.php
- app/Domains/Academic/Results/Enums/ResultDecision.php
- app/Domains/Academic/Results/Enums/FinalResultStatus.php
- docs/refactor-plan/DB-AUDIT_REPORT.md

Evidence
- docs/refactor-plan/DB-AUDIT_REPORT.md
- app/Domains/Academic/Student/Enums/GuardianRelationship.php:5
- database/migrations/2025_11_19_186000_create_student_guardian_table.php:18
- app/Domains/Academic/Student/Enums/EnrollmentStatus.php:5
- database/migrations/2025_11_19_188200_create_student_enrollments_table.php:25
- app/Domains/Academic/Results/Enums/ResultDecision.php:5
- database/migrations/2025_12_29_134541_create_annual_results_table.php:33
- app/Domains/Academic/Results/Enums/FinalResultStatus.php:5
- database/migrations/2025_12_28_233700_create_control_system_tables.php:93

Revalidation (2026-02-18)
- DB snapshot confirmed from `database/database.sqlite` (non-empty).
- Current counts: tables=105, tables_with_check=30.
- Commands:
  - `ls -l database/database.sqlite`
  - `SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';`
  - `SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND (lower(sql) LIKE '%check (%' OR lower(sql) LIKE '%check(%');`

Preflight Results (2026-02-18)
- student_guardian.relationship: father=36 (no other values found).
- student_enrollments.status: active=36 (no other values found).
- annual_results.decision: no rows.
- final_results.status: no rows.

Impact
- منع سقوط النظام عند الكتابة/القراءة بسبب عدم تطابق Enum مع CHECK.

Tasks
- [x] Evidence Revalidation: تحديث الدليل داخل `docs/refactor-plan/DB-AUDIT_REPORT.md`.
- [x] قرار موثّق لكل mismatch: DB CHECK هو المصدر النهائي، والـ Enums تُعدّل لتتطابق معه.
- [x] Preflight Queries: استخراج القيم الحالية في الأعمدة الأربعة قبل أي تغيير.
- [x] تعديل Enums لتتطابق مع DB CHECK:
  - student_guardian.relationship (إزالة قيم غير مدعومة في DB).
  - student_enrollments.status (إزالة new/returning من EnrollmentStatus).
  - annual_results.decision (إزالة absent).
  - final_results.status (إضافة incomplete/pending).
- [x] إضافة EnrollmentType لاستخدام enrollment_type بدل خلطه مع EnrollmentStatus.
- [x] إذا استلزم الأمر: خطوات تنظيف بيانات مرافقة (script/command منفصل، ليس داخل migration).
- [x] تحديث الدوكيمونتيشن داخل هذا الملف بعد التنفيذ.

Tests
- [x] `php artisan test`
  - Result: 433 passed (1099 assertions), 34.31s.
  - Warnings: PHPUnit doc-comment metadata deprecation notices (no failures).

Risks
- وجود بيانات فعلية بقيم خارج النطاق الجديد قد يفشل الهجرة؛ يجب التحقق قبل التنفيذ.

Rollback
- تراجع عن migrations الجديدة.
- إرجاع enums إلى الوضع السابق إن لزم، مع توثيق السبب.

Definition of Done
- لا يوجد mismatch بين Enum و CHECK في الجداول الأربعة المستهدفة.
- Tests تمرّ بدون ValueError أو CHECK failures.
