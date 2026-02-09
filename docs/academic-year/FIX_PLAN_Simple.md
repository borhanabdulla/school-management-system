# خطة إصلاح بسيطة - نظام إغلاق السنة الدراسية

**تاريخ الخطة:** 2026-02-07
**الحالة:** جاهزة للتنفيذ
**الأولوية:** 🔴 حرجة

---

## المشاكل الفعلية فقط

### 1. صلاحية `close.year` غير موجودة

**الملف:** `database/seeders/RoleSeeder.php`

**الإصلاح:**

```php
// أضف في مصفوفة $permissions:
'academic.year.close'
```

**السبب:** الـ route يستخدم:
```php
->middleware('can:close.year');
```

---

### 2. استخدام string بدلاً من enum

**الملف:** `app/Livewire/Academic/YearClosingWizard.php`

```diff
- if ($this->year->status === 'closed') {
+ if ($this->year->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Closed->value) {
```

**السبب:** موديل AcademicYear يستخدم enum لـ status

---

### 3. تعليق TODO للصلاحيات

**الملف:** `app/Livewire/Academic/YearClosingWizard.php`

```diff
- // TODO: تفعيل الصلاحيات
- // if (!Auth::user()->can('close.year')) {
- //     abort(403, 'ليس لديك صلاحية إغلاق السنة الدراسية');
- // }

+ if (!Auth::user()->can('academic.year.close')) {
+     abort(403, 'ليس لديك صلاحية إغلاق السنة الدراسية');
+ }
```

---

### 4. استخدام string بدلاً من enum

**الملف:** `app/Domains/Academic/Jobs/SendWeeklyReadinessReminders.php`

```diff
- ->where('status', 'active')
+ ->where('status', \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active->value)
```

---

### 5. استخدام string بدلاً من enum

**الملف:** `app/Domains/Academic/Services/ReadinessService.php`

```diff
- ->where('status', '!=', TermStatus::Completed->value)
+ ->where('status', '!=', \App\Domains\Academic\Term\Enums\TermStatus::Completed->value)
```

---

## الملفات المطلوب تعديلها

| # | الملف | التغيير |
|---|------|--------|
| 1 | `RoleSeeder.php` | إضافة صلاحيات جديدة |
| 2 | `YearClosingWizard.php` | تفعيل الصلاحيات + إصلاح enum |
| 3 | `SendWeeklyReadinessReminders.php` | إصلاح enum |
| 4 | `ReadinessService.php` | إصلاح enum |

---

## خطوات التنفيذ

### الخطوة 1: RoleSeeder

```php
// database/seeders/RoleSeeder.php

public function run(): void
{
    // صلاحيات الأكاديميا الجديدة
    $academicPermissions = [
        'academic.year.close',
    ];

    // دمج مع الصلاحيات الموجودة
    $permissions = array_merge($permissions, $academicPermissions);
}
```

### الخطوة 2: تشغيل Seeder

```bash
php artisan db:seed --class=RoleSeeder
```

---

## ملخص

| المشكلة | الأولوية | الإصلاح |
|--------|---------|--------|
| صلاحية مفقودة | 🔴 | إضافة لـ RoleSeeder |
| string بدلاً من enum | 🔧 | تغيير في 3 ملفات |
| تعليق TODO | 🔧 | تفعيل التحقق |

**المجموع:** 4 ملفات فقط
