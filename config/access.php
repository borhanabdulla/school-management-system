<?php

return [
    'permission_labels' => [
        // === النظام والإعدادات ===
        'close.year' => 'إغلاق السنة الدراسية',
        'sensitive.manage' => 'إدارة رمز الأمان الحساس',
        'roles.manage' => 'إدارة الصلاحيات',
        'settings.edit' => 'إعدادات النظام',
        'logs.view' => 'سجلات النظام',

        // === الطلاب ===
        'students.view' => 'عرض الطلاب',
        'students.create' => 'إضافة طالب',
        'students.edit' => 'تعديل بيانات طالب',
        'students.delete' => 'حذف طالب',
        'students.promote' => 'ترحيل الطلاب',

        // === المناهج والبنية ===
        'curriculum.manage' => 'إدارة المناهج',
        'classes.manage' => 'إدارة الفصول',
        'timetable.manage' => 'إدارة الجداول',

        // === الدرجات (عام) ===
        'marks.view' => 'عرض الدرجات',
        'marks.edit' => 'رصد الدرجات',
        'marks.override' => 'تعديل الدرجات (إداري)',

        // === الدرجات (تفصيلي) ===
        'grading.amend' => 'تعديل الدرجات بعد الإغلاق',
        'grading.recompute' => 'إعادة احتساب أعمال السنة',
        'grading.manage_templates' => 'إدارة قوالب التقييم',
        'grading.manage_settings' => 'إدارة إعدادات الدرجات',
        'grading.record_marks' => 'تسجيل درجات الطلاب',
        'grading.view_templates' => 'عرض قوالب التقييم',
        'grading.view_gradebook' => 'عرض دفتر الدرجات',
        'grading.view_marks' => 'عرض درجات الطلاب',
        'grading.view_marks_history' => 'عرض تاريخ تعديلات الدرجات',

        // === الموظفين ===
        'staff.view' => 'عرض الموظفين',
        'staff.create' => 'إضافة موظف',
        'staff.edit' => 'تعديل موظف',
        'staff.delete' => 'حذف موظف',

        // === الحضور ===
        'attendance.manage' => 'إدارة الحضور',
        'attendance.view' => 'عرض الحضور',
        'attendance.take' => 'تسجيل الحضور',

        // === الإجازات ===
        'leaves.approve' => 'الموافقة على الإجازات',
        'leave.request' => 'طلب إجازة',

        // === الرواتب ===
        'payroll.manage' => 'إدارة الرواتب',

        // === المالية ===
        'finance.view' => 'عرض المالية',
        'finance.apply_discount' => 'تطبيق الخصومات',
        'finance.record_payment' => 'تسجيل دفعة',
        'finance.cancel_payment' => 'إلغاء دفعة',

        // === أولياء الأمور ===
        'guardians.view' => 'عرض أولياء الأمور',
        'guardians.create' => 'إضافة ولي أمر',
        'guardians.edit' => 'تعديل ولي أمر',

        // === صلاحيات المعلم ===
        'teacher.dashboard' => 'لوحة المعلم',
        'teacher.timetable' => 'جدول المعلم',
        'teacher.homework' => 'واجبات المعلم',

        // === صلاحيات الطالب ===
        'student.view_own_grades' => 'عرض درجاتي',
        'student.view_own_attendance' => 'عرض حضوري',
        'student.homework' => 'واجباتي',
    ],

    'permission_group_labels' => [
        'close' => 'السنة الدراسية',
        'sensitive' => 'الأمان الحساس',
        'students' => 'الطلاب',
        'curriculum' => 'المناهج',
        'classes' => 'الفصول',
        'timetable' => 'الجداول',
        'marks' => 'الدرجات',
        'grading' => 'نظام الدرجات',
        'staff' => 'الموظفون',
        'attendance' => 'الحضور',
        'leaves' => 'الإجازات',
        'leave' => 'الإجازات',
        'payroll' => 'الرواتب',
        'finance' => 'المالية',
        'guardians' => 'أولياء الأمور',
        'roles' => 'الأدوار والصلاحيات',
        'settings' => 'الإعدادات',
        'logs' => 'السجلات',
        'teacher' => 'المعلم',
        'student' => 'الطالب',
        'other' => 'أخرى',
    ],

    'role_labels' => [
        'Super Admin' => 'المدير العام',
        'super_admin' => 'المدير العام',
        'Admin' => 'مدير النظام',
        'admin' => 'مدير النظام',
        'Teacher' => 'معلم',
        'teacher' => 'معلم',
        'Student' => 'طالب',
        'student' => 'طالب',
        'Parent' => 'ولي أمر',
        'parent' => 'ولي أمر',
        'Accountant' => 'محاسب',
        'accountant' => 'محاسب',
        'Academic Coordinator' => 'منسق أكاديمي',
        'academic_coordinator' => 'منسق أكاديمي',
        'HR Manager' => 'مدير الموارد البشرية',
        'hr_manager' => 'مدير الموارد البشرية',
    ],

    'static_roles' => [
        'Super Admin',
        'admin',
        'teacher',
        'student',
        'parent',
    ],
];
