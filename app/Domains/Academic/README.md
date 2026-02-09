# 🎓 Academic Domain

هذا هو "العمود الفقري" للنظام بأكمله. يحتوي على:

## 📁 الهيكل

```
Academic/
├── Models/       # الموديلات: AcademicYear, Term, Grade, etc.
├── Services/     # الخدمات: AcademicYearService, StructureService
├── Actions/      # العمليات الثقيلة: ActivateYearAction, etc.
├── Data/         # DTOs للنقل الآمن للبيانات
├── Events/       # أحداث Domain: YearActivated, TermCompleted
└── Enums/        # التعدادات: AcademicYearStatus, TermStatus
```

## 🔗 العلاقات

```
AcademicYear (1) ──→ (N) Terms
AcademicYear (1) ──→ (N) ClassSections
EducationalStage (1) ──→ (N) Grades
Grade (1) ──→ (N) ClassSections
ClassSection (1) ──→ (N) Students
Subject (1) ──→ (N) CourseOfferings
```

## 📋 قواعد العمل

1. السنة الدراسية يجب أن تحتوي على فصلين على الأقل قبل التفعيل
2. لا يمكن حذف سنة نشطة أو بها طلاب
3. الفصل يجب أن ينتهي قبل الانتقال للفصل التالي
4. لا يمكن تفعيل أكثر من سنة دراسية في نفس الوقت
