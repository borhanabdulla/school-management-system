# PR-A2 — DB Guard: Single Active Academic Year

**Instruction Pack (Environment Execution Guide)**

**Status:** Planned  
**Priority:** High  
**Depends on:** PR-A1 (Verified & Accepted)

---

## 0) الهدف (غير قابل للنقاش)

فرض القاعدة التالية على مستوى **قاعدة البيانات**:

> **يُسمح بوجود سنة دراسية واحدة فقط بحالة `active` في أي لحظة.**

هذا القيد يجب أن:

* يعمل حتى لو حصل Bug في الكود مستقبلًا
* يعمل حتى لو تم تحديث يدوي في DB
* لا يعتمد على الكاش أو منطق التطبيق

PR-A2 **لا يغيّر منطق التطبيق**، بل يضيف **حارس DB** فقط.

---

## 1) Preconditions — فحوصات إلزامية قبل أي تنفيذ

❗ لا يُسمح بتنفيذ أي Migration قبل اجتياز هذه الفحوصات وتوثيقها.

### 1.1 تحديد نوع قاعدة البيانات (Driver)

نفّذ:

```php
config('database.default')
DB::connection()->getDriverName()
```

**النتيجة المتوقعة على هذه البيئة:**
`sqlite`

> إذا لم تكن SQLite أو PostgreSQL → **توقف فورًا** وانتقل إلى خيار MySQL (غير مطلوب حاليًا).

---

### 1.2 فحص سلامة البيانات الحالية (Preflight)

نفّذ استعلام DB مباشر (Tinker أو Query):

```sql
SELECT COUNT(*) 
FROM academic_years 
WHERE status = 'active';
```

**الشرط الإلزامي:**

* النتيجة يجب أن تكون `0` أو `1`

#### إذا كانت النتيجة > 1:

* **توقف التنفيذ فورًا**
* لا تُنشئ Migration
* اكتب **Stop Report** بعنوان:
  `PR-A2 Stop Report — Multiple Active Academic Years Detected`
* لا يُستأنف PR-A2 قبل تنظيف البيانات يدويًا وتوثيق ذلك

---

## 2) Strategy المعتمدة لهذه البيئة

### ✔ Strategy المعتمدة: **SQLite Partial Unique Index**

بما أن:

* قاعدة البيانات = SQLite
* عمود `status` مخزّن كنص (`string`)
* القيمة الفعلية للحالة النشطة هي `'active'`

فالحل المعتمد هو:

> **Partial Unique Index**
> يمنع وجود أكثر من صف واحد حيث `status = 'active'`

---

## 3) التنفيذ — Migration (DB-Level Only)

### 3.1 إنشاء Migration جديدة

اسم الميجريشن (مثال واضح):

```bash
php artisan make:migration add_unique_active_year_constraint_to_academic_years
```

---

### 3.2 محتوى الـ Migration (إلزامي)

> **مهم:** Laravel Schema Builder لا يدعم partial indexes  
> لذلك يجب استخدام `DB::statement()` مباشرة.

```php
public function up(): void
{
    DB::statement("
        CREATE UNIQUE INDEX academic_years_one_active_year
        ON academic_years (status)
        WHERE status = 'active'
    ");
}

public function down(): void
{
    DB::statement("
        DROP INDEX IF EXISTS academic_years_one_active_year
    ");
}
```

❗ لا تضف أي منطق آخر  
❗ لا تلمس كود التطبيق  
❗ لا تغيّر Actions أو Models في هذا PR

---

## 4) Gates — اختبارات إلزامية بعد التنفيذ

### Gate A — إثبات أن DB تمنع سنتين Active

#### الخطوات:

1. أنشئ سنتين بحالة `pending`
2. حدّث الأولى إلى `active` → يجب أن تنجح
3. حاول تحديث الثانية إلى `active`

#### النتيجة المتوقعة:

* العملية الثانية تفشل
* يظهر **Unique Constraint Violation**
* لا تتغير حالة السنة الأولى

#### التوثيق المطلوب:

* الاستعلامات
* رسالة الخطأ
* حالة الجدول بعد الفشل

---

### Gate B — عدم كسر PR-A1

أعد تنفيذ:

* Gate A / Gate B الخاصة بـ PR-A1
* تفعيل سنة عبر Action الرسمي

#### النتيجة المتوقعة:

* الانتقالات تعمل طبيعيًا
* لا اعتماد على الكاش
* لا أخطاء جديدة

---

## 5) ما لا يُسمح به في PR-A2

❌ لا إضافة منطق Application  
❌ لا تعديل Actions  
❌ لا Runtime Singleton Table  
❌ لا معالجة MySQL  
❌ لا افتراضات عن Concurrency  
❌ لا “تحسينات جانبية”

هذا PR = **DB Guard فقط**

---

## 6) Definition of Done (توقيع القبول)

يُعتبر PR-A2 مكتملًا فقط إذا:

* ✔ تم تأكيد Driver = SQLite
* ✔ Preflight check موثّق وClean
* ✔ Partial Unique Index موجود في DB
* ✔ Gate A فشل عند محاولة سنتين Active
* ✔ PR-A1 Gates ما زالت تمر
* ✔ لا تغييرات خارج نطاق DB

---

## 7) النتيجة المعمارية بعد PR-A2

بعد هذا PR:

* Application Guard (PR-A1) ✅
* Database Guard (PR-A2) ✅

أي خرق مستقبلي لقاعدة “سنة واحدة نشطة” يصبح **مستحيلًا تقنيًا**، لا مجرد “غير متوقع”.

