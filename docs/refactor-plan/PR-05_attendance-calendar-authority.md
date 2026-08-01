# PR-05 — Attendance + Calendar Authority

Status: Completed
Priority: Medium
Depends On: PR--0, PR--1, PR-04

Scope (Exact Files)
- app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php
- app/Livewire/Teacher/AttendanceTaker.php
- app/Domains/Academic/Calendar/Services/SchoolCalendarService.php
- app/Domains/Academic/Attendance/Services/AttendanceReportService.php

Evidence
- app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php:38
- app/Livewire/Teacher/AttendanceTaker.php:72
- app/Domains/Academic/Calendar/Services/SchoolCalendarService.php:175
- app/Domains/Academic/Attendance/Services/AttendanceReportService.php:43
- app/Domains/Academic/Attendance/Services/AttendanceReportService.php:121

Revalidation (2026-02-22)
- Commands:
  - `rg -n "isHolidayForYear|holiday_override|attendance.manage" app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php app/Livewire/Teacher/AttendanceTaker.php`
  - `nl -ba app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php | sed -n '30,90p'`
  - `nl -ba app/Livewire/Teacher/AttendanceTaker.php | sed -n '60,100p'`
  - `nl -ba app/Domains/Academic/Calendar/Services/SchoolCalendarService.php | sed -n '160,210p'`
  - `nl -ba app/Domains/Academic/Attendance/Services/AttendanceReportService.php | sed -n '35,150p'`

Impact
- تسجيل حضور في أيام عطلة/ويكند بدون ضوابط قد يفسد التقارير والمحاسبة.

Tasks
- [x] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [x] إجراء Audit لمسارات كتابة الحضور للتحقق من وجود منع للعطل/الويكند (SchoolCalendarService).
- [x] إضافة Guard واضح: يمنع التسجيل في holidays/weekends إلا بصلاحية صريحة.
- [x] توحيد مصدر العطل/الويكند عبر SchoolCalendarService في جميع المسارات.

Tests
- [x] Feature test: يمنع الحضور في holiday بدون صلاحية.
- [x] Feature test: يسمح بالاستثناء مع صلاحية وسبب موثق.
- [x] `php artisan test --filter='AttendanceTermAwarenessTest|AttendanceTest|StudentLookupServiceTest|DashboardQueryGuardTest'`
  - Result: 26 passed (89 assertions), 3.72s.
  - Warnings: PHPUnit doc-comment metadata deprecation notices (no failures).

Risks
- قد تتطلب قواعد استثناءات خاصة بالأقسام أو الأنشطة.

Rollback
- تعطيل guard مؤقتاً مع warning حتى يتم ضبط الصلاحيات.

Definition of Done
- كل مسار كتابة للحضور يتحقق من calendar authority بوضوح.
