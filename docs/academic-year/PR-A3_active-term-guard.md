# PR-A3 — Active Term Guard (One Active Term Per Year)

**Instruction Pack (Environment Execution Guide)**

**Status:** Planned  
**Priority:** High  
**Depends on:** PR-A1 (DB-authoritative transitions), PR-A2 (DB guard for active year)

---

## 0) Invariant (غير قابل للنقاش)

> داخل أي سنة أكاديمية: **يسمح بترم واحد فقط بحالة `active`.**

---

## A3-0) Preflight قبل أي تعديل

### A3-0.1 حصر الاستخدامات داخل transitions فقط

نفّذ:

```bash
grep -R "ActivateTermAction" -n app/Domains/Academic | cat
grep -R "ReopenTermAction" -n app/Domains/Academic | cat
```

وتأكد أن الملفات المستهدفة هي:

* `app/Domains/Academic/Term/Actions/ActivateTermAction.php`
* `app/Domains/Academic/Term/Actions/ReopenTermAction.php`

إذا ظهر أي Transition إضافي يغيّر status للترم → **سجّله كملاحظة PR لاحق** ولا توسّع نطاق PR-A3.

---

## A3-1) Implementation — ActivateTermAction (Self-healing)

### الهدف

بعد اعتماد PR-A1 (DB-authoritative active year + lock)، نخلي التفعيل “يعالج الفساد” لو كان موجود (أكثر من active term).

### داخل `DB::transaction(...)` (إلزامي)

1) **اجلب السنة النشطة من DB + lock**

* نفس PR-A1:
  * `AcademicYear::where(status=Active)->lockForUpdate()->first()`
* إذا null → ارْمِ الاستثناء الموجود عندك (بدون اختراع جديد)

2) **Lock الترم الهدف**

* اجلب الترم المطلوب بـ `lockForUpdate()`

3) **تحقق الترم تابع للسنة النشطة**

* إذا لا → exception “year not active” (الموجود عندك)

4) **Lock كل الترمات النشطة لنفس السنة (get وليس first)**

* Query على terms حيث:
  * `academic_year_id = activeYearId`
  * `status = active`
  * `lockForUpdate()`
  * `get()`

5) **أغلق أي ترم نشط غير الهدف**

* لكل term نشط:
  * إذا `id != targetTermId` → غيّر إلى `completed` (أو الحالة المعتمدة عندك)

6) **فعّل الترم الهدف**

* غيّر status إلى `active`

7) **Keep `DB::afterCommit(...)`**

* Invalidations تبقى بعد commit فقط

**قاعدة ذهبية:**
لا تستخدم `school()->activeYearId()` ولا `school()->activeTermId()` داخل transaction.

---

## A3-2) Implementation — ReopenTermAction (Refuse if another active exists)

### القرار المعماري (ثابت)

Reopen **يرفض** إذا يوجد ترم آخر نشط في نفس السنة.

### داخل `DB::transaction(...)` (إلزامي)

1) اجلب السنة النشطة من DB + lock (مثل PR-A1)

2) Lock الترم الهدف (المعاد فتحه)

3) تحقق أنه تابع للسنة النشطة

4) **افحص هل يوجد ترم نشط حاليًا لنفس السنة**

* Query: `where academic_year_id = activeYearId AND status = active`
* `lockForUpdate()`
* إذا وجد **أي ترم نشط**:
  * ارْمِ **Business rule exception** واضحة (استخدم نوع الاستثناء الموجود عندك للـ business rules إن وجد، وإلا RuntimeException برسالة واضحة ومحددة — بدون inventing domain جديد)

5) أكمل قواعد reopen الحالية كما هي (لا تغيّر شروط reopen)

6) Keep afterCommit invalidation

---

## A3-3) DB Guard (Optional but Recommended) — SQLite

إذا PR-A2 فعّلنا فيه partial unique index للسنة، نقدر نضيف للترم أيضًا:

```sql
CREATE UNIQUE INDEX terms_one_active_per_year
ON terms(academic_year_id)
WHERE status = 'active';
```

و down:

```sql
DROP INDEX IF EXISTS terms_one_active_per_year;
```

> Laravel schema builder ما يدعم partial index → لازم `DB::statement()`.

---

## A3-4) Gates (Manual أولًا)

### Gate A

* Activate Term1 ثم Activate Term2 في نفس السنة
* Expected:
  * Term1 → completed
  * Term2 → active
  * count(active terms where year_id=X) == 1

### Gate B

* مع وجود ترم نشط بالفعل
* حاول Reopen لترم completed
* Expected:
  * العملية **ترفض**
  * exception واضح
  * لا تغييرات في statuses

---

## A3-4.1) Test Results (Executed)

Commands run:

```bash
php artisan test --filter=TermActivationTest
php artisan test --filter=ReopenTermActionTest
```

Results:
- TermActivationTest: 4 passed (9 assertions)
- ReopenTermActionTest: 2 passed (2 assertions)
- PHPUnit warned about deprecated doc-comment metadata (no failures).

DB Guard Gate (SQLite):
- Second active term insert failed with:
  - `SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: terms.academic_year_id`
- Active term count for the year remained `1`.

---

## A3-5) Definition of Done

* ActivateTermAction يصير self-healing (يغلق أي active غير الهدف)
* ReopenTermAction يرفض إذا يوجد active term
* (اختياري) DB guard موجود للـ SQLite
* كل الانتقالات تستخدم DB-authoritative reads + lockForUpdate داخل transaction
* afterCommit invalidation كما هو

---

ملاحظة دقيقة:
إذا عندكم في الـ DB “أكثر من ترم active” حاليًا، **ActivateTermAction سيصلّحه تلقائيًا** (وهذا ممتاز)، لكن **DB guard** لو أضفته قبل تنظيف البيانات ممكن يفشل إنشاء الـ index.  
ترتيب التنفيذ العملي:

1) نفّذ Action guard أولًا + شغّل Gate A مرة (لتنظيف الوضع)
2) ثم أضف DB guard
