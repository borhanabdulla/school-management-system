# PR-04 — Term/Year Leakage Fix

Status: Completed
Priority: High
Depends On: PR--0, PR--1, PR-02

Scope (Exact Files)
- app/Domains/Academic/Attendance/Services/AttendanceLookupService.php
- app/Domains/Academic/Attendance/Services/AttendanceReportService.php
- app/Domains/Academic/Student/Services/StudentLookupService.php
- app/Domains/Shared/Services/Dashboard/Concerns/HasDashboardQueries.php

Evidence
- app/Domains/Academic/Attendance/Services/AttendanceLookupService.php:18
- app/Domains/Academic/Attendance/Services/AttendanceLookupService.php:34
- app/Domains/Academic/Attendance/Services/AttendanceReportService.php:29
- app/Domains/Academic/Attendance/Services/AttendanceReportService.php:54
- app/Domains/Academic/Student/Services/StudentLookupService.php:489
- app/Domains/Shared/Services/Dashboard/Concerns/HasDashboardQueries.php:12
- app/Domains/Shared/Services/Dashboard/Concerns/HasDashboardQueries.php:27
- app/Domains/Shared/Services/Dashboard/Concerns/HasDashboardQueries.php:42

Revalidation (2026-02-21)
- Commands:
  - `rg -n "academic_year_id|academicYear|term_id|termId|year_id|activeYear|activeTerm|enrollment" app/Domains/Academic/Attendance/Services/AttendanceLookupService.php app/Domains/Academic/Attendance/Services/AttendanceReportService.php app/Domains/Academic/Student/Services/StudentLookupService.php app/Domains/Shared/Services/Dashboard/Concerns/HasDashboardQueries.php`
  - `nl -ba app/Domains/Academic/Attendance/Services/AttendanceLookupService.php | sed -n '1,140p'`
  - `nl -ba app/Domains/Academic/Attendance/Services/AttendanceReportService.php | sed -n '1,200p'`
  - `nl -ba app/Domains/Academic/Student/Services/StudentLookupService.php | sed -n '430,560p'`
  - `nl -ba app/Domains/Shared/Services/Dashboard/Concerns/HasDashboardQueries.php | sed -n '1,160p'`

Impact
- تقارير أو لوحات تعرض بيانات من سنة/ترم غير المقصودة (تسريب تاريخي).

Tasks
- [x] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [x] تحديث AttendanceLookupService لتلقي academic_year_id/term_id صراحة أو اشتقاقها من timetable بشكل واضح.
- [x] جعل term_id في AttendanceReportService إلزامياً لمسارات الحساسية أو تمريره بوضوح من UI.
- [x] استبدال StudentLookupService::getByClassSection باستعلام يعتمد enrollments للسنة المطلوبة.
- [x] تحديث Dashboard queries لتطلب academicYearId/termId بشكل صريح أو تعيد خطأ إذا غابتا.

Tests
- [x] Feature tests لتمييز بيانات ترمين مختلفين في نفس الصف.
- [x] Test يمنع عرض بيانات سنة قديمة عند عدم تمرير year/term.
- [x] `php artisan test --filter='AttendanceTermAwarenessTest|AttendanceTest|StudentLookupServiceTest|DashboardQueryGuardTest'`
  - Result: 24 passed (87 assertions), 1.91s.
  - Warnings: PHPUnit doc-comment metadata deprecation notices (no failures).

Risks
- تغيّر توقيع الخدمات قد يتطلب تعديل واجهات UI متعددة.

Rollback
- إضافة default مؤقت مع تحذير logging إذا تعذر التمرير الصريح.

Definition of Done
- لا توجد استعلامات حساسة بدون year/term في المسارات المحددة.
