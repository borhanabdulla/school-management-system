# 💰 Finance Domain

## الوصف
نظام إدارة الرسوم الدراسية والفواتير والمدفوعات

## 📁 الهيكل

```
Domains/Finance/
├── README.md
├── Models/
│   ├── FeeType.php
│   ├── FeeStructure.php
│   ├── Invoice.php
│   ├── InvoiceItem.php
│   └── Payment.php
└── Services/
    └── FinanceService.php
```

## 🔗 العلاقات

```
FeeType (1) ──→ (N) FeeStructures
Grade (1) ──→ (N) FeeStructures
Student (1) ──→ (N) Invoices
Invoice (1) ──→ (N) InvoiceItems
Invoice (1) ──→ (N) Payments
```

## 📋 قواعد العمل

1. **الرسوم**:
   - يمكن تحديد رسوم لكل صف دراسي
   - الرسوم المتكررة تُطبق سنوياً

2. **الفواتير**:
   - تُنشأ الفواتير بناءً على هيكل الرسوم
   - لا يمكن حذف فاتورة مدفوعة جزئياً أو كلياً

3. **المدفوعات**:
   - لا يمكن أن يتجاوز مجموع المدفوعات قيمة الفاتورة
   - يمكن الدفع الجزئي

## 🛠️ الـ Traits المطبقة

| Model | Traits |
|-------|--------|
| `FeeType` | `HandlesSafeDelete`, `HasModelLabels` |
| `FeeStructure` | `HandlesSafeDelete`, `HasModelLabels` |
| `Invoice` | `HandlesSafeDelete`, `HasModelLabels` |
| `Payment` | `HandlesSafeDelete`, `HasModelLabels` |
