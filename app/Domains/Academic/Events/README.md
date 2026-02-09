# Events

أحداث النطاق الأكاديمي - للتواصل بين الموديولات.

## متى نستخدم Events؟

- إعلام موديولات أخرى بتغيير حدث
- تنفيذ عمليات غير متزامنة (Async)
- فصل المسؤوليات (Decoupling)

## الملفات المتوقعة

- `AcademicYearActivated.php` - تم تفعيل سنة دراسية
- `AcademicYearClosed.php` - تم إغلاق سنة دراسية
- `TermCompleted.php` - تم إكمال فصل دراسي
- `StructureChanged.php` - تم تغيير الهيكل الأكاديمي

## مثال

```php
class AcademicYearActivated
{
    public function __construct(
        public readonly AcademicYear $year,
        public readonly ?AcademicYear $previousYear = null,
    ) {}
}

// Listener
class NotifyStaffOfNewYear
{
    public function handle(AcademicYearActivated $event): void
    {
        // إرسال إشعارات للموظفين
    }
}
```
