# PR-08 — Timetable Template Update Safety

Status: Planned
Priority: Medium
Depends On: PR--0, PR--1, PR-03

Scope (Exact Files)
- app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php
- app/Livewire/Timetable/TimetableTemplateManager.php
- docs/PR2-Gate1-Audit-Report.md

Evidence
- app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php:62
- app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php:102
- docs/PR2-Gate1-Audit-Report.md

Impact
- حذف time slots وإعادة إنشائها قد يؤدي لفقدان روابط أو منع تحديثات مطلوبة.

Tasks
- [ ] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [ ] مراجعة التوافق بين docs/PR2-Gate1-Audit-Report.md والكود الحالي (تحديث التقرير إذا تغيّر السلوك).
- [ ] تقييم الحاجة لاستبدال delete+create بتحديث تفاضلي يحافظ على IDs.
- [ ] ضمان أن أي تحديث لا يسمح بحذف slots مستخدمة، مع رسالة واضحة للمستخدم.

Tests
- [ ] Feature test: تحديث قالب مع وجود timetables يجب أن يفشل برسالة محددة.
- [ ] Feature test: تحديث قالب بدون استخدام فعلي يجب أن ينجح.

Risks
- تحديث تفاضلي قد يزيد التعقيد ويحتاج تغطية اختبارات إضافية.

Rollback
- الإبقاء على guard الحالي مع منع أي تعديل للقوالب المستخدمة.

Definition of Done
- تحديث القالب آمن وموثّق باختبارات واضحة.
