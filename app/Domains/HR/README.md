# 👥 HR Domain

## الوصف
نظام إدارة الموارد البشرية: الإجازات، الإحلال، الحضور

## 📁 الهيكل

```
Domains/HR/
├── README.md
├── Models/
│   ├── LeaveRequest.php
│   ├── LeaveType.php
│   ├── Substitution.php
│   ├── StaffLeaveBalance.php
│   └── StaffAttendance.php
├── Services/
│   ├── LeaveService.php
│   └── SubstitutionService.php
└── Actions/
    ├── ApproveLeaveRequestAction.php
    └── AssignSubstituteAction.php
```

## 🔗 العلاقات

```
Staff (1) ──→ (N) LeaveRequests
LeaveType (1) ──→ (N) LeaveRequests
LeaveRequest (1) ──→ (N) Substitutions
Teacher (1) ──→ (N) Substitutions (as original)
Teacher (1) ──→ (N) Substitutions (as substitute)
```

## 📋 قواعد العمل

1. **الإجازات**:
   - لا يمكن تعديل إجازة مقبولة
   - تُخصم من الرصيد السنوي تلقائياً
   - بعض الأنواع تتطلب مرفقات

2. **الإحلال**:
   - يجب تعيين بديل للحصص المتأثرة
   - البديل يمكنه القبول أو الرفض
   - الإحلال المدفوع يُضاف للراتب

## 🛠️ الـ Traits المطبقة

| Model | Traits |
|-------|--------|
| `LeaveRequest` | `HasModelLabels` |
| `LeaveType` | `HandlesSafeDelete`, `HasModelLabels` |
| `Substitution` | `HasModelLabels` |
