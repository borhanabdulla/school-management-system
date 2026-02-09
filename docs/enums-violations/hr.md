## مخالفات Enums في دومين HR

### 1) `PayrollBatchStatus`

- **الوصف:** Enum لحالة دفعة الرواتب:
  - `draft`, `frozen`, `approved`, `paid`

- **المخالفات:**
  1. استخدام القيم النصية `'approved'` و`'paid'` مباشرة في واجهة إدارة دفعات الرواتب:
     - `resources/views/livewire/payroll/payroll-batch-manager.blade.php`
       - خرائط الحالة إلى ألوان/أيقونات مبنية على `'paid'` كنص.
       - شرط عرض الأزرار يعتمد على المقارنة بـ `'approved'` أو `'paid'`.
  2. استخدام `'paid'` نصيًا في واجهة الملف المالي للموظف:
     - `resources/views/livewire/payroll/staff-financial-profile.blade.php`
       - مقارنة `$record->batch->status === 'paid'` لعرض "مدفوع/معلق".

- **نوع المخالفة:**
  - الاعتماد على نصوص ثابتة بدل `PayrollBatchStatus` Enum بينما حقل `status` في `PayrollBatch` مفترض أن يكون casted إلى enum.

- **اقتراحات الإصلاح:**
  - استبدال كل المقارنات النصية في Blade بمقارنات على Enum:
    - `\App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Paid`
    - `\App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Approved`
  - تعريف Helper/Map واحد يربط قيم Enum بـ ألوان/أيقونات UI بدلاً من تكرار النصوص في أكثر من مكان.

### 2) `LoanStatus`

- **الوصف:** Enum لحالة القرض/القسط:
  - `pending`, `approved`, `rejected`, `paid`, `cancelled`

- **المخالفات:**
  1. في `ApprovePayrollAction`:
     - `app/Domains/HR/Payroll/Actions/ApprovePayrollAction.php`
       - تحديث حالة الأقساط إلى `'paid'` نصيًا.
       - استعلام `where('status', 'paid')` لحساب المبالغ المدفوعة.
  2. في واجهة إدارة القروض:
     - `resources/views/livewire/payroll/loan-manager.blade.php`
       - استخدام `where('status', 'paid')` مباشرة في Blade لعرض عدد الأشهر المدفوعة.

- **نوع المخالفة:**
  - تجاوز Enum `LoanStatus` واستخدام String value مباشرة، ما يجعل تغيير القيمة أو إعادة تسميتها لاحقًا أمرًا خطيرًا ومعرّضًا لكسر النظام.

- **اقتراحات الإصلاح:**
  - في الأكشن:
    - استبدال `'paid'` بـ `\App\Domains\HR\Payroll\Enums\LoanStatus::Paid->value`.
  - في Blade:
    - حقن القيمة من الـ Model/Service بدلاً من كتابة الاستعلام داخل القالب.
    - أو استخدام Enum داخل الكود الممرَر للـ View.

### 3) `PayrollItemType`, `PayoutMethod`, `StaffStatus`, `ContractStatus`, `LeaveRequestStatus`, `StaffRole`

- **الوضع الحالي:**
  - `PayrollItemType` مستخدم بشكل صحيح في الموديلات والخدمات (`PayrollCalculationService`, `PayrollTotalsService`, `PayrollItem`, `PayrollRecord`) بدون استخدام Strings صريحة خارج نطاق الحفظ/الاستعلام المشروع.
  - `PayoutMethod` مستخدم عبر Enum في:
    - `PayrollBatch` (cast على `payout_method`)
    - `MarkPayrollPaidAction`
    - Livewire `PayrollBatchManager`
  - `StaffRole`, `StaffStatus`, `ContractStatus`, `LeaveRequestStatus`:
    - تستخدم بشكل سليم في:
      - نماذج Staff/Contracts/Leave
      - Data/Actions مثل `StaffOnboardingData`, `CreateStaffAction`, `UpdateStaffAction`

- **ملاحظات:**
  - لم تظهر مخالفات واضحة (Strings بدلاً من Enum) في هذه الـ Enums حسب مسح الكود الحالي.
  - يفضّل الاستمرار في هذا النمط والمحافظة على أن تكون أي إضافة جديدة تستعمل Enum بشكل صريح في الـ Models والـ Services والـ UI.

