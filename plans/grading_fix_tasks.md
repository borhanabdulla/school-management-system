# خطة إصلاح دومين الدرجات - تاسكات تنفيذ

**المرجع:** هذا الملف يترجم فحص دومين الدرجات إلى تاسكات تنفيذية دقيقة.  
**مبدأ التنفيذ:** لا تغيير لسلوك يعمل إلا إذا كان يسبب Crash، خلل منطقي مؤكد، أو ضوضاء آمنة للحذف.

---

## نطاق الإصلاح (وفق فحص MCP)

- **Routes ذات الصلة:**  
  - `grading/gradebooks`  
  - `grading/gradebook/{courseOfferingId}`  
  - `grading/settings`  
  - `grading/subjects`  
  - `control/grading/{sessionId?}`

- **جداول قاعدة البيانات الأساسية:**  
  - `gradebook_months`  
  - `monthly_grades`  
  - `student_marks`  
  - `gradebook_settings`  
  - `grading_templates` / `template_categories`  
  - `subject_grading_configs`  
  - `grade_scales` / `grade_scale_levels`  
  - `term_results` / `annual_results`

---

## GR-0 — إصلاح تعارض المفتاح الفريد في الدرجات الشهرية (Critical)

**الهدف:** منع تكرار `category_key` بسبب اختلاف بين `category` و`category_key` عند إنشاء بيانات اختبار/Seeder.

- [x] **MonthlyGradeFactory**: إذا تم تعيين `category` بدون `category_key` يتم توليد المفتاح تلقائيًا.  
  - الملف: `database/factories/Domains/Academic/Grading/Models/MonthlyGradeFactory.php`

**سبب الإصلاح:** فشل اختبارات `GradingTest` بسبب تكرار المفتاح الفريد مع `category_key` عشوائي.

---

## GR-1 — اتساق توقيع Adapter لإعدادات الدفتر الشهري (Crash Safety)

**الهدف:** منع TypeError عند استخدام `saveGradebookSettings`.

- [x] تعديل `GradingActionsAdapter::saveGradebookSettings` ليقبل `MonthlySettingsData` بدل `array`.  
  - الملف: `app/Domains/Academic/Grading/Adapters/GradingActionsAdapter.php`

---

## GR-1.5 — مصدر طلاب الشعبة (Enrollment-based Students)

**الهدف:** منع إعادة حساب درجات الترم بناءً على الشعبة الحالية بعد ترحيل الطلاب (Historical correctness).

- [x] **FinalizeTermCourseworkAction**: جلب الطلاب عبر `student_enrollments` لسنة/ترم محددين بدل `classSection->students`.  
  - الملف: `app/Domains/Academic/Grading/Actions/FinalizeTermCourseworkAction.php`

- [x] **CalculateTermGradesAction**: نفس الفكرة باستخدام enrollments بدل current placement.  
  - الملف: `app/Domains/Academic/Grading/Actions/CalculateTermGradesAction.php`

---

## GR-2 — حراسة الكتابة للترم/السنة (Policy/Hardening)

**الهدف:** ضمان عدم تعديل إعدادات التقييم بعد إغلاق السنة أو اكتمال الترم.

- [x] إضافة `AcademicWriteGuard` في Actions التالية (إذا تم اعتماد السياسة):  
  - `SaveGradingTemplateAction`  
  - `SaveSubjectGradingConfigAction`  
  - `SaveGradebookSettingsAction`  
  - `SaveGeneralGradingSettingsAction`  
  - `SaveGradeScaleAction`

- [x] **GradingSettingsService**: ضمان وجود Guard قبل حفظ الإعدادات العامة/سلم الدرجات/الدفتر الشهري/القوالب/المواد.  
  - الملف: `app/Domains/Academic/Grading/Services/GradingSettingsService.php`

---

## GR-3 — توحيد صلاحية القالب للترم (Template Applicability Rule)

**الهدف:** إزالة التناقض بين Service وAction حول قبول قالب year-level للترم.

- [x] إنشاء منطق موحد (Service/Policy) ويُستخدم في:  
  - `SaveSubjectGradingConfigAction`  
  - `GradingSettingsService::templateMatchesTerm`  
  - الملف: `app/Domains/Academic/Grading/Services/GradingSettingsService.php`

---

## GR-4 — توحيد مصدر السنة النشطة (Source of Truth)

**الهدف:** الاعتماد على `school()` بدل استعلامات مباشرة للسنة النشطة.

- [x] تعديل قراءة/حفظ إعدادات الدفتر الشهري لاستخدام `school()->activeYearId()`.  
  - الملف: `app/Domains/Academic/Grading/Services/GradingSettingsService.php`

---

## GR-5 — HealthGate عند الحفظ (Feedback Early)

**الهدف:** إظهار مشاكل Missing/Invalid فورًا بعد الحفظ بدل انتظار الإغلاق.

- [x] بعد أي حفظ (template/subject/monthly/scale/mapping/categories) إعادة حساب Health Report وتحديث العدّادات في UI.  
  - الملف: `app/Livewire/Admin/Grading/GradingSettings.php`

> هذا بند سياسة: إذا اعتمادك أن إعدادات الدرجات لا تتغير بعد إغلاق السنة/الترم.

---

## اختبارات مستهدفة

- [x] إعادة تشغيل: `tests/Feature/Domains/Academic/GradingTest.php` (للتحقق من إصلاح الفشل السابق).  
- [ ] تشغيل:  
  - `tests/Feature/Grading/GradingE2ETest.php`  
  - `tests/Feature/Grading/MonthlyGradeSavedListenerTest.php`

---

## مخرجات متوقعة

- اختبارات الدرجات تعمل بدون تعارض فريد.  
- Adapter لا يرمي TypeError عند حفظ إعدادات الدفتر الشهري.  
- (اختياري) منع تعديل إعدادات التقييم بعد إغلاق السنة/الترم وفق السياسة.

---

# تحسين واجهة إعدادات الدرجات (Wizard 2.0 — UX فقط)

**مبدأ التنفيذ:** تحسين تجربة المستخدم + تنظيم الكود (بدون تغيير منطق الحساب أو قواعد الإغلاق).  
**أسلوب التنفيذ:** خطوات صغيرة، كل خطوة قابلة للتحقق، مع الحفاظ على كود نظيف غير متكدس.

## UI-GR-0 — توحيد السياق + شريط أوامر ثابت (أعلى الصفحة)

**الهدف:** منع ضياع السياق وإظهار حالة النظام باستمرار.

- [x] **Context Command Bar** أعلى الصفحة ثابت:  
  - يعرض `السنة/الترم/الصف` + حالة `Closed/Archived/Completed`  
  - شارات حالة الصحة: `Missing / Invalid / Warnings`  
  - زر فتح **Health Drawer** + زر تشغيل الفحص  
  - الملف: `resources/views/livewire/admin/grading/grading-settings.blade.php`
- [x] **كود نظيف:** استخراج الشريط إلى Blade component مستقل.  
  - المقترح: `resources/views/components/grading/context-bar.blade.php`

## UI-GR-1 — هيكل الصفحة (Stepper يسار + Workspace وسط + Health Panel يمين)

**الهدف:** تحويل التبويبات إلى رحلة واضحة + تقليل التشتت.

- [x] **Stepper يسار** مرتب حسب المخاطر:  
  `Templates → Subjects → Monthly → General → Scale`  
  (Review تُضاف في UI-GR-5) مع حالات ✅/⚠️/⛔ وسبب مختصر.
- [x] **Workspace وسط**: محتوى الخطوة الحالية فقط.  
- [x] **Health Panel يمين** (Drawer أو Panel):  
  قائمة المشاكل مرتبة + زر “اذهب للإصلاح”.
- [x] **كود نظيف:** استخراج الـ Stepper و الـ Health Panel إلى components.

## UI-GR-2 — Template Builder (High Risk)

**الهدف:** منع أخطاء الأوزان قبل أن تظهر في Health.

- [x] **Weight Meter** واضح (0–100%) مع تحذير فوري.  
- [x] عرض الفئات كبطاقات بدل قائمة طويلة.  
- [x] إرشادات سياقية قصيرة داخل الخطوة.  
- [x] **كود نظيف:** فصل Tree renderer في component مستقل.

## UI-GR-3 — Subject Configs (High Risk)

**الهدف:** معالجة السبب الأكثر شيوعًا لـ Invalid.

- [x] قائمة مواد مع بحث + فلتر + شارات حالة.  
- [x] مقارنة **Default vs Override** عبر شارات الحالة داخل الجدول.  
- [x] زر “تطبيق قالب افتراضي على جميع المواد” (اختياري ومؤكد).  
- [x] إرشادات قصيرة داخل كل حالة Missing/Invalid.

## UI-GR-4 — Monthly Step (Two-step داخل نفس الشاشة)

**الهدف:** فصل “تعريف البنود” عن “ربط البنود” لتقليل الأخطاء.

- [x] Section 1: تعريف البنود + قواعد المواظبة.  
- [x] Section 2: ربط البنود بفئات القالب + ملخص تقدّم.  
- [x] فتح المابينغ في Drawer جانبي بدل modal كبير.

## UI-GR-5 — Review & Publish (نهائي)

**الهدف:** إنهاء واضح قبل الإغلاق.

- [x] عرض Health report النهائي + أسباب المنع إن وجدت.  
- [ ] زر Publish/Lock إن كان موجودًا، مع رسالة واضحة.  
- [ ] عرض آخر حفظ + من قام بالحفظ (إن كانت البيانات متاحة).

## UI-GR-6 — Guided Hints (نظام إرشادات احترافي)

**الهدف:** توجيه المستخدم بدون إزعاج.

- [x] **Intro hint** أعلى كل خطوة (سطرين فقط).  
- [x] **Inline hint** عند الحقول الحساسة (weights/mapping/cap).  
- [x] **Blocking hint** يظهر فقط عند وجود مانع.  
- [x] توحيد النمط عبر component واحد:  
  `resources/views/components/grading/help-hint.blade.php`

## UI-GR-7 — تنظيف الكود (Code Cleanliness)

**الهدف:** منع التكدّس في Blade و Livewire.

- [x] تقسيم الصفحة إلى partials/components (Context, Stepper, Panels, Tables).  
- [x] تقليل تكرار الـ classes عبر Components.  
- [x] توحيد أسماء المتغيرات وتوثيقها بـ docblocks بسيطة.

## تحقق سريع بعد كل خطوة

- [ ] لا توجد أخطاء عرض أو انهيار Livewire.  
- [ ] الخطوات تظهر بترتيب صحيح.  
- [ ] Health counter يظهر دائمًا في السياق.  
- [ ] المحتوى قابل للاستخدام على الشاشات الصغيرة.

---

# سياسة القوالب والخطوات (مطلوب حسب طلبك)

**الهدف:** جعل “قالب الترم الأول” هو الأساس لبقية الترمات بدون كسر منطق النظام الحالي، مع مسار خطوات صارم (لا انتقال قبل الإكمال).

## GR-P-0 — توضيح كيف يتعامل النظام حاليًا (مرجعي)

- [x] توثيق السياسة التشغيلية بوضوح:  
  - **ثوابت (Core):**  
    - `Max Score = 100`  
    - `Term Weights = 50/50` (لو الترمين فقط)  
    - `Passing Rule` ثابت  
    *(عرض/إرشاد فقط في UI، بدون تغيير منطق الحساب)*  
  - **عام/سنة (Year-level):** `Grade Scale` + `General Settings` + سياسة الدفتر الشهري العامة.  
  - **ترم (Term-level):** `Template` + `Subject Configs` + `Monthly Mapping`.  
  - **Fallback:** لا نعتمد عليه تشغيليًا، الاعتماد على النسخ الاختياري بدلًا منه.

## GR-P-1 — القالب كأساس لبقية الترمات (بدون حذف المنطق الحالي)

**الهدف:** عند إنشاء قالب في الترم الأول، يمكن تطبيق الأساس على بقية الترمات بشكل آمن.

- [x] **خيار واضح في الواجهة:**  
  Checkbox في خطوة القوالب:  
  "طبّق القالب على جميع ترمات السنة"  
  (افتراضيًا غير مفعّل حتى لا نغيّر سلوك قائم).
- [x] **تنفيذ النسخ الآمن:**  
  عند تفعيل الخيار، يتم إنشاء نسخة لكل ترم.  
  *لا يتم حذف أي قالب موجود مسبقًا.*
- [x] **ربط المواد تلقائيًا (اختياري):**  
  خيار منفصل:  
  "اربط القالب بكل المواد"  
  يتم تطبيقه بدون استبدال الموجود.

## GR-P-2 — تثبيت الأوزان الأساسية (50/50 للترمين)

- [ ] إبقاء المنطق الحالي + **إظهار شرح واضح**:  
  إذا كان عدد الترمات = 2 → الافتراضي 50/50  
  مع إمكانية التعديل اليدوي.
- [ ] شرح واضح في الواجهة أن الأوزان تؤثر على النتيجة النهائية.

## GR-P-3 — المابينغ: لماذا يظهر؟ وما فائدته؟

- [ ] تحديث نصوص الإرشادات:  
  يشرح أن المابينغ هو الربط بين بنود الدفتر الشهري وفئات القالب،  
  وأن بدونه تظهر Missing/Invalid.

## GR-P-4 — خطوات صارمة مثل صفحة تسجيل الطالب

**الهدف:** لا انتقال لخطوة لاحقة إلا بعد إكمال السابقة.

- [x] **تعريف شروط الإكمال لكل خطوة** (Template/Subjects/Monthly/General/Scale/Review).  
- [x] **تعطيل الانتقال** للخطوات التالية عند عدم تحقق الشرط.  
- [x] **رسالة توجيه** تظهر توضح “لماذا لا يمكنك الانتقال؟”.

## GR-P-5 — تحسين الاستقرار والأداء

- [ ] تأخير تحميل تقارير الصحة الثقيلة (lazy).  
- [ ] منع إعادة تحميل البيانات غير الضرورية عند تغيير تبويب فقط.  
- [ ] إبقاء الأداء ثابتًا مع كثرة المواد.
