# Codex Execution Plan — School Management System

> **الغرض:** خطة تشغيل دقيقة يمكن تمريرها إلى Codex للعمل على المشروع بدون افتراضات، مع البدء بفهم المعمارية أولًا، ثم استخراج الأخطاء المنطقية/المعمارية بالأدلة، ثم تنفيذ إصلاحات تدريجية قابلة للمراجعة من Senior.

---

## 0) Working Contract (قواعد العمل الإلزامية)

1. **No assumptions**: أي استنتاج يجب أن يكون مدعومًا بمرجع واضح من:
   - ملفات الكود (path + lines)
   - قيود قاعدة البيانات / migrations
   - نتائج الاختبارات
2. **Architecture first**: ممنوع البدء بأي Refactor قبل اكتمال فهم المعمارية الحالية كما هي.
3. **Action-first writes**: أي مسار كتابة يجب أن يمر عبر Action/Use Case واضح.
4. **Guarded business rules**: أي Rule حساس (year/term/closure/ledger) يجب أن يكون في Domain Guard/Service وليس في UI.
5. **Behavior preservation**: أي تغيير سلوكي يجب توثيقه بـ Before/After مدعوم باختبار.

---

## 1) Phase A — Architecture Understanding (إجباري قبل أي تعديل)

### A1. Architectural Inventory
استخرج صورة المشروع الحالية كما هي (وليس الشكل المثالي):

- UI Layer: Livewire / Controllers / Requests / Views
- Application Layer: Actions / Jobs / Commands / Orchestrators
- Domain Layer: Rules / Guards / Policies / Domain Services
- Infrastructure: Eloquent Models / DB integrations / external services

**Deliverable A1:**
- `Repo Map`
- قائمة الدومينات وحدود كل دومين
- أين يحدث Read/Write فعليًا

### A2. Correctness Rules (Project-specific)
عرّف قواعد “الصحة” التي يلتزم بها المشروع فعليًا (من الكود):

- من هو مصدر الحقيقة لكل نطاق (Academic, Attendance, Grading, Finance, Payroll)
- كيف يتم فرض year/term awareness
- كيف يتم منع الكتابة بعد الإغلاق
- كيف تُدار side effects (events/jobs/notifications)

**Deliverable A2:**
- `Correctness Rules` مع أدلة (files/lines)

### A3. Anti-Patterns Matrix
ابنِ قائمة بما يُعد خطأ في هذا المشروع تحديدًا، مع أمثلة حقيقية إن وجدت:

- كتابة مباشرة من UI
- bypass لـ Actions/Guards
- استعلامات حساسة بدون `academic_year_id` / `term_id`
- استخدام current_* في بيانات تاريخية
- إحصائيات مالية لا تعتمد على ledger source-of-truth

**Deliverable A3:**
- `Anti-Patterns List` مع severity لكل بند

> **Gate:** لا تنتقل لـ Phase 0 قبل اكتمال A1+A2+A3.

---

## 2) Phase 0 — Proof Pack (بدون تعديل)

### 0.1 Write Paths Audit
حصر كل مسارات الكتابة:
- `create/update/delete/save/upsert/updateOrCreate/DB::table`

تصنيف كل مسار:
- المصدر (Livewire/Controller/Job/Action)
- هل يمر عبر Action؟
- هل يمر عبر Guard/Policy؟
- هل داخل Transaction؟

**Deliverable:** `Write-Paths Report`

### 0.2 Term/Year Leakage Scan
افحص الدومينات الحساسة لأي تسريب year/term:
- Academic Year/Term
- Attendance
- Grading/Results
- Timetable
- Finance/Payroll

**Deliverable:** `Leakage Findings` (path + line + risk)

### 0.3 DB Invariants Review
تحليل قيود DB:
- unique/index/fk/checks
- active year/term uniqueness
- payroll period overlaps
- term-aware pivots

**Deliverable:** `DB Invariants List`

### 0.4 Test Baseline
تشغيل الاختبارات وتوثيق:
- pass/fail counts
- top failures + السبب (Auth/DB/Logic/Factories)

**Deliverable:** `Test Baseline Report`

---

## 3) Phase 1 — AcademicYear/Term + Closure (Highest Priority)

1. تحديد source-of-truth للسنة/الترم بالأدلة.
2. قفل بوابة الكتابة: نقل أي write مباشر إلى Actions.
3. فرض year/term explicit في Actions الحساسة.
4. Closure Guard مركزي:
   - يمنع الكتابة بعد الإغلاق
   - يسمح باستثناءات admin amendment مع audit
5. إضافة اختبارات Feature للحالات:
   - منع الكتابة بعد closure
   - السماح بالاستثناءات المصرح بها
   - readiness/read-only reports

**Deliverable:** خطة PR1 + الملفات + الاختبارات + المخاطر.

---

## 4) Phase 2 — Attendance + Calendar Authority

- تثبيت calendar كمرجع نهائي للأيام الدراسية
- منع الحضور في weekends/holidays إلا بإذن واضح
- فرض term-awareness في كل attendance queries
- اختبارات: holiday/weekend/exception flows

**Deliverable:** خطة PR2 + regression tests.

---

## 5) Phase 3 — Grading & Results Integrity

- إثبات تدفق: raw marks → aggregation → final results
- منع الاعتماد على `current_grade_id/current_class_section_id` تاريخيًا
- term-aware aggregation كامل
- اختبارات multi-term + promotion + historical correctness

**Deliverable:** خطة PR3 + safety tests.

---

## 6) Phase 4 — Finance & Payroll Integrity

- تثبيت ledger source-of-truth (`invoices/payments`)
- year/term explicit للإحصائيات الحساسة
- منع overlapping payroll periods
- اختبارات حالات التزامن/التكرار الأساسية

**Deliverable:** خطة PR4 + integrity tests.

---

## 7) Phase 5 — Timetable / HR / Notifications

- term-aware mappings في assignment/substitution/timetable
- safe delete rules (no orphan references)
- notifications must originate from domain events/actions (not UI)
- اختبارات إطلاق الإشعار الصحيح من الحدث الصحيح

**Deliverable:** خطة PR5 + event-flow tests.

---

## 8) PR Strategy (طريقة التسليم)

- PR صغير لكل Phase (أو sub-phase)
- كل PR يحتوي:
  1. **Motivation**
  2. **Scope** (files changed)
  3. **Behavior impact** (before/after)
  4. **Tests added/updated**
  5. **Risk + rollback notes**

---

## 9) Senior Review Checklist (Ready-to-Use)

- [ ] هل المعمارية الحالية موثقة قبل الإصلاح؟
- [ ] هل كل write path يمر عبر Action + Guard؟
- [ ] هل year/term constraints واضحة في كل query حساس؟
- [ ] هل DB constraints تدعم business invariants؟
- [ ] هل السلوك الحرج مغطى باختبارات Feature/Integration؟
- [ ] هل side effects (notifications/jobs) event-driven من domain؟
- [ ] هل يوجد Before/After واضح لكل تغيير سلوكي؟

---

## 10) Ready Prompt for Codex (Copy/Paste)

```text
ابدأ بفهم المعمارية الحالية للمشروع كما هي في الريبو (UI/Application/Domain/Infrastructure) ثم استخرج Correctness Rules من الكود نفسه، ثم Anti-Patterns بالأدلة، ثم نفذ Phase 0 Proof Pack بدون أي تعديل. بعد اكتمال الأدلة انتقل تدريجيًا إلى Phase 1..5.

قيود التنفيذ:
- ممنوع أي افتراض غير مدعوم بملف/سطر/اختبار.
- ممنوع تعديل قبل اكتمال A1+A2+A3+Proof Pack.
- أي write path يجب أن يمر عبر Action + Guard.
- أي تغيير سلوكي يجب إثباته باختبار Before/After.

المخرجات المطلوبة:
Repo Map, Correctness Rules, Anti-Patterns List, Write-Paths Report,
Leakage Findings, DB Invariants List, Test Baseline Report,
ثم خطة PRs مرحلية مع المخاطر والاختبارات.
```

