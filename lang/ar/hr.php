<?php

return [
    // Leave Service Messages
    'leave' => [
        'no_work_days_in_period' => 'الفترة المحددة لا تحتوي على أيام عمل فعلية (كلها عطلات).',
        'insufficient_balance' => 'رصيد الإجازة غير كافٍ. المتبقي: :remaining يوم، المطلوب: :requested يوم.',
        'auto_remarks' => 'إجازة: :type',
        'overlap' => 'يوجد طلب إجازة متداخل لنفس الموظف في نفس الفترة.',
        'request_submitted' => 'تم تقديم طلب الإجازة بنجاح.',
        'request_approved' => 'تم اعتماد الإجازة بنجاح.',
        'request_rejected' => 'تم رفض طلب الإجازة.',
        'balance_recalculated' => 'تم إعادة احتساب الأرصدة بنجاح.',
    ],

    'employment_types' => [
        'full_time' => 'دوام كامل',
        'part_time' => 'دوام جزئي',
        'contract' => 'عقد',
        'temporary' => 'مؤقت',
    ],
    'days' => [
        'sun' => 'الأحد',
        'mon' => 'الإثنين',
        'tue' => 'الثلاثاء',
        'wed' => 'الأربعاء',
        'thu' => 'الخميس',
        'fri' => 'الجمعة',
        'sat' => 'السبت',
    ],
    'status' => [
        'present' => 'حاضر',
        'absent' => 'غائب',
        'late' => 'متأخر',
        'excused' => 'معذور',
        'pending' => 'قيد الانتظار',
    ],
];
