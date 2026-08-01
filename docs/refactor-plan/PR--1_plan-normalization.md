# PR--1 — Plan Normalization Gate

Status: Planned
Priority: Critical
Scope: خطة فقط (لا تغييرات كود)

Purpose
- إزالة التكرار وتثبيت dependencies وحدود الـ scope لكل PR.

Tasks
- [ ] مراجعة README لخارطة PRs وإزالة أي تكرار.
- [ ] توحيد dependencies بين ملفات الـ PRs.
- [ ] تعريف حدود الـ scope لكل PR بقائمة ملفات أولية (Exact Files).
- [ ] تثبيت PR-01 كمرجع رئيسي للـ status literals.
- [ ] إدراج Evidence Revalidation كخطوة ثابتة قبل كل PR.
- [ ] اعتماد Gate -0 للسياسات المعمارية.

Deliverables
- README محدّث.
- ملفات PRs محدّثة بالـ scope boundaries.
- سجل تغييرات مختصر.

Definition of Done
- لا يوجد PR مكرر في الخطة.
- كل PR لديه dependency واضح وScope (Exact Files).
- Evidence Revalidation مذكور في كل PR.
