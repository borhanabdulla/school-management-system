# PR-A1: Active Year Transitions Are DB-Authoritative

Status: Planned  
Priority: Critical  
Depends On: PR-A0 (Verified Findings)

## 0) Purpose

Eliminate stale-cache risk during state transitions by forcing all active year / active term transitions to read the current state from the database with locks.

Invariant enforced:
- Active year and active term transitions are DB-authoritative and lock the rows they mutate.

---

# Read/Write Rule (No Grey Area)

**Read Path (allowed):**
- `school()->activeYearId()` and `school()->activeTermId()` are **read-only** helpers (cached, TTL 24h).

**Write/Transition Path (forbidden):**
- Any state change or sensitive write must **not** read from cached context.
- Use DB-authoritative reads inside `DB::transaction(...)` with `lockForUpdate()`.

**One-line guard for all transitions:**
> Do not use cached academic context inside transitions. Use DB authoritative reads with locks inside transactions.

---

# PR-A1 Execution Pack (Instruction Pack للوكيل)

## A1-0) Preflight قبل أي تعديل

هدفنا تثبيت نطاق التغيير (Transitions فقط) وتأكيد أن الاعتماد على الكاش موجود كما وثّقناه.

### A1-0.1 اجمع مواقع الاستخدام داخل transitions

```bash
grep -R "school()->activeYearId()" -n app/Domains/Academic | cat
grep -R "school()->activeTermId()" -n app/Domains/Academic | cat
```

**المتوقع:** تظهر الملفات الثلاثة:

- `ActivateAcademicYearAction`
- `ActivateTermAction`
- `ReopenTermAction`

إذا ظهر غيرها داخل Actions انتقالية حساسة، سجّلها كملاحظة لـ PR لاحق بدون توسيع نطاق PR-A1.

---

## A1-0.2) Impact Scan (قبل التنفيذ)

هدفنا التأكد أن لا توجد مسارات كتابة أخرى تعتمد على الكاش داخل transaction.

### A1-0.2.1 حصر الاستخدامات

```bash
# كل أماكن الاعتماد على السياق (قراءة أو كتابة)
grep -R "school()->activeYearId()" -n app | cat
grep -R "school()->activeTermId()" -n app | cat

# أي helpers أخرى (إن وجدت)
grep -R "active_year_id()" -n app | cat
grep -R "active_term_id()" -n app | cat
```

### A1-0.2.2 تصنيف النتائج

صنّف كل نتيجة إلى:

1) **Read-only (عرض فقط)**  
✅ لا تأثير

2) **Write but not transition**  
⚠️ يحتاج مراجعة لاحقة (لا توسّع PR-A1)

3) **Transition**  
✅ هذا PR-A1 يعالجه

### A1-0.2.3 Gate C (Impact Safety)

لا نعتمد PR-A1 إلا إذا:

> لا يوجد أي مسار كتابة (غير transitions الثلاثة) يعتمد على `school()->activeYearId()` أو `school()->activeTermId()` داخل `DB::transaction(...)`.

أي نتيجة مخالفة تُسجّل كـ PR لاحق (PR-A1.1/PR-A4) دون تغيير نطاق PR-A1.

---

## A1-1) MCP Gate (مطلوب قبل الكود)

اقرأ الملف:

- `docs/academic-year/PR-A1_mcp_gate.md`

**الخلاصة المطلوبة للوكيل:**

`lockForUpdate()` يجب أن يكون داخل `DB::transaction()` وعلى نفس الاتصال، وإلا لا يضمن الحجز.

---

## A1-2) Implementation — ActivateAcademicYearAction

### A1-2.1 الملف

- `app/Domains/Academic/AcademicYear/Actions/ActivateAcademicYearAction.php`

### A1-2.2 الهدف

- منع أي اعتماد على `school()->activeYearId()` داخل transaction.
- الانتقال يصبح DB-authoritative.

### A1-2.3 التعديل المطلوب داخل `DB::transaction(...)`

1) **Lock السنة الهدف**  
اجلب السنة المراد تفعيلها بـ `lockForUpdate()` (حسب توقيع الأكشن).

2) **أبقِ Guards الجاهزية كما هي**  
لا تغيّر شروط الجاهزية الحالية.

3) **اجلب كل السنوات Active من DB مع lock**  
استخدم `get()` وليس `first()`.

4) **أغلق كل سنة Active بعد validation**  
لكل سنة:
- مرّر على `AcademicYearClosureValidator`
- ثم غيّر الحالة إلى `Closed`.

5) **فعّل السنة الهدف**  
غيّر الحالة إلى `Active`.

6) **Keep `DB::afterCommit(...)` كما هو**  
Invalidate cache بعد commit فقط.

---

## A1-3) Implementation — ActivateTermAction

### A1-3.1 الملف

- `app/Domains/Academic/Term/Actions/ActivateTermAction.php`

### A1-3.2 الهدف

- إزالة `school()->activeYearId()` و `school()->activeTermId()` من داخل transaction.
- الترم النشط يتبع الحقيقة في DB.

### A1-3.3 التعديل المطلوب داخل `DB::transaction(...)`

1) **اجلب السنة النشطة من DB مع lock**  
`AcademicYear::where(status=Active)->lockForUpdate()->first()`  
إن لم توجد، ارْمِ الاستثناء الموجود عندك (بدون اختراع جديد).

2) **Lock الترم الهدف**  
اجلب الترم بـ `lockForUpdate()`.

3) **تحقق أن الترم تابع للسنة النشطة**  
إذا لا، ارْمِ استثناء “year not active”.

4) **اجلب كل الترمات Active لنفس السنة مع lock**  
استخدم `get()` وليس `first()`.

5) **أغلق أي ترم Active آخر**  
غيّر حالته إلى `Completed` (أو المعتمد عندك).

6) **فعّل الترم الهدف**  
غيّر حالته إلى `Active`.

7) **Keep `DB::afterCommit(...)`**  
Invalidate cache بعد commit.

---

## A1-4) Implementation — ReopenTermAction

### A1-4.1 الملف

- `app/Domains/Academic/Term/Actions/ReopenTermAction.php`

### A1-4.2 المطلوب داخل `DB::transaction(...)`

1) **اجلب السنة النشطة من DB + lock**  
2) **Lock الترم الهدف**  
3) **تحقق الترم تابع للسنة النشطة**  
4) **أبقِ قواعد reopen الحالية كما هي**  
5) **Keep afterCommit invalidation**

---

## A1-5) Gate Scenarios (تشغيل يدوي كمرحلة أولى)

### Gate A — Stale cache لا يكسر تفعيل السنة

**Setup:**
- DB: Year B = active
- DB: Year C = pending + ready
- Cache: active year points to Year A

**Action:** Activate Year C

**Expected:**
- B → closed
- C → active
- `count(active years) == 1`

### Gate B — تفعيل ترم يتبع DB لا الكاش

**Setup:**
- DB: Year B active
- Term A belongs to Year A
- Cache: active year points to Year A (stale)

**Action:** Activate Term A

**Expected:**
- Exception “year not active”
- لا تغييرات في statuses

---

## A1-5.1) Test Results (Automated)

Commands run:

```bash
php artisan test --filter=AcademicYearTest
php artisan test --filter=TermActivationTest
```

Results:
- AcademicYearTest: 11 passed (18 assertions)
- TermActivationTest: 4 passed (9 assertions)
- PHPUnit warned about deprecated doc-comment metadata (no failures).

Manual Gate A/B:
- Covered by automated tests:
  - `Tests\Feature\AcademicYearTest::it_activates_pending_year_even_when_active_year_cache_is_stale`
  - `Tests\Feature\TermActivationTest::it_rejects_term_activation_when_active_year_cache_is_stale`

---

## A1-6) Definition of Done (توقيع قبول PR-A1)

الوكيل لا يدمج PR-A1 إلا إذا:

1) لا يوجد أي `school()->activeYearId()` داخل:
   - ActivateAcademicYearAction
   - ActivateTermAction
   - ReopenTermAction
2) لا يوجد أي `school()->activeTermId()` داخل ActivateTermAction
3) جميع transitions تقرأ الحالة من DB مع `lockForUpdate()` داخل `DB::transaction()`
4) `DB::afterCommit` invalidation بقي كما هو
5) Gate A و Gate B تم تنفيذها ونتائجها موثقة (حتى لو manual)

---

## ملاحظة منهجية (ضمن PR-A1)

في ActivateTermAction، جلب السنة النشطة بـ `first()` مقبول مبدئيًا في PR-A1، لكن إذا كان هناك فساد سابق بوجود أكثر من سنة نشطة، قد يلتقط صفًا عشوائيًا.  
هذا يُحل جذريًا في PR-A2 عبر DB guard يمنع وجود أكثر من سنة نشطة أساسًا.
