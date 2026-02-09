<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => 'يجب قبول :attribute.',
    'accepted_if' => 'يجب قبول :attribute عندما يكون :other هو :value.',
    'active_url' => ':attribute ليس رابطاً صحيحاً.',
    'after' => 'يجب أن يكون :attribute تاريخاً بعد :date.',
    'after_or_equal' => 'يجب أن يكون :attribute تاريخاً بعد أو يساوي :date.',
    'alpha' => 'يجب أن يحتوي :attribute على أحرف فقط.',
    'alpha_dash' => 'يجب أن يحتوي :attribute على أحرف وأرقام وشرطات فقط.',
    'alpha_num' => 'يجب أن يحتوي :attribute على أحرف وأرقام فقط.',
    'array' => 'يجب أن يكون :attribute مصفوفة.',
    'before' => 'يجب أن يكون :attribute تاريخاً قبل :date.',
    'before_or_equal' => 'يجب أن يكون :attribute تاريخاً قبل أو يساوي :date.',
    'between' => [
        'numeric' => 'يجب أن يكون :attribute بين :min و :max.',
        'file' => 'يجب أن يكون حجم :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون عدد أحرف :attribute بين :min و :max.',
        'array' => 'يجب أن يحتوي :attribute على :min إلى :max عناصر.',
    ],
    'boolean' => 'يجب أن يكون :attribute صحيحاً أو خاطئاً.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'current_password' => 'كلمة المرور الحالية غير صحيحة.',
    'date' => ':attribute ليس تاريخاً صحيحاً.',
    'date_equals' => 'يجب أن يكون :attribute تاريخاً مساوياً لـ :date.',
    'date_format' => ':attribute لا يتطابق مع الصيغة :format.',
    'declined' => 'يجب رفض :attribute.',
    'declined_if' => 'يجب رفض :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits' => 'يجب أن يحتوي :attribute على :digits أرقام.',
    'digits_between' => 'يجب أن يحتوي :attribute على :min إلى :max أرقام.',
    'dimensions' => ':attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => ':attribute يحتوي على قيمة مكررة.',
    'email' => 'يجب أن يكون :attribute بريداً إلكترونياً صحيحاً.',
    'ends_with' => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'exists' => ':attribute المحدد غير صحيح.',
    'file' => 'يجب أن يكون :attribute ملفاً.',
    'filled' => 'يجب أن يحتوي :attribute على قيمة.',
    'gt' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من :value.',
        'file' => 'يجب أن يكون حجم :attribute أكبر من :value كيلوبايت.',
        'string' => 'يجب أن يكون عدد أحرف :attribute أكبر من :value.',
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عناصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
        'file' => 'يجب أن يكون حجم :attribute أكبر من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يكون عدد أحرف :attribute أكبر من أو يساوي :value.',
        'array' => 'يجب أن يحتوي :attribute على :value عناصر أو أكثر.',
    ],
    'image' => 'يجب أن يكون :attribute صورة.',
    'in' => ':attribute المحدد غير صحيح.',
    'in_array' => ':attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون :attribute عدداً صحيحاً.',
    'ip' => 'يجب أن يكون :attribute عنوان IP صحيحاً.',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صحيحاً.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صحيحاً.',
    'json' => 'يجب أن يكون :attribute نص JSON صحيحاً.',
    'lt' => [
        'numeric' => 'يجب أن يكون :attribute أقل من :value.',
        'file' => 'يجب أن يكون حجم :attribute أقل من :value كيلوبايت.',
        'string' => 'يجب أن يكون عدد أحرف :attribute أقل من :value.',
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عناصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن يكون :attribute أقل من أو يساوي :value.',
        'file' => 'يجب أن يكون حجم :attribute أقل من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يكون عدد أحرف :attribute أقل من أو يساوي :value.',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :value عناصر.',
    ],
    'max' => [
        'numeric' => 'يجب أن لا يكون :attribute أكبر من :max.',
        'file' => 'يجب أن لا يكون حجم :attribute أكبر من :max كيلوبايت.',
        'string' => 'يجب أن لا يكون عدد أحرف :attribute أكبر من :max.',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :max عناصر.',
    ],
    'mimes' => 'يجب أن يكون :attribute ملفاً من نوع: :values.',
    'mimetypes' => 'يجب أن يكون :attribute ملفاً من نوع: :values.',
    'min' => [
        'numeric' => 'يجب أن يكون :attribute على الأقل :min.',
        'file' => 'يجب أن يكون حجم :attribute على الأقل :min كيلوبايت.',
        'string' => 'يجب أن يكون عدد أحرف :attribute على الأقل :min.',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :min عناصر.',
    ],
    'multiple_of' => 'يجب أن يكون :attribute من مضاعفات :value.',
    'not_in' => ':attribute المحدد غير صحيح.',
    'not_regex' => 'صيغة :attribute غير صحيحة.',
    'numeric' => 'يجب أن يكون :attribute رقماً.',
    'password' => 'كلمة المرور غير صحيحة.',
    'present' => 'يجب أن يكون :attribute موجوداً.',
    'regex' => 'صيغة :attribute غير صحيحة.',
    'required' => ':attribute مطلوب.',
    'required_if' => ':attribute مطلوب عندما يكون :other هو :value.',
    'required_unless' => ':attribute مطلوب ما لم يكن :other في :values.',
    'required_with' => ':attribute مطلوب عندما يكون :values موجوداً.',
    'required_with_all' => ':attribute مطلوب عندما تكون :values موجودة.',
    'required_without' => ':attribute مطلوب عندما لا يكون :values موجوداً.',
    'required_without_all' => ':attribute مطلوب عندما لا تكون :values موجودة.',
    'prohibited' => ':attribute محظور.',
    'prohibited_if' => ':attribute محظور عندما يكون :other هو :value.',
    'prohibited_unless' => ':attribute محظور ما لم يكن :other في :values.',
    'prohibits' => ':attribute يحظر وجود :other.',
    'same' => 'يجب أن يتطابق :attribute مع :other.',
    'size' => [
        'numeric' => 'يجب أن يكون :attribute :size.',
        'file' => 'يجب أن يكون حجم :attribute :size كيلوبايت.',
        'string' => 'يجب أن يكون عدد أحرف :attribute :size.',
        'array' => 'يجب أن يحتوي :attribute على :size عناصر.',
    ],
    'starts_with' => 'يجب أن يبدأ :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون :attribute نصاً.',
    'timezone' => 'يجب أن يكون :attribute منطقة زمنية صحيحة.',
    'unique' => ':attribute مُستخدم من قبل.',
    'uploaded' => 'فشل رفع :attribute.',
    'url' => 'صيغة :attribute غير صحيحة.',
    'uuid' => 'يجب أن يكون :attribute UUID صحيحاً.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'subject_already_assigned' => 'المادة مضافة مسبقاً لهذا الصف في (:term). لا يمكن تكرارها في نفس التوقيت.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    | تعريب أسماء الحقول
    */

    'attributes' => [
        // معلومات الطالب
        'first_name_ar' => 'الاسم الأول بالعربية',
        'family_name_ar' => 'اسم العائلة بالعربية',
        'first_name_en' => 'الاسم الأول بالإنجليزية',
        'family_name_en' => 'اسم العائلة بالإنجليزية',
        'date_of_birth' => 'تاريخ الميلاد',
        'gender' => 'الجنس',
        'nationality_id' => 'الجنسية',
        'national_id' => 'الرقم القومي',
        'passport_number' => 'رقم جواز السفر',
        'blood_type' => 'فصيلة الدم',
        'admission_number' => 'رقم القبول',

        // معلومات ولي الأمر
        'guardian_first_name' => 'اسم ولي الأمر الأول',
        'guardian_last_name' => 'اسم عائلة ولي الأمر',
        'guardian_national_id' => 'الرقم القومي لولي الأمر',
        'guardian_phone' => 'هاتف ولي الأمر',
        'guardian_nationality_id' => 'جنسية ولي الأمر',
        'guardian_preferred_language' => 'اللغة المفضلة',
        'relationship' => 'صلة القرابة',

        // معلومات أكاديمية
        'grade_id' => 'الصف الدراسي',
        'grade_name' => 'اسم الصف الدراسي',

        'class_section_id' => 'الشعبة',
        'academic_year_id' => 'السنة الدراسية',
        'term_id' => 'الفصل الدراسي',
        'educational_stage_id' => 'المرحلة التعليمية',

        // العنوان
        'city' => 'المدينة',

        'district' => 'الحي',
        'street_name' => 'اسم الشارع',
        'building_number' => 'رقم المبنى',
        'google_maps_link' => 'رابط خرائط جوجل',


        // السنة الدراسية
        'name' => 'الاسم',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'status' => 'الحالة',

        // عام
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'phone' => 'رقم الهاتف',
        'address' => 'العنوان',
        'notes' => 'ملاحظات',

        // إدارة المواد
        'form.name' => 'اسم المادة',
        'form.code' => 'كود المادة',
        'form.type' => 'نوع المادة',
        'alloc_subject_id' => 'المادة',
        'credit_hours' => 'عدد الحصص',
        'max_grade' => 'الدرجة العظمى',
        'pass_grade' => 'درجة النجاح',
        'term_type' => 'فترة التدريس',
        'source_year_id' => 'السنة المصدر',
        'target_year_id' => 'السنة الهدف',

        // ولي الأمر - صفحة الإنشاء
        'form.first_name' => 'الاسم الأول',
        'form.last_name' => 'اسم العائلة',
        'form.national_id' => 'الرقم القومي',
        'form.nationality_id' => 'الجنسية',
        'form.phone' => 'رقم الهاتف الجوال',
        'form.email' => 'البريد الإلكتروني',
        'form.employer' => 'جهة العمل',
        'form.work_phone' => 'هاتف العمل',
        'form.city' => 'المدينة',
        'form.district' => 'الحي',
        'form.street_name' => 'اسم الشارع',

        // ولي الأمر - Labels
        'first_name' => 'الاسم الأول',
        'last_name' => 'اسم العائلة',
        'national_id' => 'الرقم القومي',
        'nationality_id' => 'الجنسية',
        'phone' => 'رقم الهاتف الجوال',
        'email' => 'البريد الإلكتروني',
        'employer' => 'جهة العمل',
        'work_phone' => 'هاتف العمل',
        'city' => 'المدينة',
        'district' => 'الحي',
        'street_name' => 'اسم الشارع',
    ],
];
