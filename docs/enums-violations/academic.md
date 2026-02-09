## مخالفات Enums في دومين Academic

### 1) `AcademicYearStatus`

- **الوصف:** Enum لحالة السنة الدراسية:
  - `pending`, `active`, `closed`, `archived`

- **المخالفات المكتشفة:**
  1. استخدام Namespace قديم في بعض القوالب:
     - الرسائل في `storage/logs/laravel.log` تشير إلى:
       - `App\Enums\AcademicYearStatus` داخل:
         - `resources/views/livewire/academic/academic-year-manager.blade.php`
         - `resources/views/components/academic/year-card.blade.php`
  2. التعامل مع Enum كأنه Array في الـ Blade (من خلال رسالة: *Cannot access offset of type App\Enums\AcademicYearStatus on array*).

- **نوع المخالفة:**
  - بقايا لترحيل سابق من `App\Enums\AcademicYearStatus` إلى `App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus`.
  - سوء استخدام Enum في الـ Blade (محاولة استخدامه كمصفوفة بدل استدعاء methods مثل `color()`, `styles()`, إلخ).

- **اقتراحات الإصلاح:**
  - تحديث كل الإشارات إلى `App\Enums\AcademicYearStatus` في الـ Blade إلى:
    - `App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus`
  - التأكد أن الـ Blade يتعامل مع الـ Enum عبر:
    - استدعاء الدوال الموجودة داخله (`color()`, `styles()`, `label()`) بدلاً من التوقع أنه يحتوي على مفاتيح Array.
  - مراجعة compiled views المشار إليها في الـ logs للتأكد من موضع الـ match/الاستخدام الخاطئ ثم تعديل الـ Blade الأصلية.

### 2) `SectionGenderType`

- **الوصف:** Enum لنوع جنس الشعبة:
  - `boys`, `girls`, `mixed`

- **المشكلة:**
  - رسائل الخطأ في `storage/logs/laravel.log`:
    - `Unhandled match case of type App\Enums\SectionGenderType (View: ... class-section-manager.blade.php)`
  - هذا يدل على:
    - استخدام Namespace قديم (`App\Enums\SectionGenderType`).
    - أو وجود `match` في الـ Blade لا يغطي كل الحالات (`boys`, `girls`, `mixed`).

- **نوع المخالفة:**
  - ترحيل غير مكتمل للـ Enum إلى المسار الجديد:
    - `App\Domains\Academic\ClassSection\Enums\SectionGenderType`
  - match غير شامل لكل الحالات، يؤدي إلى `UnhandledMatchError`.

- **اقتراحات الإصلاح:**
  - تحديث namespace في `resources/views/livewire/academic/class-section-manager.blade.php` إلى المسار الجديد.
  - ضمان أن أي `match ($section->gender)` يغطي:
    - `SectionGenderType::Boys`
    - `SectionGenderType::Girls`
    - `SectionGenderType::Mixed`
  - يمكن إضافة default case للحماية، لكن الأفضل تغطية كل الـ cases صراحة.

### 3) تكامل Academic مع Finance عبر `InvoiceStatus` و `EnrollmentStatus`

- **أمثلة:**
  1. `PromotionService`:
     - يستخدم `InvoiceStatus` للتحقق من الإبراء المالي أثناء الترقية:
       - `app/Domains/Academic/Promotion/Services/PromotionService.php`
       - يحتوي شرطًا يجمع بين `InvoiceStatus::Paid->value` و `'paid'` (مخالفة موثقة في ملف Finance).
  2. `StudentLookupService`:
     - يستخدم `InvoiceStatus::unpaidValues()` لحساب المديونية:
       - `app/Domains/Academic/Student/Services/StudentLookupService.php`
     - يستخدم أيضًا `EnrollmentStatus` في الاستعلامات والفلترة.

- **نوع المسألة:**
  - Coupling مع دومين Finance مطلوب وظيفيًا، لكن يجب أن يظل تحت سيطرة واضحة.
  - عدم توحيد شكل قيمة الحالة المالية (`InvoiceStatus` مقابل `'paid'` String).

- **اقتراحات:**
  - نقل منطق الإبراء المالي إلى خدمة في Finance يتم استدعاؤها من Academic.
  - توثيق أن `PromotionService` يعتمد على Finance، ووضع واجهة/Service واضحة لذلك.

### 4) Enums أخرى في Academic

- **الحالة العامة:**
  - Enums مثل:
    - `AttendanceStatus`, `AttendanceMode`, `AttendanceResponsibility`
    - `HomeworkStatus`, `SubmissionType`, `SubmissionStatus`
    - `ResultDecision`, `FinalResultStatus`
    - `PromotionType`
    - `TermStatus`, `StudentStatus`, `EnrollmentStatus`
  - تم استخدامها في:
    - النماذج (casts)
    - الخدمات (Services)
    - الأكشنز (Actions)
    - الاختبارات
  - بدون ظهور استخدامات واسعة للقيم النصية البديلة خارج الحالات الطبيعية (مثل الـ Forms أو Seeders التي تعتمد على `->value`).

- **الاستنتاج:**
  - لا توجد مخالفات كبيرة مرتبطة بهذه الـ Enums في الوضع الحالي، لكنها تبقى نقاط يجب مراعاتها عند التوسع أو التعديل.

