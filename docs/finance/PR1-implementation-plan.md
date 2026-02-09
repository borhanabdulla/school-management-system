# PR1 — Payer Snapshot + Payment Eligibility + Guardian Statement

## هدف PR1

تثبيت الدافع (الولي المالي) تاريخياً على الفاتورة + قواعد أهلية الدفع + كشف حساب ولي الأمر.

---

## ✅ Definition of Done

1. كل فاتورة لها `payer_guardian_id` ثابت وقت الإنشاء
2. تسجيل الدفع يتأكد أن الدافع مسموح له على الفاتورة
3. لو تغيّر `is_financial_sponsor` لاحقاً، الفواتير القديمة لا تتغير
4. تقرير كشف حساب ولي الأمر يعطي أرقام صحيحة
5. منع أكثر من sponsor واحد للطالب

---

## 🔍 الوضع الحالي (Facts from PR0)

| الموضوع | الحالة |
|---------|--------|
| جدول `payments` | ✅ موجود مع `guardian_id` |
| `RecordPaymentAction` | ✅ يتحقق من `is_financial_sponsor` |
| `payer_guardian_id` على Invoice | ❌ غير موجود |
| تثبيت الدافع تاريخياً | ❌ غير موجود |
| منع أكثر من sponsor | ❌ `validateGuardians()` يسمح بأكثر من واحد |

---

## 📋 الخطة التنفيذية

### Migration

---

#### [NEW] `2026_02_02_000010_add_payer_to_invoices_table.php`

إضافة أعمدة تثبيت الدافع:

| العمود | النوع | الملاحظات |
|--------|-------|-----------|
| `payer_guardian_id` | FK nullable | → guardians (الدافع المثبت) |
| `payer_set_at` | datetime nullable | وقت تثبيت الدافع |
| `payer_set_by` | FK nullable | → users (من ثبّت الدافع) |

```php
$table->foreignId('payer_guardian_id')->nullable()->constrained('guardians');
$table->datetime('payer_set_at')->nullable();
$table->foreignId('payer_set_by')->nullable()->constrained('users');
$table->index(['payer_guardian_id', 'academic_year_id']);
```

---

### Services

---

#### [NEW] `PayerResolverService.php`

تحديد الدافع من الطالب:

```php
class PayerResolverService
{
    public function resolve(Student $student): Guardian
    {
        $sponsors = $student->guardians()
            ->wherePivot('is_financial_sponsor', true)
            ->get();
        
        if ($sponsors->isEmpty()) {
            throw new NoFinancialSponsorFoundException();
        }
        
        if ($sponsors->count() > 1) {
            throw new MultipleFinancialSponsorsException();
        }
        
        return $sponsors->first();
    }
}
```

---

#### [NEW] `GuardianStatementService.php`

كشف حساب ولي الأمر:

```php
class GuardianStatementService
{
    public function getStatement(Guardian $guardian, ?int $academicYearId = null): array
    {
        return [
            'total_invoiced' => // sum invoices where payer_guardian_id = X
            'total_paid' => // sum payments where guardian_id = X
            'balance' => // المفوتر - المدفوع
            'invoices' => // قائمة الفواتير
            'payments' => // قائمة الدفعات
        ];
    }
}
```

---

### Models

---

#### [MODIFY] [Invoice.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Models/Invoice.php)

إضافة:
- `payer_guardian_id`, `payer_set_at`, `payer_set_by` → fillable
- `payerGuardian()` → belongsTo Guardian
- `payerSetter()` → belongsTo User

---

### Actions

---

#### [MODIFY] [CreateInvoiceAction.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Actions/CreateInvoiceAction.php)

قبل `Invoice::create()`:
1. جلب الطالب
2. استدعاء `PayerResolverService->resolve()`
3. تخزين `payer_guardian_id`, `payer_set_at`, `payer_set_by`

---

#### [MODIFY] [RecordPaymentAction.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Actions/RecordPaymentAction.php)

قواعد أهلية الدفع:

**إذا `payer_guardian_id` موجود:**
- `payment.guardian_id` must equal `invoice.payer_guardian_id`
- وإلا: throw `InvoicePayerMismatchException`

**إذا `payer_guardian_id` = null:**
- يسمح بالدفع فقط إذا guardian هو sponsor
- بعد أول دفع ناجح: ثبّت `payer_guardian_id` تلقائياً

---

### Exceptions

---

#### [NEW] `InvoicePayerMismatchException.php`
الدافع لا يطابق الدافع المثبت على الفاتورة

#### [NEW] `NoFinancialSponsorFoundException.php`
لا يوجد sponsor للطالب

#### [NEW] `MultipleFinancialSponsorsException.php`
أكثر من sponsor — فساد بيانات

---

### Validation

---

#### [MODIFY] [StudentRegistrationForm.php](file:///home/a/projects/school-dashboard/app/Livewire/Forms/Student/StudentRegistrationForm.php)

تعديل `validateGuardians()`:

```diff
- $hasSponsor = collect($guardians)->contains('is_financial_sponsor', true);
- if (!$hasSponsor) {
-     $this->addError('guardians', 'يجب تحديد مسؤول مالي واحد على الأقل.');
+ $sponsorCount = collect($guardians)->where('is_financial_sponsor', true)->count();
+ if ($sponsorCount === 0) {
+     $this->addError('guardians', 'يجب تحديد مسؤول مالي واحد.');
+     return false;
+ }
+ if ($sponsorCount > 1) {
+     $this->addError('guardians', 'لا يمكن تحديد أكثر من مسؤول مالي واحد.');
      return false;
  }
```

---

## ✅ Verification Plan

### Tests

#### Test 1: Payer Snapshot Immutability
```php
// إنشاء طالب + وليين (الأب sponsor)
// إنشاء فاتورة → payer = الأب
// تغيير sponsor للأم
// assert: الفاتورة ما زالت payer = الأب
```

#### Test 2: Payment Eligibility
```php
// دفع من غير payer → throws InvoicePayerMismatchException
// دفع من payer → مقبول
```

#### Test 3: Auto-fix null payer
```php
// فاتورة payer=null + دفع من sponsor
// assert: يقبل ويثبت payer تلقائياً
```

**أمر التشغيل:**
```bash
php artisan test --filter=PayerSnapshotTest
```

---

## 📁 ملخص الملفات

| النوع | الملف |
|-------|-------|
| **Migration** | `add_payer_to_invoices_table.php` |
| **Service** | `PayerResolverService.php` (جديد) |
| **Service** | `GuardianStatementService.php` (جديد) |
| **Model** | `Invoice.php` (تعديل) |
| **Action** | `CreateInvoiceAction.php` (تعديل) |
| **Action** | `RecordPaymentAction.php` (تعديل) |
| **Exception** | 3 جديدة |
| **Form** | `StudentRegistrationForm.php` (تعديل) |
| **Tests** | `PayerSnapshotTest.php` (جديد) |
