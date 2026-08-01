# PR-01 — Status Semantics Hardening (Remove String Literals)

Status: Planned
Priority: High
Depends On: PR--0, PR--1, PR-00

Scope (Exact Files)
- app/Domains/Academic/CourseOffering/Actions/AssignTeacherAction.php
- app/Livewire/Payroll/PayrollProcessor.php
- app/Livewire/HR/HRDashboard.php
- app/Console/Commands/GradingConfigCheck.php
- app/Domains/HR/Payroll/Actions/ApprovePayrollAction.php
- resources/views/livewire/payroll/loan-manager.blade.php
- resources/views/livewire/payroll/staff-financial-profile.blade.php
- resources/views/livewire/payroll/payroll-batch-manager.blade.php
- resources/views/livewire/student/student-directory.blade.php
- resources/views/components/student/badge.blade.php
- app/Domains/Academic/Promotion/Services/PromotionService.php
- docs/enums-violations/finance.md
- docs/enums-violations/hr.md

Evidence
- app/Domains/Academic/CourseOffering/Actions/AssignTeacherAction.php:40
- app/Domains/HR/Staff/Enums/StaffStatus.php:8
- app/Livewire/Payroll/PayrollProcessor.php:116
- app/Livewire/HR/HRDashboard.php:109
- app/Domains/HR/Leave/Models/LeaveRequest.php:62
- app/Console/Commands/GradingConfigCheck.php:22
- app/Domains/Academic/Term/Enums/TermStatus.php:7
- app/Domains/Academic/Term/Models/Term.php:118
- app/Domains/HR/Payroll/Actions/ApprovePayrollAction.php:48
- app/Domains/HR/Payroll/Actions/ApprovePayrollAction.php:57
- app/Domains/HR/Payroll/Enums/LoanStatus.php:8
- resources/views/livewire/payroll/loan-manager.blade.php:54
- resources/views/livewire/payroll/staff-financial-profile.blade.php:217
- app/Domains/Academic/Promotion/Services/PromotionService.php:339
- docs/enums-violations/hr.md
- docs/enums-violations/finance.md

Policy (ضمن نطاق هذا PR)
- المعنى الدوميني في PHP Enums داخل الدومين.
- قيود DB (enum/check) هي Guard فقط.
- ممنوع string literals في منطق التطبيق أو Blade؛ الاستثناءات توثّق.

Impact
- عدم اتساق الدلالات يسبب انحراف في المنطق بين الطبقات ويصعّب صيانة القيم.

Fix Options
- استخدام Enums أو Scopes بدلاً من strings.
- نقل الاستعلامات من Blade إلى Livewire/Service عند الحاجة.

Tasks
- [ ] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [ ] Replace Staff status literal في AssignTeacherAction بـ StaffStatus::Active.
- [ ] Replace LeaveRequest status literals في PayrollProcessor وHRDashboard بـ LeaveRequestStatus أو scopePending().
- [ ] Replace Term status literal في GradingConfigCheck بـ Term::active() أو TermStatus::Active->value.
- [ ] Replace Loan status literals في ApprovePayrollAction وloan-manager UI بـ LoanStatus::Paid->value.
- [ ] Replace payroll batch status literals في staff-financial-profile UI بـ PayrollBatchStatus::Paid (أو helper واضح).
- [ ] Remove 'paid' fallback في PromotionService واستعمال InvoiceStatus فقط (بعد التحقق من قيم DB).
- [ ] تحديث أي Blade mapping للاعتماد على Enum->label/color بدل نص.
- [ ] Replace payroll batch status literals في payroll-batch-manager UI بـ PayrollBatchStatus Enum.
- [ ] Replace finance UI paid/unpaid display في student-directory وstudent badge عبر InvoiceStatus أو mapper.

Tests
- [ ] Feature tests لمسارات: AssignTeacherAction, ApprovePayrollAction, PromotionService financial clearance.
- [ ] UI/Livewire tests إذا كانت موجودة.

Risks
- إذا كانت بيانات DB تحتوي قيماً قديمة غير مطابقة لـ Enum، يجب تنظيفها أولاً.

Rollback
- إرجاع المقارنات النصية إن لزم، مع تسجيل السبب.

Definition of Done
- لا توجد comparisons نصية للحالات في الملفات المستهدفة.
