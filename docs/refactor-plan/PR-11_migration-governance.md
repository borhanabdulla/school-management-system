# PR-11 — Migration & DB Governance

Status: Planned
Priority: Medium
Depends On: PR--0, PR--1, PR-02

Scope (Exact Files)
- database/migrations/2026_01_31_154655_add_unique_active_year_constraint_to_academic_years.php
- database/migrations/2026_02_11_000001_fix_active_year_partial_index.php
- database/migrations/2026_01_31_155915_add_unique_active_term_per_year_constraint_to_terms.php
- phpunit.xml

Evidence
- database/migrations/2026_01_31_154655_add_unique_active_year_constraint_to_academic_years.php:12
- database/migrations/2026_01_31_155915_add_unique_active_term_per_year_constraint_to_terms.php:13
- database/migrations/2026_02_11_000001_fix_active_year_partial_index.php:14
- phpunit.xml:26
- docs/refactor-plan/DB-AUDIT_REPORT.md

Impact
- اختلاف دعم partial indexes بين SQLite (اختبارات) وقواعد الإنتاج قد يضعف invariants.

Tasks
- [ ] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [ ] توثيق سياسة توافق DB (SQLite في الاختبارات مقابل prod).
- [ ] إضافة اختبارات تتحقق من invariants الأساسية (active year/term uniqueness) على اتصال الاختبارات.
- [ ] إذا كان DB الاختبارات لا يدعم partial indexes، أضف guard بديل موثّق أو skip واضح مع سبب.
- [ ] منع إدخال شروط DB خاصة في migrations إلا مع توثيق قرار واضح.
- [ ] إضافة خطة استعادة CHECK constraints للأعمدة التي تُدار عبر Enums (بالرجوع إلى DB-AUDIT_REPORT).
- [ ] توثيق ومنع anti-patterns في migrations:
  - data operations داخل migration (التحويل إلى Commands/Seeders).
  - try/catch التي تخفي أخطاء schema.
  - كثرة Schema::hasColumn بدون مبرر.
  - استخدام change() في SQLite بدون توثيق تأثير إسقاط القيود.

Tests
- [ ] Feature/Integration tests لإثبات invariants في بيئة الاختبار.

Risks
- تعديل المهاجرات قد يؤثر على بيانات موجودة؛ يجب التحقق قبل أي تنفيذ.

Rollback
- إلغاء القيود الجديدة وإعادة الوضع السابق مع توثيق السبب.

Definition of Done
- سياسة DB موثّقة، والاختبارات تثبت invariants الأساسية.
