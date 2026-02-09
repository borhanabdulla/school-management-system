## مخالفات Enums في دومين Finance

### 1) `InvoiceStatus`

- **المشكلة:** استخدام قيم نصية (`'paid'`, `'unpaid'`, `'partially_paid'`, `'cancelled'`, `'overdue'`) في أماكن متفرقة بدلاً من الاعتماد الكامل على `InvoiceStatus`.
- **أمثلة على المواقع المتأثرة:**
  - منطق التحقق المالي في `PromotionService`:
    - `app/Domains/Academic/Promotion/Services/PromotionService.php` (شرط يجمع بين `InvoiceStatus::Paid->value` و `'paid'` نصياً).
  - قيم قديمة `"partial"` تسبب `ValueError` مع `InvoiceStatus`:
    - `storage/logs/laravel.log` (رسائل الخطأ).
    - `database/migrations/2026_02_02_000001_alter_invoices_status_enum.php` (ترحيل من `partial` إلى `partially_paid`).
  - تمثيل الحالة المالية للطالب في الواجهات:
    - `resources/views/livewire/student/student-directory.blade.php` (استنتاج `'unpaid'/'paid'` من مبلغ متبقّي).
    - `resources/views/components/student/badge.blade.php` (خرائط ألوان لـ `'paid'/'unpaid'`).

- **نوع المخالفة:**
  - خلط بين منطق الدومين (حالة الفاتورة الحقيقية) وتمثيل عرضي مبسّط (`paid/unpaid`).
  - الاعتماد على نصوص ثابتة بدلاً من Enum موحّد، ما يصعّب تغيير القيم مستقبلاً.

- **اقتراحات الإصلاح:**
  - جعل كل حقول حالة الفاتورة تعتمد فقط على `InvoiceStatus` (cast على الموديل + استخدام الـ Enum في الاستعلامات).
  - تنظيف كل القيم القديمة `partial` في قاعدة البيانات (Migration/Script إصلاحي).
  - تعريف طبقة تحويل من `InvoiceStatus` إلى حالة عرضية مبسطة عند الحاجة (مثلاً: Helper أو خدمة تعرض `paid/unpaid` بناءً على `InvoiceStatus` ومبالغ السداد).

### 2) `PaymentStatus`

- **الوضع الحالي:** معظم الاستخدامات تعتمد على الـ Enum (`PaymentStatus::Posted/Cancelled`) في النماذج والخدمات والواجهات.
- **الملاحظة:** بعض الاختبارات ما زالت تستخدم `'cancelled'` كنص مباشر.
  - أمثلة:
    - `tests/Feature/Ledger/LedgerPaymentCancellationTest.php`
    - `tests/Feature/Domains/Finance/FinancialClosingTest.php`
- **نوع المخالفة:** ليست معمارية خطيرة، لكنها تقلّل من اتساق الدومين بين الكود الإنتاجي والاختبارات.
- **اقتراح الإصلاح:** استخدام `PaymentStatus::Cancelled->value` في كل الاختبارات بدلاً من النص.

### 3) تكامل Finance مع Domains أخرى

- **أمثلة على التكامل:**
  - `StudentLookupService` في دومين Academic يعتمد على `InvoiceStatus::unpaidValues()` لتحديد الالتزامات المالية للطالب:
    - `app/Domains/Academic/Student/Services/StudentLookupService.php`
  - `PromotionService` يستخدم `InvoiceStatus` للتحقق من إبراء الذمة المالية:
    - `app/Domains/Academic/Promotion/Services/PromotionService.php`

- **نوع المسألة:** Coupling عابر للدومينات (مقبول وظيفياً لكنه يحتاج توثيق).
- **اقتراح:** إن زاد هذا التكامل، يمكن التفكير في:
  - نقل منطق التحقق المالي إلى خدمة في Finance (مثلاً: `FinancialClearanceService`) وتستدعيها الدومينات الأخرى.
  - أو إنشاء طبقة Anti-Corruption تعيد قيمًا أبسط (OK/NOT_OK) بدلاً من تعريض الأكاديميا لتفاصيل `InvoiceStatus`.

