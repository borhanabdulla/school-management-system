# PR-09 — Data Contracts (DTO Standardization)

Status: Planned
Priority: Medium
Depends On: PR--0, PR--1, PR-03

Scope (Exact Files)
- app/Domains/Academic/Timetable/Data/TimetableTemplateData.php
- app/Domains/HR/Payroll/Data/PayrollGenerationData.php
- app/Domains/Academic/CourseOffering/Actions/AssignTeacherAction.php

Evidence
- app/Domains/Academic/Timetable/Data/TimetableTemplateData.php:15
- app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php:41
- app/Domains/HR/Payroll/Data/PayrollGenerationData.php:10
- app/Domains/Academic/CourseOffering/Actions/AssignTeacherAction.php:15

Impact
- تباين العقود بين DTO وأكشنات تقبل primitives يسبب تكرار التحقق ويزيد مخاطر الانحراف بين الطبقات.

Tasks
- [ ] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [ ] Audit: حصر Actions الكتابية التي تستقبل arrays/primitives بدون DTO واضح.
- [ ] تثبيت معيار: أي Action كتابة جديدة/معدلة يجب أن تستقبل DTO/Data.
- [ ] تحويل مجموعة صغيرة عالية الأثر إلى DTOs بناءً على نتائج الـ audit (بدون تغيير سلوكي مقصود).
- [ ] تحديث Livewire Forms للترجمة إلى DTOs.

Tests
- [ ] Feature tests لمسارات التحويل (DTO -> Action) في النطاق المحدد.

Risks
- توسيع النطاق بلا دليل؛ لذلك التحويل يتم بعد الـ audit فقط.

Rollback
- إعادة توقيع الأكشنات إلى primitives مع توثيق السبب.

Definition of Done
- الأكشنات في النطاق تعتمد DTOs واضحة وموثقة.
