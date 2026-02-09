# تقرير المخالفات المعمارية: Events & Listeners

تم فحص جميع الأحداث (Events) والمستمعين (Listeners) في المشروع school-dashboard، مع توثيق أماكن الربط والاستخدام الفعلي، وتحديد الأحداث أو المستمعين غير المرتبطين (ميتة) أو غير الموحدة.

---

## 1. الأحداث (Events) المستخدمة فعليًا

تم العثور على الأحداث التالية مستخدمة فعليًا (يتم إطلاقها عبر event() أو dispatch()):

- MonthlyGradeSaved: مرتبط بـ Listener (SyncMonthlyToStudentMark) عبر Event::listen في AppServiceProvider
- AttendanceBatchSaved: مرتبط بـ Listener (SyncAttendanceToMonthlyGrade) عبر Event::listen في AppServiceProvider
- StructureChanged: يتم إطلاقه في عدة Actions (Create/Update/DeleteClassSectionAction, CreateGradeAction)
- TeacherCreated: يتم إطلاقه في CreateTeacherAction
- LeaveRequestStatusChanged: يتم إطلاقه في LeaveRequestObserver
- InvoiceCreated: يتم إطلاقه في CreateInvoiceAction
- PaymentReceived: يتم إطلاقه في RecordPaymentAction
- TeacherAbsentWithClasses: يتم إطلاقه في AttendanceService
- TimetableTemplateUpdated: يتم إطلاقه في UpdateTimetableTemplateAction
- TimetableTemplateCreated: يتم إطلاقه في CreateTimetableTemplateAction
- AcademicYearActivated: يتم إطلاقه في ActivateAcademicYearAction
- StudentAttendanceSaved: يتم إطلاقه في RecordStudentAttendanceAction
- StudentAssignedToClass: يتم إطلاقه في AssignStudentToClassAction

## 2. الأحداث غير المرتبطة بمستمعين (Listeners)

- معظم الأحداث أعلاه يتم إطلاقها فقط (event/dispatch) ولا يوجد Listener فعلي لها إلا MonthlyGradeSaved وAttendanceBatchSaved وStudentAttendanceSaved.

## 3. المستمعون (Listeners) غير المسجلين

- SendAbsentNotification: لا يوجد أي ربط له في AppServiceProvider أو أي مكان آخر (غير مسجل في Event::listen أو EventServiceProvider)
- SendAttendanceNotifications: لا يوجد أي ربط له في AppServiceProvider أو أي مكان آخر (غير مسجل في Event::listen أو EventServiceProvider)

## 4. التوصيات

- حذف أو ربط SendAbsentNotification وSendAttendanceNotifications إذا كان هناك حاجة فعلية.
- توحيد أسلوب ربط الأحداث والمستمعين (Event::listen أو EventServiceProvider) وتوثيق جميع الأحداث والمستمعين في ملف مرجعي.
- مراجعة جميع الأحداث التي يتم إطلاقها بدون مستمعين فعلًا، والتأكد من الحاجة الفعلية لها.

---

### منهجية الفحص:
- تم استخدام grep للبحث عن جميع الأحداث والمستمعين، وفحص أماكن الربط في AppServiceProvider وجميع الأكواد.
- تم توثيق النتائج بدقة مع ذكر أماكن الربط أو غيابها.

---

> هذا الملف مرجعي لأي عملية Refactor أو تنظيف معماري لاحق تخص Events & Listeners.
