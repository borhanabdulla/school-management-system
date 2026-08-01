# PR-03 — Write Gate Enforcement (Action-Only Writes)

Status: Completed
Priority: High
Depends On: PR--0, PR--1, PR-01

Scope (Exact Files)
- app/Livewire/Guardian/GuardianManager.php
- app/Livewire/HR/Leave/LeaveTypeManager.php
- app/Livewire/Payroll/SalaryComponentManager.php
- app/Domains/Academic/Student/Actions/DeleteGuardianAction.php
- app/Domains/HR/Leave/Actions/CreateLeaveTypeAction.php
- app/Domains/HR/Leave/Actions/UpdateLeaveTypeAction.php
- app/Domains/HR/Leave/Actions/DeleteLeaveTypeAction.php
- app/Domains/HR/Payroll/Actions/CreateSalaryComponentAction.php
- app/Domains/HR/Payroll/Actions/UpdateSalaryComponentAction.php
- app/Domains/HR/Payroll/Actions/DeleteSalaryComponentAction.php
- tests/Feature/Actions/Student/DeleteGuardianActionTest.php
- tests/Feature/HR/LeaveTypeActionsTest.php
- tests/Feature/Payroll/SalaryComponentActionsTest.php

Evidence
- app/Livewire/Guardian/GuardianManager.php:47
- app/Livewire/HR/Leave/LeaveTypeManager.php:57
- app/Livewire/HR/Leave/LeaveTypeManager.php:61
- app/Livewire/HR/Leave/LeaveTypeManager.php:70
- app/Livewire/Payroll/SalaryComponentManager.php:55
- app/Livewire/Payroll/SalaryComponentManager.php:60
- app/Livewire/Payroll/SalaryComponentManager.php:81

Revalidation (2026-02-18)
- Commands:
  - `rg -n -e "->update\\(" -e "::create\\(" -e "->delete\\(" app/Livewire/Guardian/GuardianManager.php app/Livewire/HR/Leave/LeaveTypeManager.php app/Livewire/Payroll/SalaryComponentManager.php`
  - `nl -ba app/Livewire/Guardian/GuardianManager.php | sed -n '35,120p'`
  - `nl -ba app/Livewire/HR/Leave/LeaveTypeManager.php | sed -n '40,140p'`
  - `nl -ba app/Livewire/Payroll/SalaryComponentManager.php | sed -n '30,140p'`

عقد تدفق الكتابة (Write Flow Contract)
- Livewire/UI validation ثم DTO/Data mapping.
- Action orchestrates + Guard/Service rules.
- Transaction + Persistence.
- Events/Jobs بعد نجاح العملية.

Impact
- كتابة مباشرة من UI بدون Actions/Guards تزيد مخاطر اختراق قواعد الدومين.

Tasks
- [x] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [x] إنشاء Actions لكل مسار كتابة في الملفات أعلاه (Create/Update/Delete) وربطها بـ Policies/Guards عند توفرها.
- [x] تعديل Livewire components لاستدعاء Actions بدلاً من الكتابة المباشرة.
- [x] استخدام AcademicWriteGuard عندما تكون الكتابة مرتبطة بسنة/ترم (غير منطبق على هذا النطاق).
- [x] توسيع القائمة باستخدام نتائج Gate 0.1 (Write Paths Report) كـ checklist (لا مسارات إضافية ضمن نطاق هذا PR).
- [x] إذا احتجنا DTOs جديدة لمسارات الكتابة، توثّق وتُنفّذ ضمن PR-09 (لا حاجة في هذا النطاق).

Tests
- [x] `php artisan test --filter=DeleteGuardianActionTest`
  - Result: 2 passed (2 assertions), 1.11s.
- [x] `php artisan test --filter=LeaveTypeActionsTest`
  - Result: 3 passed (6 assertions), 1.25s.
- [x] `php artisan test --filter=SalaryComponentActionsTest`
  - Result: 3 passed (3 assertions), 0.91s.
  - Warnings: PHPUnit doc-comment metadata deprecation notices (no failures).

Risks
- تغيير نقطة الكتابة قد يكشف اعتماداً خفياً على Side effects في UI.

Rollback
- إعادة الربط إلى الكتابة المباشرة مؤقتاً مع إبقاء Actions للاستخدام لاحقاً.

Definition of Done
- المسارات المذكورة لا تكتب مباشرة من UI، وكل كتابة تمر عبر Action مع Guard.
