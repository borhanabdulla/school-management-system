# Evidence Revalidation (قبل كل PR)

Goal
- منع الاعتماد على أدلة قديمة أو خطوط تغيّرت.

Checklist
- [ ] إعادة استخراج الأدلة المتعلقة بالـ PR.
- [ ] تحديث ملفات الأدلة المتأثرة:
  - docs/academic-context-audit.md
  - docs/academic-year/PR-A4_write-path-audit.md
  - docs/enums-violations/*
  - docs/PR2-Gate1-Audit-Report.md (عند تعلق الـ PR بالتيمتابل)
- [ ] تحديث قسم Evidence داخل ملف الـ PR بالمسارات والأسطر الجديدة.
- [ ] توثيق أي تغيّر في النطاق أو المخاطر داخل نفس ملف الـ PR.

Standard Commands (أمثلة)
- rg -n "activeYear|activeTerm|status" app
- rg -n "create|update|delete|save|upsert" app
- rg -n "enum" app

Definition of Done
- Evidence داخل ملف الـ PR محدث.
- الأدلة الداعمة في docs محدثة.
- لا يوجد ادعاء بدون مسار/سطر واضح.

Log
- 2026-02-18: PR-02 revalidation completed (AcademicContextService enum status + SSOT docs).
- 2026-02-18: PR-03 revalidation completed (Guardian/LeaveType/SalaryComponent write gate).
- 2026-02-21: PR-04 revalidation completed (AttendanceLookup/Report, StudentLookup, Dashboard queries).
- 2026-02-22: PR-05 revalidation completed (Attendance calendar authority).
- 2026-02-22: PR-06 revalidation completed (Grading historical integrity MCP gate).
