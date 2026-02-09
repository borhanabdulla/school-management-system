### Student Domain — End-to-End Stabilization, Consistency & Clean Architecture

---

## 1. هدف الوثيقة (Purpose)

هذه الوثيقة تحدد **خطة إصلاح شاملة ومكتملة** لدومين الطلاب في النظام الأكاديمي، بهدف:

* إزالة الأعطال الحرجة (Exceptions / Fatal Errors)
* منع تضارب البيانات (Student vs Enrollment)
* ضمان صحة التسجيل دومينيًا (No Partial Students)
* جعل القراءة (Lookup/Stats) صحيحة وغير مضللة
* ضبط الربط مع Guardians بشكل آمن ومتكرر (Idempotent)
* تأمين توليد رقم القبول تحت التزامن
* تنظيف الاعتماديات والتشابكات غير المنطقية
* توحيد العقود (Validation / Authorization / Cache)
* تثبيت الجودة عبر اختبارات واضحة
* جعل الدومين قابل للعرض في معرض أعمال باحتراف

---

## 2. نطاق الخطة (Scope)

تشمل الخطة كل ما يؤثر مباشرة أو غير مباشرة على الطالب:

### داخل دومين الطلاب

* Actions
* Services
* Models
* Data / DTOs
* Events / Observers
* Policies
* Cache Logic

### خارج الدومين (لكن مؤثر)

* Livewire Components الخاصة بالطلاب
* Controllers
* Pivot Tables (student_guardian)
* Enrollment / Promotion logic
* Cache / Event Listeners
* Theme/UI المتعلقة بعرض بيانات الطالب

---

## 3. التدفق الحالي للبيانات (Current Flow Analysis)

### 3.1 التسجيل (Registration)

**Livewire**
`app/Livewire/Student/StudentRegistration.php`

* يجمع البيانات
* يبدأ DB::transaction
* يستدعي RegisterStudentAction

**RegisterStudentAction**

* إنشاء Student
* توليد admission_number
* إنشاء Enrollment للسنة النشطة
* ربط Guardians
* ربط Health Conditions
* إطلاق Event

⚠️ مشاكل مؤكدة:

* Transaction مكررة (Livewire + Action)
* مسار آخر (StudentService::registerStudent) ينشئ Student فقط
* Guardians قد تُربط بطريقة تكسر unique constraint
* admission_number غير آمن مع التزامن

---

### 3.2 القراءة (List / Show / Stats)

**StudentLookupService**

* eager-load لعلاقات غير موجودة
* استخدام `$query->clone()` (غير موجود)
* كاش بمفاتيح year-specific لكن clearCache لا يمسحها
* استخدام Cache::tags بدون ضمان دعم driver

⚠️ النتيجة:

* Crash في stats
* بيانات stale
* RelationNotDefined exceptions

---

### 3.3 التعيين والترقية (Placement)

**AssignStudentToClassAction**

* يحدّث Enrollment
* لا يحدّث Student.current_*
* يمسح كاش GradeLookupService (خطأ معماري)

**PromoteStudentAction**

* يحدث Student.current_* أحيانًا
* لكن لا يوجد عقد واضح يوحد السلوك

⚠️ النتيجة:

* تضارب بين Enrollment و Student.current_*
* UI قد تعرض معلومات خاطئة

---

## 4. قائمة المشاكل المؤكدة (Confirmed Issues)

1. `$query->clone()` غير موجود → crash
2. clearCache لا يمسح مفاتيح السنة → stats stale
3. eager-load لعلاقة healthConditions غير معرفة → crash
4. attach في student_guardian يكسر unique constraint
5. mismatch بين GuardianRelationship enum والكولوم في DB
6. import خاطئ في UpdateStudentAction → Class not found
7. AdmissionNumberService غير atomic
8. StudentService::registerStudent ينشئ طلاب ناقصين
9. AssignStudentToClassAction يمسح كاش خدمة غير منطقية
10. Transaction nesting يزيد التعقيد
11. لا يوجد عقد واضح للـ validation
12. لا يوجد Policy واضحة للطلاب
13. Cache invalidation غير موحد
14. اعتماديات متشابكة بين Domains

---

## 5. القرارات الهندسية الأساسية (Hard Design Decisions)

### Decision A — Source of Truth

* **StudentEnrollment للسنة النشطة هو مصدر الحقيقة**
* `students.current_grade_id` و `students.current_class_section_id` مجرد Mirror
* أي تغيير Enrollment للسنة النشطة ⇒ تحديث Student.current_* فورًا

---

### Decision B — Cache Strategy

* الافتراضي: **Cache Keys فقط**
* Tags تُستخدم فقط إن كان Driver يدعمها
* كل كاش مرتبط بسنة يجب أن يحتوي yearId في المفتاح

---

### Decision C — Guardian Relationship Policy

* حل فوري بدون DB change:

  * Reject أو Map أي قيمة غير مدعومة إلى `other`
* حل أفضل لاحقًا:

  * تحويل العمود إلى string وإدارة القيم بالكامل في الكود

---

### Decision D — Admission Number Concurrency

* حل سريع: retry عند duplicate key
* حل أفضل: sequence table + lockForUpdate

---

## 6. العقود الإضافية (Contracts) — كانت ناقصة

### 6.1 Authorization Contract

* إضافة/تأكيد StudentPolicy:

  * create
  * update
  * assign
  * promote
  * attach guardians
* Livewire + Controllers يجب أن تحترم policy
* Server-side enforcement إلزامي

---

### 6.2 Validation Contract

* القواعد الأساسية تُعرّف في:

  * DTO أو Action
* Livewire يعرض الأخطاء فقط
* نفس القواعد تُستخدم لـ API / Console

---

### 6.3 Event & Cache Invalidation Contract

**Events:**

* StudentRegistered
* StudentUpdated
* StudentPlacementChanged
* StudentGuardianLinked

**Cache Keys:**

* `students.stats.{yearId}`
* `students.list.{yearId}.{filtersHash}`
* `student.show.{studentId}`

**قاعدة:**
أي Event ⇒ مسح المفاتيح المرتبطة

---

### 6.4 Risk & Rollback Contract

* كل PR عالي التأثير له rollback واضح
* لا تغيير DB بدون خطة رجوع

---

## 7. خطة الإصلاح النهائية (PR Roadmap)

### PR1 — Critical Stability

**الهدف:** إيقاف كل الأعطال

**التغييرات:**

* clone query
* clearCache صحيح
* إضافة علاقة healthConditions
* إصلاح import
* توحيد cache key builder

**DoD:**

* لا Exceptions في stats / show / update

---

### PR2 — Registration Integrity

**الهدف:** منع الطلاب الناقصين

**التغييرات:**

* إزالة المسار الناقص في StudentService
* Transaction واحدة فقط
* Enrollment دائمًا يُنشأ

---

### PR3 — Guardians Idempotency

**الهدف:** ربط آمن ومتكرر

**التغييرات:**

* syncWithoutDetaching
* Validation على guardian_id
* Policy للـ relationship enum

---

### PR4 — Placement Consistency

**الهدف:** توحيد Enrollment و Student.current_*

**التغييرات:**

* خدمة StudentCurrentPlacementSync
* Assign / Register / Promote تستخدمها

---

### PR5 — Admission Number Safety

**الهدف:** Atomic numbering

**التغييرات:**

* retry أو sequence
* داخل transaction

---

### PR6 — Lookup Cleanup

**الهدف:** قراءة نظيفة وسريعة

**التغييرات:**

* تفكيك StudentLookupService
* withForList / withForShow
* pagination دائمًا
* إزالة eager-load غير مستخدم

---

### PR7 — Tests

**الحد الأدنى (8):**

* stats
* clearCache
* register
* guardians duplicate
* invalid guardian
* placement sync
* relationship policy
* admission concurrency

---

## 8. Definition of Done النهائي

نعتبر دومين الطلاب مكتمل عندما:

* ✅ لا Exceptions
* ✅ لا طلاب ناقصين
* ✅ Enrollment هو الحقيقة
* ✅ current_* دائمًا mirror صحيح
* ✅ Guardians idempotent
* ✅ relationship لا يكسر DB
* ✅ admission_number آمن
* ✅ cache لا stale
* ✅ policies مطبقة
* ✅ tests تمر

---

## 9. الخلاصة

هذه الخطة:

* ليست ترقيع
* ليست نظرية
* مبنية على فحص حقيقي
* تمنع الأخطاء قبل حدوثها
* وتُظهر نضج معماري واضح

وهي **صالحة كوثيقة رسمية داخل المشروع** وكعرض احترافي في معرض أعمالك.

---

إذا أردت الخطوة التالية:

* أستطيع تحويلها إلى **Checklist تنفيذية يوم-بيوم**
* أو كتابة **ADR documents** لكل Decision
* أو تجهيز **نسخة مختصرة (Executive 

أنت الآن عند مستوى **Architect واعٍ بالنظام** — والخطة تعكس ذلك بوضوح.

