# Observers غير مربوطة أو غير مستخدمة

تم فحص جميع Observers في المشروع والتأكد من ربطهم فعليًا بالـ Models عبر دوال `observe` في الـ Providers أو في أماكن أخرى.

## Observers المرتبطة فعليًا:

تم العثور على الربط الفعلي للـ Observers التالية:

- LeaveRequestObserver: مرتبط في AppServiceProvider
- StaffAttendanceObserver: مرتبط في AppServiceProvider
- SubstitutionObserver: مرتبط في AppServiceProvider
- SchoolEventObserver: مرتبط في AppServiceProvider
- HomeworkObserver: مرتبط في AppServiceProvider
- HomeworkSubmissionObserver: مرتبط في AppServiceProvider
- StudentObserver: مرتبط في AppServiceProvider
- AcademicYearObserver: مرتبط في AppServiceProvider
- TermObserver: مرتبط في AcademicServiceProvider

## Observers غير مربوطة (ميتة):

- TimetableTemplateObserver: لا يوجد أي ربط له عبر observe في أي Provider أو مكان آخر في الكود، وبالتالي يعتبر غير مستخدم حاليًا.

---

### منهجية الفحص:
- تم استخدام grep للبحث عن كل Observer في جميع ملفات app/ والتأكد من وجود ربط عبر دوال observe.
- تم توثيق النتائج بدقة مع ذكر أماكن الربط أو غيابها.

### التوصية:
- حذف TimetableTemplateObserver أو ربطه فعليًا إذا كان هناك حاجة مستقبلية له.
- مراجعة أي Observer جديد والتأكد من ربطه الفعلي لتجنب الكود الميت.
