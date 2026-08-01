# PR-10 — Model Slimming + Infrastructure Adoption

Status: Planned
Priority: Medium
Depends On: PR--0, PR--1, PR-09

Scope (Exact Files)
- app/Domains/Academic/AcademicYear/Models/AcademicYear.php
- app/Domains/Academic/Term/Models/Term.php

Evidence
- app/Domains/Academic/AcademicYear/Models/AcademicYear.php:21
- app/Domains/Academic/Term/Models/Term.php:25

Impact
- الحفاظ على الموديلات خفيفة يمنع تكرار المنطق ويعزز وضوح الحدود بين الطبقات.

Tasks
- [ ] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [ ] Audit: حصر الموديلات التي تحتوي منطق أعمال يتجاوز العلاقات/casts/scopes البسيطة.
- [ ] نقل المنطق غير البسيط إلى Actions/Domain Services أو Traits مشتركة في Infrastructure عند التكرار.
- [ ] الإبقاء على الموديل: علاقات + casts + scopes + helpers بسيطة فقط.

Tests
- [ ] تحديث/إضافة اختبارات للوحدات المنقولة عند الحاجة.

Risks
- نقل المنطق قد يكشف اعتماداً ضمنياً في UI أو Services أخرى.

Rollback
- إعادة المنطق إلى الموديل مع توثيق السبب.

Definition of Done
- الموديلات المستهدفة خفيفة، والمنطق المعقد معزول في الطبقات المناسبة.
