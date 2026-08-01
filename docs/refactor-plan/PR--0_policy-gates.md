# PR--0 — Architecture Policy Gates

Status: In Review
Priority: Critical
Scope: قواعد تنفيذ فقط (لا تغييرات كود)

Enum Policy
- PHP Enum = المعنى الدوميني.
- DB enum/check = قيد حماية فقط.
- ممنوع status literals في application/UI/tests إلا مبرر موثق.
- تحويل مخالفات docs/enums-violations/* إلى checklist تنفيذية في PR-01.

Migration Policy (DB-vendor conditions)
- أي if/else حسب نوع قاعدة البيانات يحتاج:
  - سبب مكتوب.
  - اختبار تغطية.
  - fallback واضح.
- منع فروع DB غير موثقة داخل migrations.

Migration Hygiene Policy (anti-patterns)
- ممنوع data operations داخل migrations (نقلها إلى Command/Seeder موثق).
- ممنوع try/catch يخفي أخطاء schema (يسمح فقط عند توثيق السبب والبديل).
- استخدام Schema::hasColumn فقط عند وجود سبب موثق (ليس كقاعدة عامة).
- تجنّب change() في SQLite بدون توثيق تأثيره على القيود وإعادة تطبيقها عند الحاجة.
- المرجع: docs/refactor-plan/DB-AUDIT_REPORT.md

Model Boundary Policy
- الموديل: relations/casts/scopes/invariants البسيطة فقط.
- business orchestration في Action/Domain Service.
- shared technical logic في Infrastructure Services/Traits.

Definition of Done
- السياسات موثقة ومقبولة كـ Gate رسمي قبل أي PR كودي.
- تحويل مخالفات الـ Enums إلى checklist تنفيذية.
- إدراج الروابط في ملفات الـ PRs ذات الصلة.
