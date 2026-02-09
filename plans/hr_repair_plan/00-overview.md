# HR Repair Plan (Index)

Purpose
- Build a step-by-step repair plan for the HR domain based on the prior deep review.
- Focus on data correctness, domain boundaries (DDD), and preventing regressions.
- Livewire only (no Filament assumptions).

Guiding invariants
- DB is the source of truth; models must match DB exactly (no silent mismatches).
- Use-case = Action. UI must not write to models directly.
- Locks: once payroll period is Frozen/Approved/Paid, no direct edits to source data.
- After lock: changes go through Amendments and produce adjustments.
- Cache keys must be scoped (if multi-school/tenant) and invalidated reliably.

Execution order (phases)
0) Stabilize (must-fix runtime/data breakages)
1) Staff domain (root entity)
2) Teacher + Substitution (orphan risk)
3) Attendance (HR presence data)
4) WorkShift (policy reference)
5) Leave (request/balance + attendance coupling)
6) Payroll (calculation engine + batch workflow)
7) Period locks + Amendments + reproducibility
8) Livewire cleanup (remove write bypasses)
9) Cache/Observers alignment
10) Tests (guardrails)

Folder map
- 00-stabilization.md
- 01-staff.md
- 02-teacher-substitution.md
- 03-attendance.md
- 04-workshift.md
- 05-leave.md
- 06-payroll.md
- 07-locks-amendments.md
- 08-ui-livewire.md
- 09-cache-observers.md
- 10-tests.md

Notes
- This plan is implementation-ready; each file lists the exact files to touch.
- If you want the plan in Arabic, say the word and I will translate.
