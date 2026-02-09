# PR0 — تشغيل الرسوم والتحصيل اليدوي بشكل صحيح

## هدف PR0

إنشاء فاتورة + تسجيل دفع يدوي/نقدي + تحديث حالة الفاتورة بشكل صحيح، مع ربط الدفع بـ **الولي المالي (is_financial_sponsor)**.

---

## ✅ Definition of Done

1. إنشاء Invoice من FeeStructure بدون أخطاء DB
2. تسجيل Payment على Invoice (نقد/تحويل يدوي فقط) مرتبط بـ الولي المالي
3. منع الدفع الزائد (Overpay)
4. تحديث حالة الفاتورة: `unpaid → partially_paid → paid`
5. لا يسمح بالدفع/التعديل إذا السنة الدراسية Closed
6. اختبارين Feature يثبتون السيناريو (جزئي/كامل)

---

## 🔍 المراجعة المعمارية - الفجوات المكتشفة

### 1. عدم تطابق Enum حالة الفاتورة

| المكان | القيم |
|--------|-------|
| **DB Migration** | `unpaid`, `paid`, `partial` |
| **InvoiceStatus Enum** | `unpaid`, `partially_paid`, `paid`, `cancelled`, `overdue` |

> [!CAUTION]
> الكود يستخدم `partially_paid` لكن DB يقبل فقط `partial` - سيسبب خطأ عند التحديث!

### 2. InvoiceItem Model vs DB

| الموضع | fillable |
|--------|----------|
| **Model** | `fee_structure_id`, `description` |
| **DB** | `fee_type_id`, `discount_id` |

### 3. CreateInvoiceAction يكتب أعمدة غير موجودة

```php
// السطور 67-70 تكتب:
'description' => 'رسوم دراسية',  // ❌ لا يوجد
'quantity' => 1,                   // ❌ لا يوجد
```

### 4. Payment Model و Table

- **لا يوجد migration لجدول payments** ❌
- Model يعرف fillable لكن الجدول غير موجود
- ينقص `guardian_id` لربط الدافع

### 5. PaymentData DTO

```php
class PaymentData extends BaseData  // ❌ BaseData غير مستورد
```

### 6. FeeStructure Model

```php
return $this->belongsTo(Grade::class);  // ❌ Grade غير مستورد
```

---

## 📋 الخطة التنفيذية

### Migrations

---

#### [NEW] [2026_02_02_000001_alter_invoices_status_enum.php](file:///home/a/projects/school-dashboard/database/migrations/2026_02_02_000001_alter_invoices_status_enum.php)

توسيع enum لحالة الفاتورة:
- إضافة: `partially_paid`, `cancelled`, `overdue`
- تحويل البيانات القديمة: `partial → partially_paid`
- إزالة القيمة القديمة `partial`

```php
// Migration content:
DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'paid', 'partial', 'partially_paid', 'cancelled', 'overdue') DEFAULT 'unpaid'");
DB::statement("UPDATE invoices SET status = 'partially_paid' WHERE status = 'partial'");
DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'paid', 'partially_paid', 'cancelled', 'overdue') DEFAULT 'unpaid'");
```

---

#### [NEW] [2026_02_02_000002_create_payments_table.php](file:///home/a/projects/school-dashboard/database/migrations/2026_02_02_000002_create_payments_table.php)

إنشاء جدول المدفوعات:

| العمود | النوع | الملاحظات |
|--------|-------|-----------|
| `id` | bigint | PK |
| `invoice_id` | FK | → invoices |
| `guardian_id` | FK | → guardians (الدافع) |
| `amount` | decimal(10,2) | |
| `method` | enum | `cash`, `manual_transfer` |
| `transaction_reference` | string nullable | unique index |
| `paid_at` | datetime | |
| `notes` | text nullable | |
| `created_by` | FK nullable | → users (المحاسب) |
| timestamps | | |

---

### Models

---

#### [MODIFY] [InvoiceItem.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Models/InvoiceItem.php)

تصحيح fillable ليطابق DB:

```diff
- protected $fillable = ['invoice_id', 'fee_structure_id', 'amount', 'description'];
+ protected $fillable = ['invoice_id', 'fee_type_id', 'amount', 'discount_id'];
```

إضافة علاقة feeType():
```php
public function feeType()
{
    return $this->belongsTo(\App\Domains\Finance\Models\FeeType::class);
}
```

---

#### [MODIFY] [Invoice.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Models/Invoice.php)

إضافة علاقة payments() (مذكورة في protectedRelations لكن غير معرفة):

```php
public function payments()
{
    return $this->hasMany(Payment::class);
}
```

---

#### [MODIFY] [Payment.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Models/Payment.php)

تحديث fillable وإضافة علاقات:

```diff
- protected $fillable = ['invoice_id', 'amount', 'method', 'transaction_reference', 'paid_at', 'notes'];
+ protected $fillable = ['invoice_id', 'guardian_id', 'amount', 'method', 'transaction_reference', 'paid_at', 'notes', 'created_by'];
```

```php
public function guardian()
{
    return $this->belongsTo(\App\Domains\Academic\Guardian\Models\Guardian::class);
}

public function creator()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}
```

---

#### [MODIFY] [FeeStructure.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Models/FeeStructure.php)

إضافة import مفقود:

```php
use App\Domains\Academic\Grade\Models\Grade;
```

---

### Enums / DTO

---

#### [MODIFY] [PaymentMethod.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Enums/PaymentMethod.php)

تبسيط للطرق المدعومة فعلياً في PR0:

```diff
  enum PaymentMethod: string
  {
      case Cash = 'cash';
-     case BankTransfer = 'bank_transfer';
-     case CreditCard = 'credit_card';
-     case Cheque = 'cheque';
-     case Online = 'online';
+     case ManualTransfer = 'manual_transfer';
  }
```

---

#### [MODIFY] [PaymentData.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Data/PaymentData.php)

1. إضافة import مفقود
2. إضافة guardianId إلزامي

```diff
+ use App\Infrastructure\Data\BaseData;

  class PaymentData extends BaseData
  {
      public function __construct(
          public readonly int $invoiceId,
+         public readonly int $guardianId,
          public readonly float $amount,
          ...
      ) {}
  }
```

---

### Actions / Services

---

#### [MODIFY] [CreateInvoiceAction.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Actions/CreateInvoiceAction.php)

إزالة الحقول غير الموجودة في DB:

```diff
  InvoiceItem::create([
      'invoice_id' => $invoice->id,
      'fee_type_id' => $fee->fee_type_id,
-     'description' => 'رسوم دراسية',
      'amount' => $fee->amount,
-     'quantity' => 1,
+     'discount_id' => null,  // PR0 بدون خصومات
  ]);
```

---

#### [MODIFY] [RecordPaymentAction.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Actions/RecordPaymentAction.php)

إضافة 4 قواعد إلزامية:

**A. منع الدفع على سنة Closed:**
```php
if ($invoice->academicYear->status === AcademicYearStatus::Closed) {
    throw new AcademicYearClosedException();
}
```

**B. التحقق من الولي المالي:**
```php
$student = $invoice->student;
$isFinancialSponsor = $student->guardians()
    ->wherePivot('guardian_id', $data->guardianId)
    ->wherePivot('is_financial_sponsor', true)
    ->exists();

if (!$isFinancialSponsor) {
    throw new GuardianNotFinancialSponsorException();
}
```

**C. ربط guardian_id في الدفعة:**
```php
$payment = Payment::create([
    'invoice_id' => $invoice->id,
    'guardian_id' => $data->guardianId,  // ➕ جديد
    'amount' => $data->amount,
    ...
]);
```

**D. استخدام InvoiceTotalsService (لاحقاً):**
```php
// بدل التحديث اليدوي
$this->invoiceTotalsService->recalculate($invoice);
```

---

#### [NEW] [InvoiceTotalsService.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Services/InvoiceTotalsService.php)

خدمة موحدة لإعادة حساب حالة الفاتورة:

```php
class InvoiceTotalsService
{
    public function recalculate(Invoice $invoice): Invoice
    {
        $paidAmount = $invoice->payments()->sum('amount');
        
        $status = match(true) {
            $paidAmount >= $invoice->total_amount => InvoiceStatus::Paid,
            $paidAmount > 0 => InvoiceStatus::PartiallyPaid,
            default => InvoiceStatus::Unpaid,
        };
        
        $invoice->update([
            'paid_amount' => $paidAmount,
            'status' => $status,
        ]);
        
        return $invoice->fresh();
    }
}
```

---

#### [NEW] [AcademicYearClosedException.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Exceptions/AcademicYearClosedException.php)

```php
class AcademicYearClosedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('لا يمكن تنفيذ العملية: السنة الدراسية مغلقة');
    }
}
```

---

#### [NEW] [GuardianNotFinancialSponsorException.php](file:///home/a/projects/school-dashboard/app/Domains/Finance/Exceptions/GuardianNotFinancialSponsorException.php)

```php
class GuardianNotFinancialSponsorException extends \Exception
{
    public function __construct()
    {
        parent::__construct('الولي المحدد ليس المسؤول المالي لهذا الطالب');
    }
}
```

---

## ✅ Verification Plan

### Automated Tests

سننشئ ملف اختبار جديد:

#### [NEW] [tests/Feature/Domains/Finance/PaymentFlowTest.php](file:///home/a/projects/school-dashboard/tests/Feature/Domains/Finance/PaymentFlowTest.php)

```php
// Test 1: partial then paid
it('correctly updates invoice status through payment flow', function () {
    // Setup: Student + Guardian (sponsor) + Active Year + Invoice(1000)
    // Act: Pay 200 → assert partially_paid
    // Act: Pay 800 → assert paid
});

// Test 2: reject overpay
it('rejects payment exceeding remaining amount', function () {
    // Setup: Invoice(500) paid=0
    // Act: Pay 600
    // Assert: throws PaymentExceedsInvoiceException
});

// Test 3: reject non-sponsor
it('rejects payment from non-financial-sponsor guardian', function () {
    // Setup: Guardian without is_financial_sponsor
    // Assert: throws GuardianNotFinancialSponsorException
});

// Test 4: reject closed year
it('rejects payment on closed academic year', function () {
    // Setup: AcademicYear::Closed
    // Assert: throws AcademicYearClosedException
});
```

**أمر التشغيل:**
```bash
php artisan test --filter=PaymentFlowTest
```

---

### Manual Verification

> [!NOTE]
> بعد تشغيل الـ Migrations وتطبيق التغييرات، يمكن التحقق يدوياً:

1. **فتح Tinker:**
   ```bash
   php artisan tinker
   ```

2. **اختبار إنشاء فاتورة:**
   ```php
   $invoice = \App\Domains\Finance\Models\Invoice::first();
   $invoice->status; // should be InvoiceStatus enum
   ```

3. **اختبار إنشاء دفعة:**
   ```php
   $payment = \App\Domains\Finance\Models\Payment::create([...]);
   $payment->guardian; // should return Guardian model
   ```

---

## 📁 ملخص الملفات

| النوع | الملف |
|-------|-------|
| **Migration** | `2026_02_02_000001_alter_invoices_status_enum.php` |
| **Migration** | `2026_02_02_000002_create_payments_table.php` |
| **Model** | `InvoiceItem.php` (تعديل) |
| **Model** | `Invoice.php` (تعديل) |
| **Model** | `Payment.php` (تعديل) |
| **Model** | `FeeStructure.php` (تعديل) |
| **Enum** | `PaymentMethod.php` (تعديل) |
| **DTO** | `PaymentData.php` (تعديل) |
| **Action** | `CreateInvoiceAction.php` (تعديل) |
| **Action** | `RecordPaymentAction.php` (تعديل) |
| **Service** | `InvoiceTotalsService.php` (جديد) |
| **Exception** | `AcademicYearClosedException.php` (جديد) |
| **Exception** | `GuardianNotFinancialSponsorException.php` (جديد) |
| **Test** | `PaymentFlowTest.php` (جديد) |

---

## ⚠️ ملاحظات مهمة

> [!WARNING]
> في PR0 **لن نلمس الخصومات ولا العروض**.
> سنضمن فقط أن `invoice_items.discount_id` يبقى موجوداً (nullable).
> سياسات الخصم تبدأ في PR2/PR3.

> [!IMPORTANT]
> يجب تشغيل الـ Migrations بالترتيب الصحيح (status enum أولاً، ثم payments table).
