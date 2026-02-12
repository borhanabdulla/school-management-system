# خطة إصلاح دومين الطلاب - تاسكات تنفيذ

**المرجع:** هذا الملف يترجم فحص دومين الطلاب إلى تاسكات تنفيذية دقيقة.  
**مبدأ التنفيذ:** لا تغيير لسلوك يعمل إلا إذا كان يسبب Crash، خلل منطقي مؤكد، أو ضوضاء آمنة للحذف.

---

## نطاق الإصلاح (وفق فحص MCP)

- **Routes ذات الصلة:**  
  - `students`  
  - `students/register`  
  - `students/{id}`
- **جداول قاعدة البيانات الأساسية:**  
  - `students`  
  - `student_enrollments`  
  - `student_guardian`  
  - `student_marks`  
  - `homework_submissions`  
  - `exam_seatings`  
  - `term_results`  
  - `annual_results`  
  - `invoices`  
  - `attendances`

---

## ST-0 — إصلاح أعطال تشغيل (Crash Safety)

**الهدف:** إزالة أي خطأ SQL مباشر في البحث والفلترة.

- [x] **StudentLookupService: إزالة/استبدال `student_code` غير الموجود**  
  - الملف: `app/Domains/Academic/Student/Services/StudentLookupService.php`  
  - السبب: العمود غير موجود في DB → يؤدي إلى Crash عند البحث.  
  - التعديل: استبداله بـ `admission_number` أو إزالته كليًا.

---

## ST-1 — تسجيل الطالب: اتساق الصف/الشعبة والسنة

**الهدف:** منع تعيين طالب لشعبة من سنة مختلفة أو بصف غير مطابق.

- [x] **StudentRegistration: فلترة الشُعب على السنة النشطة**  
  - الملف: `app/Livewire/Student/StudentRegistration.php`  
  - التعديل: جلب الشعب عبر `ClassSectionLookupService` أو شرط `academic_year_id = activeYear`.

- [x] **AssignStudentToClassAction: التأكد من أن الشعبة تنتمي للسنة النشطة**  
  - الملف: `app/Domains/Academic/Student/Actions/AssignStudentToClassAction.php`  
  - التعديل: رفض التعيين إذا `section->academic_year_id !== activeYear->id`.

- [x] **RegisterStudentAction: تطبيق AcademicWriteGuard**  
  - الملف: `app/Domains/Academic/Student/Actions/RegisterStudentAction.php`  
  - التعديل: منع التسجيل إذا السنة مغلقة/مؤرشفة.

- [x] **AssignStudentToClassAction: Guard للسنة النشطة**  
  - الملف: `app/Domains/Academic/Student/Actions/AssignStudentToClassAction.php`  
  - التعديل: منع التعيين إذا السنة مغلقة/مؤرشفة.

- [x] **RegisterStudentAction: لا تملأ `current_class_section_id` قبل نجاح التعيين**  
  - الملف: `app/Domains/Academic/Student/Actions/RegisterStudentAction.php`  
  - التعديل: اجعلها `null` ثم اعتمد على `AssignStudentToClassAction` + `placementSync`.

---

## ST-2 — تضارب إنشاء الفاتورة في التسجيل (Duplicate Invoice)

**الهدف:** منع إنشاء فاتورتين لنفس التسجيل.

- [x] **StudentRegistrationData: تمرير `create_invoice` فعليًا من الواجهة**  
  - الملف: `app/Domains/Academic/Student/Data/StudentRegistrationData.php`

- [x] **توحيد جهة إنشاء الفاتورة (قرار واحد فقط)**  
  - الملف: `app/Livewire/Student/StudentRegistration.php`  
  - القرار المقترح: **اعتماد Action فقط** + حذف الإنشاء اليدوي،  
  مع جعل `create_invoice = (create_invoice && final_total > 0)` لتجنّب إنشاء فاتورة صفرية.

---

## ST-3 — فلترة دليل الطلاب بالسنة الصحيحة (منع تضليل)

**الهدف:** عند اختيار سنة، يجب الاعتماد على enrollments وليس current_class_section.

- [x] **StudentLookupService: فلترة `academicYearId` عبر enrollments**  
  - الملف: `app/Domains/Academic/Student/Services/StudentLookupService.php`  
  - التعديل: استخدام `student_enrollments` لتحديد السنة/الصف/الشعبة.

---

## ST-4 — حماية حذف الطالب (منع فقد بيانات بسبب cascade)

**الهدف:** منع حذف طالب له بيانات أكاديمية أو مالية مرتبطة فعليًا.

- [x] **DeleteStudentAction: فحص تبعيات مباشرة (مستهدفة)**  
  - الملف: `app/Domains/Academic/Student/Actions/DeleteStudentAction.php`  
  - تشمل (على الأقل): جداول تحمل `student_id` مباشرة مثل  
    `student_marks`, `homework_submissions`, `exam_seatings`, `term_results`, `promotions`,  
    `student_health_conditions`, `student_previous_histories`.

> الهدف: منع حذف يؤدي لخسارة سجلات تقييم/اختبارات/واجبات.

---

## ST-4.5 — حماية الحذف على مستوى الموديل (Critical)

**الهدف:** منع حذف مباشر خارج الأكشن (مسارات غير رسمية).

- [x] **Student.php: إضافة `enrollments` إلى `protectedRelations`**  
  - الملف: `app/Domains/Academic/Student/Models/Student.php`

---

## ST-5 — تنظيف/تصحيح StudentEnrollmentQueryService

**الهدف:** إزالة ميثود غير مستخدمة أو إصلاحها لتفادي مشاكل مستقبلية.

- [x] **إصلاح أو حذف `studentsWithEnrollmentGrade()`**  
  - الملف: `app/Domains/Academic/Student/Services/StudentEnrollmentQueryService.php`  
  - القرار: إن كانت غير مستخدمة → حذفها. وإن كانت مستخدمة → تصحيح الأعمدة.

---

## ST-6 — صورة الطالب عند الإنشاء

**الهدف:** تمكين رفع صورة الطالب وتخزينها في `profile_photo_path`.

- [x] **إضافة حقل صورة في الفورم والواجهة**  
  - الملفات:  
    - `app/Livewire/Forms/Student/StudentRegistrationForm.php`  
    - `resources/views/livewire/student/registration/steps/1-student-info.blade.php`
- [x] **تمرير الصورة في StudentRegistrationData**  
  - الملف: `app/Domains/Academic/Student/Data/StudentRegistrationData.php`
- [x] **حفظ الصورة أثناء التسجيل**  
  - الملف: `app/Domains/Academic/Student/Actions/RegisterStudentAction.php`

---

## اختبارات مستهدفة

- [ ] Test: البحث في دليل الطلاب لا يرمي خطأ (student_code).  
- [ ] Test: تسجيل الطالب لا يُنشئ أكثر من فاتورة.  
- [ ] Test: منع تعيين طالب لشعبة من سنة مختلفة.  
- [ ] Test: فلترة الدليل بالسنة تعتمد على enrollments (وليس current_class_section).  
- [ ] Test: حذف الطالب يُمنع عند وجود student_marks/homework/term_results.
- [ ] Test: رفع صورة الطالب عند التسجيل (مسار حفظ الملف).

---

## مخرجات متوقعة

- البحث في دليل الطلاب يعمل بدون Crash.  
- لا يمكن تعيين طالب لشعبة من سنة مختلفة.  
- لا يحدث Duplicate Invoice عند التسجيل.  
- فلترة الطلاب بالسنة صحيحة وتعكس enrollments.  
- حذف الطالب محمي ضد فقد البيانات الأكاديمية.
