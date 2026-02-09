# 09 Guards, Audit, Queue Health

Goal
- Prevent unsafe writes and make grading queues observable.

Scope
- AcademicWriteGuard usage, amendment flow, queue monitoring.

Inputs (code references)
- app/Domains/Academic/Services/AcademicWriteGuard.php
- app/Domains/Academic/Grading/Actions/AmendStudentMarkAction.php
- app/Domains/Academic/Grading/Listeners/SyncMonthlyToStudentMark.php
- app/Domains/Academic/Grading/Listeners/SyncAttendanceToMonthlyGrade.php

Tasks
- [x] Enforce AcademicWriteGuard before every write to MonthlyGrade and StudentMark.
- [x] Ensure AmendStudentMarkAction updates amended_* fields and logs reason.
- [x] Add a grading queue health indicator (dashboard or warning banner).
- [x] Define a manual sync fallback if queue is down (policy decision).

Acceptance Criteria
- Any write attempt after term closure is blocked with a clear error.
- Amendment changes are auditable.
- Queue failures are visible to admins.

Verification
- Force a queue failure and confirm the UI displays a grading sync warning.
