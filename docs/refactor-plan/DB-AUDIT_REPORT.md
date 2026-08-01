# تقرير فحص قاعدة البيانات (مصحح + Evidence)

Snapshot (من واقع الريبو/الـ DB الحالي)
- Laravel: 12.49.0
- DB: SQLite
- عدد المهاجرات: 135
- عدد الجداول (غير جداول sqlite_): 105
- عدد PHP Enums: 36
- عدد الجداول التي تحتوي CHECK فعلي في DDL: 30

Snapshot Provenance
- المصدر: `database/database.sqlite` داخل الريبو (غير فارغ).
- الحجم الحالي: 1,675,264 bytes.
- الأرقام أعلاه مستخرجة من sqlite_master في نفس الملف.

المصادر المستخدمة (Evidence)
- database/migrations/* (تعريفات الأعمدة والقيود الأصلية).
- app/**/Enums/* + app/**/Models/* (قيم الـ Enums و casts).
- database/database.sqlite (sqlite_master DDL الحالي).

---

## 1) Enum ↔ DB CHECK mismatches (Verified, High Risk)
هذه الحالات تعني أحد أمرين:
- **Crash عند الكتابة** إذا الـ Enum يسمح بقيم غير موجودة في CHECK.
- **ValueError عند القراءة** إذا الـ DB يحتوي قيمة ليست ضمن Enum (cast).

1) GuardianRelationship ↔ student_guardian.relationship
- Enum يحتوي قيم إضافية: grandfather, grandmother, aunt, sister.
- CHECK في DB يقبل فقط: father, mother, brother, uncle, other.
- Evidence:
  - Enum: `app/Domains/Academic/Student/Enums/GuardianRelationship.php:5`
  - Migration: `database/migrations/2025_11_19_186000_create_student_guardian_table.php:18`

2) EnrollmentStatus ↔ student_enrollments.status
- Enum يحتوي returning + new (قيم ليست ضمن CHECK الخاص بـ status).
- CHECK في DB يقبل فقط: active, completed, failed, withdrawn.
- Evidence:
  - Enum: `app/Domains/Academic/Student/Enums/EnrollmentStatus.php:5`
  - Migration: `database/migrations/2025_11_19_188200_create_student_enrollments_table.php:25`

3) ResultDecision ↔ annual_results.decision
- Enum يحتوي absent.
- CHECK في DB لا يتضمن absent.
- Evidence:
  - Enum: `app/Domains/Academic/Results/Enums/ResultDecision.php:5`
  - Migration: `database/migrations/2025_12_29_134541_create_annual_results_table.php:33`

4) FinalResultStatus ↔ final_results.status
- CHECK في DB يتضمن incomplete + pending.
- Enum لا يحتوي incomplete/pending → قراءة هذه القيم تسبب ValueError.
- Evidence:
  - Enum: `app/Domains/Academic/Results/Enums/FinalResultStatus.php:5`
  - Migration: `database/migrations/2025_12_28_233700_create_control_system_tables.php:93`

---

## 2) Enums مستخدمة في casts بدون CHECK في DDL الحالي (Integrity Risk)
هذه الأعمدة تُدار بالـ Enum في الكود، لكن الـ DB لا يفرض CHECK حاليًا.
الأثر: أي قيمة نصية يمكن إدخالها بدون حماية DB → انحراف منطقي أو ValueError عند القراءة.

أمثلة موثقة (قابلة للتوسع بعد revalidation):
- AcademicYear.status (AcademicYearStatus)
  - Cast: `app/Domains/Academic/AcademicYear/Models/AcademicYear.php:52`
- Term.status (TermStatus)
  - Cast: `app/Domains/Academic/Term/Models/Term.php:55`
- Student.status + Student.gender (StudentStatus + Gender)
  - Cast: `app/Domains/Academic/Student/Models/Student.php:86`
  - Enum: `app/Domains/Academic/Student/Enums/StudentStatus.php:5`, `app/Domains/Shared/Enums/Gender.php:5`
  - ملاحظة: القيم في `StudentStatus` تختلف عن قيم migration الأصلية للـ status
    (موجودة في `database/migrations/2025_11_19_185900_create_students_table.php:36`).
- ClassSection.gender_type (SectionGenderType)
  - Cast: `app/Domains/Academic/ClassSection/Models/ClassSection.php:70`
- Staff.status (StaffStatus)
  - Cast: `app/Domains/HR/Staff/Models/Staff.php:56`
- StaffAttendance.status (StaffAttendanceStatus)
  - Cast: `app/Domains/HR/Staff/Models/StaffAttendance.php:34`
- AttendanceSetting.mode/responsible_role (AttendanceMode/AttendanceResponsibility)
  - Cast: `app/Domains/Academic/Attendance/Models/AttendanceSetting.php:29`
- Invoice.status (InvoiceStatus)
  - Cast: `app/Domains/Finance/Models/Invoice.php:39`
- PayrollBatch.status + payout_method (PayrollBatchStatus + PayoutMethod)
  - Cast: `app/Domains/HR/Payroll/Models/PayrollBatch.php:80`
- Loan.status (LoanStatus)
  - Cast: `app/Domains/HR/Payroll/Models/Loan.php:44`
- Homework.status + submission_type (HomeworkStatus + SubmissionType)
  - Cast: `app/Domains/Academic/Homework/Models/Homework.php:55`
- HomeworkSubmission.status (SubmissionStatus)
  - Cast: `app/Domains/Academic/Homework/Models/HomeworkSubmission.php:39`
- TimetableTemplate.status (TemplateStatus)
  - Cast: `app/Domains/Academic/Timetable/Models/TimetableTemplate.php:54`
- TimeSlot.type (TimeSlotType)
  - Cast: `app/Domains/Academic/Timetable/Models/TimeSlot.php:54`
- LedgerEntry.direction/category/status (LedgerDirection/LedgerCategory/LedgerStatus)
  - Cast: `app/Domains/Finance/Ledger/Models/LedgerEntry.php:36`
- Payment.method (PaymentMethod)
  - Cast: `app/Domains/Finance/Models/Payment.php:33`

DB Evidence: DDL الحالي لهذه الجداول لا يحتوي CHECK على هذه الأعمدة (انظر Appendix A).

---

## 3) CHECK موجودة في DB بدون Enum/Cast (Verified)
هذه القيم محمية على مستوى DB لكن لا يوجد Enum واضح في الكود حتى الآن.
هذه ليست مشكلة فورية، لكنها فجوة في الاتساق.

- admission_applications.status
  - DDL: `database/migrations/2025_11_19_185850_create_admission_applications_table.php:29`
  - Model لا يحتوي casts: `app/Domains/Academic/Student/Models/AdmissionApplication.php:25`
- discipline_incidents.status
  - DDL: sqlite_master (Appendix A)
  - لا يوجد Model/Enum حالياً في app/*
- payroll_policies.month_days_type
  - DDL: sqlite_master (Appendix A)
  - Model بدون casts: `app/Domains/HR/Payroll/Models/PayrollPolicy.php:7`
- audit_logs.action
  - DDL: sqlite_master (Appendix A)
  - لا يوجد Enum حالياً (مقبول إذا اعتبرناه framework-level)

---

## 4) Data operations داخل migrations (Verified)
هذه migrations تخلط schema + data أو تستخدم Models داخل migration.
المخاطر: فشل تشغيل migrate:fresh، أو كسر عند تغيّر الـ Model.

- `database/migrations/2026_02_02_000001_alter_invoices_status_enum.php:17` (DB::table update + drop/rename)
- `database/migrations/2026_02_05_000002_add_category_key_to_monthly_grades.php:18` (Model + chunkById + updates)
- `database/migrations/2026_02_08_000001_add_term_id_to_grading_templates_table.php:22` (Model + backfill)
- `database/migrations/2026_02_04_143541_add_term_id_to_attendances.php:18` (backfill + RuntimeException)
- `database/migrations/2026_02_03_225114_add_term_id_to_timetables_with_strict_guard.php:21` (backfill + RuntimeException)
- `database/migrations/2026_02_02_000001_add_term_id_to_student_marks_unique_index.php:16` (backfill + RuntimeException)
- `database/migrations/2026_02_04_141839_add_weekend_days_to_academic_years.php:17` (DB update)
- `database/migrations/2026_02_03_151151_add_academic_year_id_to_payroll_batches.php:24` (DB backfill loop)

---

## 5) Guard patterns تخفي أخطاء أو توسّع migrations (Verified)
- try/catch على dropUnique:
  - `database/migrations/2026_02_03_231357_make_course_offerings_term_aware.php:15`
- كثرة Schema::hasColumn داخل migrations:
  - `database/migrations/2026_01_08_000000_add_missing_columns_to_grading_templates.php:11`
  - `database/migrations/2025_12_21_200005_alter_payroll_items_table.php:19`
  - `database/migrations/2026_02_01_000001_add_term_year_to_student_marks_table.php:11`
  - `database/migrations/2026_02_08_000001_add_term_id_to_grading_templates_table.php:12`
- استخدام change() (خطر إسقاط constraints في SQLite عند إعادة بناء الجدول):
  - `database/migrations/2026_02_03_225114_add_term_id_to_timetables_with_strict_guard.php:54`
  - `database/migrations/2026_02_04_141839_add_weekend_days_to_academic_years.php:23`
  - `database/migrations/2026_02_04_143541_add_term_id_to_attendances.php:57`
  - `database/migrations/2026_01_27_180618_make_national_id_nullable_in_students_table.php:14`

---

## 6) كيف يدخل هذا في خطة الـ PRs (Priorities)
### Priority 1 (High) — PR-01b: Enum/DB CHECK Alignment
تركيز على الـ mismatches التي تسبب crash:
- GuardianRelationship
- EnrollmentStatus
- ResultDecision
- FinalResultStatus

### Priority 2 (Medium) — PR-11: Restore/Introduce CHECK Constraints
إعادة CHECK للأعمدة التي تُدار عبر Enums (بعد فحص القيم الحالية).

### Priority 3 (Medium) — PR-11: Migration Governance
سياسات واضحة تمنع data ops داخل migrations مستقبلًا، مع بدائل عبر Commands/Seeders.

### Deferred (حسب طلبك)
قسم الفهارس على مفاتيح FK مؤجل، ولا يدخل في الأولويات الحالية.

---

## Appendix A — SQLite DDL Snapshot (Tables without CHECK on enum columns)
مقتطفات من sqlite_master تُثبت غياب CHECK في الأعمدة المذكورة:

- academic_years
  - `CREATE TABLE "academic_years" (... "status" varchar not null, ...)`
- terms
  - `CREATE TABLE "terms" (... "status" varchar not null default 'pending', ...)`
- students
  - `CREATE TABLE "students" (... "gender" varchar not null, "status" varchar not null default ('active'), ...)`
- class_sections
  - `CREATE TABLE "class_sections" (... "gender_type" varchar not null default ('mixed'), ...)`
- staff
  - `CREATE TABLE "staff" (... "status" varchar not null default ('active'), ...)`
- staff_attendance
  - `CREATE TABLE "staff_attendance" (... "status" varchar not null, ... "source" varchar check (...))`
- attendance_settings
  - `CREATE TABLE "attendance_settings" (... "mode" varchar not null default 'checkpoints', "responsible_role" varchar not null default 'subject_teacher', ...)`
- invoices
  - `CREATE TABLE "invoices" (... "status" varchar not null default ('unpaid'), ...)`
- payroll_batches
  - `CREATE TABLE "payroll_batches" (... "status" varchar not null default ('draft'), "payout_method" varchar, ...)`
- loans
  - `CREATE TABLE "loans" (... "status" varchar not null default 'pending', ...)`
- homeworks
  - `CREATE TABLE "homeworks" (... "submission_type" varchar not null, "status" varchar not null default 'draft', ...)`
- homework_submissions
  - `CREATE TABLE "homework_submissions" (... "status" varchar not null default 'pending', ...)`
- timetable_templates
  - `CREATE TABLE "timetable_templates" (... "status" varchar not null default 'draft', ...)`
- time_slots
  - `CREATE TABLE "time_slots" (... "type" varchar not null default 'academic', ...)`
- ledger_entries
  - `CREATE TABLE "ledger_entries" (... "direction" varchar not null, "category" varchar not null, "status" varchar not null default ('posted'), ...)`
- payments
  - `CREATE TABLE "payments" (... "method" varchar not null default ('cash'), "status" varchar check (...), ...)`

---

## Appendix B — Queries/Commands (Evidence)
- `ls database/migrations | wc -l` → 135
- `ls -l database/database.sqlite` → حجم الملف (غير فارغ)
- `SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';` → 105
- `rg -n "^enum " app | wc -l` → 36
- `SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND (lower(sql) LIKE '%check (%' OR lower(sql) LIKE '%check(%');` → 30
- `SELECT name, sql FROM sqlite_master WHERE type='table' AND name IN (...);` (Appendix A)
