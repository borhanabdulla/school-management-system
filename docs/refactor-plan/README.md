# خطة إصلاح مرحلية (Evidence-Driven)

هدف هذا المجلد: تحويل مخرجات Gate A وGate 0 إلى PRs صغيرة قابلة للمراجعة، وفق قواعد "بدون افتراضات".

مبادئ ثابتة
- لا تعديل بدون دليل (كود/مهاجرات/اختبار).
- كل PR له هدف واحد واختبارات تثبت Before/After.
- الكتابة في الدومين تمر عبر Actions/Guards وليس من UI.

عقد تدفق البيانات القياسي (قابل للتكييف حسب بنية المشروع)
- Livewire/UI: input state + validation.
- Mapping واضح إلى DTO/Data.
- Action: orchestration فقط.
- Domain Guard/Service: القواعد (status/year/term/closure).
- Transaction + Model/Persistence.
- Events/Jobs بعد نجاح العملية.
- Read models/UI refresh بوضوح مع year/term.
- أي خروج عن التدفق يوثّق في نفس PR.

سياسة الـ Enums
- المعنى الدوميني في PHP Enums داخل الدومين.
- قيود DB (enum/check) هي Guard فقط.
- ممنوع status literals في منطق التطبيق أو Blade؛ الاستثناءات توثّق.

حالة البوابات
- Gate -1: Plan Normalization (PR--1_plan-normalization).
- Gate -0: Architecture Policy Gates (PR--0_policy-gates).
- Gate A: مكتمل (Repo Map + Correctness Rules + Anti-Patterns).
- Gate 0: مكتمل (Write Paths + Leakage + DB Invariants + Test Baseline).
- Evidence Revalidation: إلزامي قبل كل PR.

مراجع الأدلة الأساسية (داخل الريبو)
- docs/academic-context-audit.md
- docs/academic-year/PR-A0_verified_findings.md
- docs/academic-year/PR-A4_write-path-audit.md
- docs/enums-violations/README.md
- docs/finance/PR0-implementation-plan.md
- docs/PR2-Gate1-Audit-Report.md
- docs/decisions/grading-monthly-category-and-attendance-term.md
- docs/refactor-plan/DB-AUDIT_REPORT.md

خارطة المراحل (Phases)
- Phase -1 (Plan): PR--1.
- Phase -0 (Policies): PR--0.
- Phase B (Stabilization): PR-00, PR-01, PR-01b, PR-02, PR-03.
- Phase C (Business Integrity): PR-04 .. PR-08.
- Phase D (Structural Cleanup): PR-09 .. PR-10.
- Phase E (DB & Deprecation Governance): PR-11 .. PR-12.

خارطة PRs (بالترتيب)
1) PR--1_plan-normalization
2) PR--0_policy-gates
3) PR-00_test-baseline-unblock
4) PR-01_status-semantics-hardening
5) PR-01b_db-enum-check-alignment
6) PR-02_active-year-term-contract
7) PR-03_write-gate-enforcement
8) PR-04_term-year-leakage
9) PR-05_attendance-calendar-authority
10) PR-06_grading-historical-integrity
11) PR-07_finance-payroll-ledger
12) PR-08_timetable-template-safety
13) PR-09_data-contracts-dto
14) PR-10_model-slimming-infra
15) PR-11_migration-governance
16) PR-12_deprecation-dead-code

Stop Conditions
- لا بدء PR-n قبل اكتمال تعريفه واختباراته في ملفه.
- Evidence Revalidation قبل كل PR (تحديث الأدلة + تحديث ملف الـ PR).
- أي قرار يحتاج MCP Gate يجب أن يُقفل قبل التنفيذ.

KPIs نجاح الخطة
- انخفاض status literals في الكود الإنتاجي والاختبارات ضمن النطاق المستهدف.
- كل write path في النطاق المستهدف يمر عبر Action.
- تسريب year/term = صفر في المسارات الحساسة.
- كل PR يحتوي Before/After + tests + rollback notes.

Definition of Done (عام)
- لا يوجد string literals للحالات ضمن النطاق المستهدف.
- كل write path في النطاق يمر عبر Action + Guard.
- اختبارات Before/After موجودة وتثبت السلوك.
- Rollback واضح لكل PR.
