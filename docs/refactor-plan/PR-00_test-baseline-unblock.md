# PR-00 — Test Baseline Unblock (Factories + Logging)

Status: In Review (baseline achieved)
Priority: Critical
Depends On: PR--1, PR--0
Scope: اختبار البنية فقط (لا تغيير في منطق الدومين)

Scope (Exact Files)
- phpunit.xml
- app/Domains/Academic/Grade/Services/GradeLookupService.php
- app/Infrastructure/Security/SensitiveAccess.php
- app/Livewire/Academic/AcademicYearManager.php
- app/Domains/Academic/Control/Services/ResultProcessingService.php
- tests/Feature/AcademicManagementTest.php
- database/factories/UserFactory.php
- database/factories/AcademicYearFactory.php
- database/factories/TermFactory.php
- database/factories/StaffFactory.php
- database/factories/TeacherFactory.php
- database/factories/EducationalStageFactory.php
- database/factories/GradeFactory.php
- database/factories/SubjectFactory.php
- database/factories/ClassSectionFactory.php
- database/factories/ContractFactory.php
- database/factories/SubstitutionFactory.php
- database/factories/PayrollBatchFactory.php
- database/factories/StaffAttendanceFactory.php
- database/factories/LeaveTypeFactory.php
- database/factories/StaffLeaveBalanceFactory.php
- database/factories/LeaveRequestFactory.php
- database/factories/Domains/Academic/Student/Models/StudentFactory.php
- database/factories/Domains/Academic/Student/Models/GuardianFactory.php
- database/factories/Domains/Academic/Student/Models/StudentEnrollmentFactory.php
- database/factories/Domains/Academic/Student/Models/StudentMarkFactory.php
- database/factories/Domains/Academic/Attendance/Models/AttendanceFactory.php
- database/factories/Domains/Academic/Grading/Models/GradebookMonthFactory.php
- database/factories/Domains/Academic/Grading/Models/AssessmentFactory.php
- database/factories/Domains/Academic/Grading/Models/GradingTemplateFactory.php
- database/factories/Domains/Academic/Grading/Models/TemplateCategoryFactory.php
- database/factories/Domains/Academic/Grading/Models/MonthlyGradeFactory.php
- database/factories/Domains/Academic/Results/Models/AnnualResultFactory.php
- database/factories/Domains/Academic/CourseOffering/Models/CourseOfferingFactory.php
- database/factories/Domains/Academic/Timetable/Models/TimetableTemplateFactory.php
- database/factories/Domains/Academic/Timetable/Models/TimeSlotFactory.php
- database/factories/Domains/Academic/Timetable/Models/TimetableFactory.php
- database/factories/Domains/Academic/Control/Models/ExamSessionFactory.php
- database/factories/Domains/Academic/Control/Models/ExamSeatingFactory.php
- database/factories/Domains/Academic/Homework/Models/HomeworkFactory.php
- database/factories/Domains/Academic/Homework/Models/HomeworkSubmissionFactory.php
- database/factories/Domains/Finance/Models/InvoiceFactory.php
- database/factories/Domains/Finance/Models/PaymentFactory.php
- database/seeders/EducationalStructureSeeder.php

Evidence
- app/Domains/Shared/Models/User.php:19
- app/Domains/Academic/AcademicYear/Models/AcademicYear.php:28
- app/Domains/Academic/Term/Models/Term.php:31
- app/Domains/Academic/Stage/Models/EducationalStage.php:13
- app/Domains/Academic/Grade/Models/Grade.php:20
- app/Domains/Academic/Grade/Services/GradeLookupService.php:85
- app/Infrastructure/Security/SensitiveAccess.php:31
- app/Livewire/Academic/AcademicYearManager.php:452
- app/Domains/Academic/Control/Services/ResultProcessingService.php:360
- tests/Feature/AcademicManagementTest.php:1
- app/Domains/Academic/Subject/Models/Subject.php:12
- app/Domains/Academic/ClassSection/Models/ClassSection.php:23
- app/Domains/Academic/Student/Models/Student.php:30
- app/Domains/Academic/Student/Models/Guardian.php:15
- app/Domains/Academic/Student/Models/StudentEnrollment.php:13
- app/Domains/Academic/Student/Models/StudentMark.php:17
- app/Domains/Academic/Attendance/Models/Attendance.php:15
- app/Domains/Academic/Grading/Models/GradebookMonth.php:18
- app/Domains/Academic/Grading/Models/Assessment.php:23
- app/Domains/Academic/Grading/Models/GradingTemplate.php:13
- app/Domains/Academic/Grading/Models/TemplateCategory.php:13
- app/Domains/Academic/Grading/Models/MonthlyGrade.php:17
- app/Domains/Academic/Results/Models/AnnualResult.php:17
- app/Domains/Academic/CourseOffering/Models/CourseOffering.php:28
- app/Domains/Academic/Timetable/Models/TimetableTemplate.php:17
- app/Domains/Academic/Timetable/Models/TimeSlot.php:18
- app/Domains/Academic/Timetable/Models/Timetable.php:19
- app/Domains/Academic/Control/Models/ExamSession.php:16
- app/Domains/Academic/Control/Models/ExamSeating.php:14
- app/Domains/Academic/Homework/Models/Homework.php:17
- app/Domains/Academic/Homework/Models/HomeworkSubmission.php:16
- app/Domains/HR/Staff/Models/Staff.php:19
- app/Domains/HR/Staff/Models/StaffAttendance.php:12
- app/Domains/HR/Teacher/Models/Teacher.php:26
- app/Domains/HR/Payroll/Models/Contract.php:21
- app/Domains/HR/Payroll/Models/PayrollBatch.php:21
- app/Domains/HR/Leave/Models/LeaveType.php:14
- app/Domains/HR/Leave/Models/LeaveRequest.php:15
- app/Domains/HR/Leave/Models/StaffLeaveBalance.php:13
- app/Domains/HR/Substitution/Models/Substitution.php:15
- app/Domains/Finance/Models/Invoice.php:14
- app/Domains/Finance/Models/Payment.php:15
- tests/Feature/TermServiceTest.php:72
- tests/Feature/HR/SubstitutionAssignmentTest.php:25
- tests/Feature/Livewire/Finance/Invoices/CancelPaymentModalTest.php:28
- .env:18

Impact
- اختبارات تفشل لعدم وجود Factories أو states مطلوبة.
- الكتابة إلى storage/logs أثناء الاختبار تفشل، مما يمنع Baseline حقيقي.

Tasks
- [ ] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [x] إضافة Factories أساسية لكل الموديلات التي تعتمد عليها الاختبارات.
- [x] إضافة states لازمة في factories (مثل active() للسنة/الترم).
- [x] ضبط قيم الـ Enum الافتراضية صراحة في factories حسب المطلوب للاختبارات.
- [x] إضافة Seeder بسيط للهيكل التعليمي المستخدم في الاختبارات.
- [x] ضبط بيئة الاختبار لتسجيل logs إلى stderr أو null عبر phpunit.xml.
- [x] تشغيل الاختبارات وتسجيل Baseline الجديد.

Tests
- [x] `php artisan test` — 433 passed (1099 assertions), 0 failed; warnings about PHPUnit metadata in doc-comments.

Risks
- تعريف Factory ناقص قد يكسر اختبارات تعتمد على علاقات إلزامية.

Rollback
- إزالة ملفات factories الجديدة.
- إزالة Seeder الهيكل التعليمي.
- إزالة override الخاص بـ LOG_CHANNEL من phpunit.xml.

Definition of Done
- اختبارات تعمل بدون "Factory not found" وبدون خطأ كتابة log.
- لا توجد أخطاء BadMethodCallException بسبب states مفقودة في factories.
Notes
- phpunit.xml يحدد `storage/framework/testing` كمسار temp للاختبارات.
- تحذيرات PHPUnit metadata ما زالت تظهر لكنها غير مانعة للنجاح.
