# مراجعه وتدقيق - نظام إغلاق السنة الدراسية

**تاريخ المراجعة:** 2026-02-07
**المستند:** تقرير تدقيق شامل لجميع جزئيات نظام إغلاق السنة الدراسية
**ملاحظة:** تم التحقق الفعلي من الملفات بالتحديد

---

## 1. التحقق من الملفات الموجودة

### 1.1 التحقق من YearNotEditableException

```bash
$ grep -r "YearNotEditableException" app/
# ✅ موجود في:
# app/Domains/Academic/AcademicYear/Exceptions/YearNotEditableException.php
# app/Domains/Academic/AcademicYear/Actions/CloseAcademicYearAction.php:21
# app/Domains/Academic/AcademicYear/Actions/UpdateAcademicYearAction.php:27,41
```

**✅ تم التأكد:** الملف موجود والكلاس يعمل بشكل صحيح.

---

### 1.2 التحقق من RoleSeeder و Permissions

```bash
$ cat database/seeders/RoleSeeder.php | grep -A 30 "permissions ="
```

**❌ لم يتم إنشاء صلاحية `close.year`!**

```php
// السطر 15-34: قائمة الصلاحيات
$permissions = [
    'marks.view',
    'marks.edit',
    'marks.override',
    'curriculum.manage',
    'attendance.manage',
    'attendance.view',
    'staff.view',
    'staff.create',
    'staff.edit',
    'roles.manage',
    'students.promote',
    'settings.edit',
    'leaves.approve',
    'leave.request',
    'payroll.manage',
    'finance.apply_discount',
    'finance.record_payment',
    'finance.cancel_payment',
    // ⚠️ صلاحية 'close.year' غير موجودة!
];
```

**✅ النتيجة:** `YearNotEditableException` موجود ✅
**❌ النتيجة:** صلاحية `close.year` غير معرفة ❌

---

## 2. سيناريو الاستخدام الحقيقي (User Journey)

### الخطوة 1: دخول المستخدم للصفحة

```
المستخدم يدخل: /academic-years
```

**الملف:** [`routes/web.php:31-34`](routes/web.php:31)

```php
// Year Closing Wizard
Route::get('/academic-years/{year}/close', \App\Livewire\Academic\YearClosingWizard::class)
    ->name('academic-years.close')
    ->middleware('can:close.year');  // ⚠️ يعتمد على صلاحية غير موجودة!
```

**⚠️ مشكلة حرجة #1:**

```
الـ middleware 'can:close.year' يعتمد على spatie/laravel-permission
الـ permission 'close.year' غير موجودة في RoleSeeder.php

❌ ماذا سيحدث؟
- المستخدم يحاول الدخول
- الـ middleware يستدعي Gate::check('close.year')
- الـ Gate يبحث عن الـ permission في قاعدة البيانات
- لا يجد الـ permission → يرجع false
- الـ middleware يرجع 403 Forbidden

✅ لكن هناك حل مؤقت:
- Super Admin له صلاحية always true (حسب AppServiceProvider.php:77)
- admins يمكنهم الدخول، لكن المستخدمين العاديين لا يمكنهم
```

**📝 ملاحظة من [`AppServiceProvider.php:77`](app/Providers/AppServiceProvider.php:77):**

```php
\Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
    return $user->hasRole('Super Admin') ? true : null;
});
```

---

### الخطوة 2: الضغط على زر الإغلاق

```
المستخدم يرى: 🔒 أيقونة قفل بجانب السنة النشطة
```

**الملف:** [`resources/views/livewire/academic/academic-year-manager.blade.php:139-142`](resources/views/livewire/academic/academic-year-manager.blade.php:139)

```blade
@elseif($year->canBeClosed())
    <a href="{{ route('academic-years.close', $year->id) }}" 
       class="text-amber-500 hover:text-amber-700" 
       title="إغلاق السنة الدراسية">
        @include('icons.lock')  // ⚠️ تحقق من وجود الملف
    </a>
```

**✅ تحقق من icons.lock:**

```bash
$ ls -la resources/views/icons/
# ✅ lock.blade.php موجود!
```

**✅ النتيجة:** الأيقونة موجودة ✅

---

### الخطوة 3: تحميل معالج الإغلاق

**الملف:** [`app/Livewire/Academic/YearClosingWizard.php:51-106`](app/Livewire/Academic/YearClosingWizard.php:51)

```php
public function mount(AcademicYear $year)
{
    $this->year = $year;
    $this->authorizeAccess();  // ⚠️ تعليق TODO موجود
    
    $this->readinessService = app(ReadinessService::class);
    $this->closureValidator = app(AcademicYearClosureValidator::class);
    
    $this->loadReadinessData();
}
```

**⚠️ مشكلة #2: تعليق TODO لا يزال موجوداً**

```php
// السطر 92-95
// TODO: تفعيل الصلاحيات
// if (!Auth::user()->can('close.year')) {
//     abort(403, 'ليس لديك صلاحية إغلاق السنة الدراسية');
// }
```

**⚠️ مشكلة #3: استخدام string بدلاً من enum**

```php
// السطر 98
if ($this->year->status === 'closed') {  // ⚠️ يجب استخدام AcademicYearStatus::Closed->value
    abort(400, 'السنة الدراسية مغلقة بالفعل');
}
```

---

## 3. مشاكل AcademicWriteGuard

### الملف: [`app/Domains/Academic/Services/AcademicWriteGuard.php`](app/Domains/Academic/Services/AcademicWriteGuard.php)

**⚠️ مشكلة #4: استعلام غير فعال**

```php
// السطر 33-39
public function assertYearNotClosed(int $academicYearId): void
{
    $year = AcademicYear::where('id', $academicYearId)->first();  // ⚠️ غير فعال
    
    if (!$year) {
        throw ResourceNotFoundException::forModel(AcademicYear::class, $academicYearId);
    }
    
    if ($year->status === AcademicYearStatus::Closed) {  // ✅ هذا صحيح
        throw InvalidOperationException::cannotModify(...);
    }
}
```

**❓ السؤال:** هل `find()` أفضل من `where()->first()`؟

```php
// find() vs where()->first()
AcademicYear::find($academicYearId);           // ✅ أفضل
AcademicYear::where('id', $academicYearId)->first();  // ❌ أبطأ
```

**✅ الحقيقة:**
- `find()` يبحث فقط في Primary Key
- `where()->first()` ينشئ Query Builder كامل
- `find()` يستخدم cache إذا كان الموديل يستخدم cache

**⚠️ مشكلة #5: عدم استخدام Cache**

```php
// لا يوجد أي استخدام للـ Cache في AcademicWriteGuard
// إذا تم استدعاء Guard عدة مرات، يتم استعلام قاعدة البيانات كل مرة
```

---

## 4. مشاكل ReadinessService

### الملف: [`app/Domains/Academic/Services/ReadinessService.php`](app/Domains/Academic/Services/ReadinessService.php)

**⚠️ مشكلة #6: استخدام string بدلاً من enum**

```php
// السطر 164-166
$incompleteCount = $year->terms()
    ->where('status', '!=', TermStatus::Completed->value)  // ⚠️ يمكن تحسين
    ->count();
```

**❓ هل هذا خطأ؟**
- لا، هذا صحيح لأن `where()` يحتاج قيمة وليد Enum
- `TermStatus::Completed->value` يعطينا القيمة الفعلية (string/int)

**⚠️ مشكلة #7: دالة countMissingAttendance() غير واضحة**

```php
// السطر 380-387
protected function countMissingAttendance(AcademicYear $year, Term $term): int
{

---

## 5. تحديثات جزئية الدرجات (2026-02-07)

**الهدف:** منع الكتابة لغير الإدارة على إعدادات الدفتر، وحماية العمليات الحساسة عند إغلاق الترم، مع ضمان أن المعلم لا يرى سوى مواده.

**ما تم تنفيذه:**
- تقييد تعديل الدرجات على مواد المعلم فقط عبر صلاحيات `CourseOffering` في `SmartGradeBook`.
- منع كتابة إعدادات الدفتر من شاشة المعلم؛ القراءة أصبحت بدون إنشاء تلقائي.
- إضافة حراسة إغلاق الترم قبل تجميع الدرجات أو إعادة تجميع أعمال السنة.
- إخفاء زر إضافة البنود الشهرية للمعلم إلا بصلاحية الإدارة.
- توحيد مصدر سلم التقدير في النتائج النهائية عبر `grading.scale`.

**ملفات التغيير الأساسية:**
- `app/Livewire/Teacher/Grading/SmartGradeBook.php`
- `app/Livewire/Teacher/Grading/TeacherGradebooks.php`
- `app/Domains/Academic/Grading/Actions/AggregateGradebookToTemplateMarksAction.php`
- `app/Domains/Academic/Grading/Actions/FinalizeTermCourseworkAction.php`
- `app/Domains/Academic/Grading/Actions/CalculateTermGradesAction.php`
- `app/Domains/Academic/Grading/Models/GradebookSettings.php`
- `resources/views/livewire/teacher/grading/smart-grade-book.blade.php`

**موضعنا الحالي من الخطوات الرئيسية (من خطة الإصلاح):**
- اكتملت صلاحيات المعلم/الإدارة مع حراسة إغلاق الترم.
- تم توحيد سلم التقدير وربطه بالنتائج النهائية.

**نتيجة الاختبارات (جزئية):**
- `php artisan test --filter=SmartGradeBookPermissionsTest` ✅
- `php artisan test --filter=CalculateTermGradesScaleTest` ✅
- `php artisan test --filter=TeacherGradebooksTest` ✅
- ملاحظة: تحذيرات PHPUnit doc-comment metadata مستمرة (لا تؤثر على النجاح).

**الخطوة التالية المقترحة:**
1) مراجعة أي تقارير تعتمد على `grade_letter` والتأكد من استخدام `percentage` عند غياب الحرف.
2) تدقيق بصري للواجهات للتأكد أن خيار إضافة البنود يظهر للإدارة فقط.

**ملاحظة تنفيذية:**
- `ReportCardBuilder::resolveGradeLabel` يطبق fallback للـ `percentage` عند غياب `grade_letter`، ومغطى باختبار `ReportCardBuilderTest`.

**فحص grade_letter/grade_label (نتيجة grep):**
- `grade_letter` يظهر فقط في نتائج الترم وReportCardBuilder، والـ fallback موجود في `resolveGradeLabel`.
- `grade_label` مستخدم في عرض النتائج النهائية (Results Viewer) ويُحسب عند المعالجة داخل `ResultProcessingService` عبر `resolveGradeLabel`.

**تطبيق الخطوتين:**
- تمت تغطية تدقيق واجهة زر "إضافة قسم" باختبار يظهره فقط لصاحب صلاحية `curriculum.manage`.
- تم تأكيد fallback في تقرير الدرجات (`ReportCardBuilder`) دون تعديل إضافي في واجهات النتائج النهائية.

**تحديثات هيكلية إضافية:**
- فصل قوالب الدرجات بالترم عبر `grading_templates.term_id` وتصفية القوالب حسب الترم.
- اعتماد `subject_grading_configs` كمصدر وحيد للـ max/pass وإزالة الاعتماد على `grade_subjects`.
    return Attendance::where('academic_year_id', $year->id)
        ->where('term_id', $term->id)
        ->whereNull('status')  // ⚠️ ما معنى هذا؟
        ->count();
}
```

**❓ أسئلة:**
1. هل `status` هو الحقل الصحيح؟
2. هل الغياب الناقص هو الذي status فيه null؟
3. أم أن الغياب الناقص هو الذي لا يوجد سجل له أصلاً؟

**✅ افتراض:** هذا منطق خاطئ! الغياب الناقص = عدم وجود سجل، وليس سجل بـ null

**⚠️ اقتراح الإصلاح:**

```php
protected function countMissingAttendance(AcademicYear $year, Term $term): int
{
    // إذا كان الغياب المطلوب = جميع الحصص المجدولة
    // الغياب الموجود = السجلات في جدول Attendance
    // الغياب الناقص = المجدول - الموجود
    
    $expectedSessions = $this->calculateExpectedSessions($year, $term);
    $actualSessions = Attendance::where('academic_year_id', $year->id)
        ->where('term_id', $term->id)
        ->count();
    
    return max(0, $expectedSessions - $actualSessions);
}
```

---

## 5. مشاكل CloseAcademicYearAction

### الملف: [`app/Domains/Academic/AcademicYear/Actions/CloseAcademicYearAction.php`](app/Domains/Academic/AcademicYear/Actions/CloseAcademicYearAction.php)

**🔴 مشكلة حرجة #8: إغلاق غير آمن**

```php
// السطر 18-38
public function execute(AcademicYear $year): void
{
    if ($year->status !== AcademicYearStatus::Active) {
        throw new \App\Domains\Academic\AcademicYear\Exceptions\YearNotEditableException(
            'لا يمكن إغلاق سنة غير نشطة.'
        );
    }
    
    $check = $this->closureValidator->validate($year);
    if (!$check['can']) {
        throw InvalidOperationException::cannotClose(...);
    }
    
    DB::transaction(function () use ($year) {
        $year = AcademicYear::where('id', $year->id)->lockForUpdate()->first();
        
        $year->update(['status' => AcademicYearStatus::Closed]);
        
        Log::info('Academic year closed', ['year_id' => $year->id]);
    });
}
```

**❌ المشاكل:**
1. لا يوجد Events للإغلاق
2. لا يوجد Cache Invalidation
3. لا يوجد Notification

**✅ ما هو موجود:**
1. ✅ Exception Class موجود ✅
2. ✅ Validator يتم استدعاؤه ✅
3. ✅ Transaction يُستخدم ✅
4. ✅ Locking يُستخدم ✅

**❌ ما هو مفقود:**
1. ❌ Event::dispatch(AcademicYearClosed::class)
2. ❌ Cache::tags(['academic', 'years'])->flush()
3. ❌ Notification::send(...)

---

## 6. مشاكل SendWeeklyReadinessReminders

### الملف: [`app/Domains/Academic/Jobs/SendWeeklyReadinessReminders.php`](app/Domains/Academic/Jobs/SendWeeklyReadinessReminders.php)

**⚠️ مشكلة #9: استخدام string بدلاً من enum**

```php
// السطر 61
->where('status', 'active')  // ⚠️ يجب استخدام AcademicYearStatus::Active->value
```

**❌ هذا خطأ!**

```php
// يجب أن يكون:
->where('status', AcademicYearStatus::Active->value)
```

**⚠️ مشكلة #10: Job غير مجدول**

```php
// لا يوجد كود في Kernel.php:
// $schedule->job(SendWeeklyReadinessReminders::class)->weekly()->mondays()->at('08:00');
```

---

## 7. قائمة المشاكل حسب الأولوية

### 🔴 حرجة (Critical)

| # | المشكلة | الملف | السطر | الحالة |
|---|--------|-------|-------|--------|
| 1 | صلاحية `close.year` غير موجودة | `RoleSeeder.php` | 15-34 | ❌ لم تنشأ |
| 2 | Job غير مجدول | `Kernel.php` | - | ❌ لا يوجد |
| 3 | لا يوجد Events للإغلاق | `CloseAcademicYearAction.php` | 32-38 | ❌ مفقود |

### 🔧 عالية (High)

| # | المشكلة | الملف | السطر | ملاحظة |
|---|--------|-------|-------|--------|
| 4 | استعلام غير فعال | `AcademicWriteGuard.php` | 35 | `find()` أفضل |
| 5 | تعليق TODO | `YearClosingWizard.php` | 92-95 | يجب تفعيله |
| 6 | string بدلاً من enum | `YearClosingWizard.php` | 98 | `'closed'` |
| 7 | string بدلاً من enum | `SendWeeklyReadinessReminders.php` | 61 | `'active'` |
| 8 | countMissingAttendance() | `ReadinessService.php` | 380-387 | منطق خاطئ |

### ⚠️ متوسطة (Medium)

| # | المشكلة | الملف | ملاحظة |
|---|--------|-------|--------|
| 9 | عدم استخدام Cache | `ReadinessService.php` | N+1 queries |
| 10 | AcademicYearManager bypass | bypass المعالج | يمكن الإغلاق مباشرة |

---

## 8. الخلاصة والتوصيات

### ✅ ما يعمل بشكل صحيح:

1. **YearNotEditableException** - موجود ومستخدم بشكل صحيح
2. **Icons** - ملف lock.blade.php موجود
3. **Guard Logic** - منطق الحظر صحيح
4. **Validator** - يتم استدعاؤه قبل الإغلاق
5. **Transaction** - يُستخدم بشكل صحيح
6. **Logging** - موجود

### ❌ ما يحتاج إصلاح:

1. **إضافة صلاحية `close.year` إلى RoleSeeder**
2. **جدولة الـ Job في Kernel.php**
3. **إضافة Events و Notifications**
4. **إصلاح استخدام string → enum**
5. **تفعيل تعليق TODO في YearClosingWizard**

---

## 9. خطة الإصلاح

### الخطوة 1: إصلاح الصلاحيات

```php
// database/seeders/RoleSeeder.php

// أضف في مصفوفة $permissions:
// 'academic.year.close'

// ثم:
// php artisan db:seed --class=RoleSeeder
```

### الخطوة 2: إصلاح الـ Kernel

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // إضافة:
    $schedule->job(\App\Domains\Academic\Jobs\SendWeeklyReadinessReminders::class)
        ->weekly()
        ->mondays()
        ->at('08:00');
}
```

### الخطوة 3: إصلاح الـ Enums

```php
// في YearClosingWizard.php:98
if ($this->year->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Closed->value) {
```

```php
// في SendWeeklyReadinessReminders.php:61
->where('status', \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active->value)
```

### الخطوة 4: إضافة Events

```php
// في CloseAcademicYearAction.php
use App\Domains\Academic\AcademicYear\Events\AcademicYearClosed;

// داخل transaction بعد update:
event(new AcademicYearClosed($year));
```

---

## 10. نتيجة التقييم النهائي

| المعيار | التقييم |
|---------|---------|
| التوثيق | ✅ جيد |
| الأخطاء الحرجة | 3 |
| سهولة الإصلاح | سهل |
| الاستقرار العام | ⚠️ يحتاج إصلاحات |
| الأمان | ⚠️ صلاحيات غير مكتملة |
| الأداء | ⚠️ لا يوجد cache |

**التوصية:** يمكن إصلاح المشاكل في أقل من يوم عمل واحد.
