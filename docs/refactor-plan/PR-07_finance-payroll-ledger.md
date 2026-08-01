# PR-07 — Finance/Payroll Ledger + Non-Overlap

Status: Planned
Priority: Medium
Depends On: PR--0, PR--1, PR-01

Scope (Exact Files)
- docs/finance/PR0-implementation-plan.md
- docs/enums-violations/finance.md
- app/Domains/Academic/Promotion/Services/PromotionService.php
- database/migrations/2026_02_02_000001_alter_invoices_status_enum.php
- database/migrations/2026_02_02_000002_create_payments_table.php
- database/migrations/2025_12_21_200002_create_payroll_batches_table.php

Evidence
- docs/finance/PR0-implementation-plan.md
- docs/enums-violations/finance.md
- app/Domains/Academic/Promotion/Services/PromotionService.php:339
- database/migrations/2025_12_21_200002_create_payroll_batches_table.php:47
- database/migrations/2026_02_02_000002_create_payments_table.php:15

Impact
- عدم توحيد حالة الفواتير أو تداخل الفترات المالية يهدد صحة الدفاتر.

Tasks
- [ ] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [ ] مراجعة اتساق InvoiceStatus مع قيم DB (تنفيذ ما في PR0 إن لم يكن مطبقاً).
- [ ] نقل أي منطق مالي مشترك إلى خدمة Finance واضحة (مثال: FinancialClearanceService).
- [ ] تدقيق إنشاء payroll_batches لمنع تداخل الفترات (إن كان يسمح بإدخال يدوي).
- [ ] تحديث أي استخدام نصي لحالات المدفوعات في الاختبارات إلى Enum.

Tests
- [ ] Feature tests لمسار الدفع الجزئي/الكامل وتحديث حالة الفاتورة.
- [ ] Test يمنع إنشاء دفعة رواتب بفترة متداخلة (إذا كانت السياسة تتطلب ذلك).

Risks
- تغيير قواعد الفوترة قد يتطلب data migration أو تنظيف بيانات قديمة.

Rollback
- إعادة العمل بالمنطق السابق مع تسجيل تحذيرات فقط.

Definition of Done
- الدفاتر المالية تعتمد على source-of-truth واحد والاختبارات تغطي الحالات الحرجة.
