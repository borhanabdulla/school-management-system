<?php

namespace App\Livewire\Forms\Student;

use Livewire\Form;
use App\Domains\Academic\Grade\Models\Grade;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Domains\Academic\Student\Enums\GuardianRelationship;
use Illuminate\Support\Facades\Validator;

class StudentRegistrationForm extends Form
{
    // Step 1: Student Info
    public $first_name_ar = '';
    public $family_name_ar = '';
    public $first_name_en = '';  // اختياري
    public $family_name_en = ''; // اختياري
    public $date_of_birth = '';
    public $gender = 'male';
    public $nationality_id = '';
    public $blood_type = '';
    public $national_id = '';
    public $photo = null;

    // Step 2: Temporary Guardian Info
    public $guardian_first_name = '';
    public $guardian_last_name = '';
    public $guardian_national_id = '';
    public $guardian_phone = '';
    public $guardian_nationality_id = '';
    public $guardian_preferred_language = 'ar';

    // Step 3: Academic Info
    public $grade_id = '';
    public $class_section_id = '';

    // Step 4: Address
    public $city = '';
    public $district = '';
    public $street_name = '';
    public $building_number = '';
    public $google_maps_link = '';

    // Step 4: Transfer Student Extra Info
    public $school_name = '';
    public $previous_curriculum = '';
    public $last_grade_completed = '';
    public $completion_year = '';
    public $last_gpa = '';
    public $reason_for_transfer = '';

    public function rules()
    {
        return [
            // Step 1: Student Info
            'first_name_ar' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}\s]+$/u'],
            'family_name_ar' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}\s]+$/u'],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'nationality_id' => 'nullable|exists:countries,id',
            'blood_type' => 'nullable|string|max:3',
            'national_id' => 'nullable|unique:students,national_id|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Step 2: Guardian Info (يجب تعريفها هنا لمنع الخطأ، حتى لو كانت nullable)
            // جعلناها nullable لأن التحقق الصارم يتم عند الضغط على زر "إضافة ولي أمر"
            'guardian_first_name' => 'nullable|string|max:255',
            'guardian_last_name' => 'nullable|string|max:255',
            'guardian_national_id' => 'nullable|string|max:20',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_nationality_id' => 'nullable|exists:countries,id',
            'guardian_preferred_language' => 'nullable|string|in:ar,en',

            // Step 3: Academic Info
            'grade_id' => 'required|exists:grades,id',
            'class_section_id' => 'nullable|exists:class_sections,id',

            // Step 4: Address
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'street_name' => 'required|string|max:255',
            'building_number' => 'nullable|string|max:50',
            'google_maps_link' => 'nullable|url|max:500',

            // Step 4 – Transfer
            'school_name' => 'nullable|string|max:255',
            'previous_curriculum' => 'nullable|string|max:255',
            'last_grade_completed' => 'nullable|string|max:255',
            'completion_year' => 'nullable|integer|min:2000|max:' . date('Y'),
            'last_gpa' => 'nullable|numeric|min:0|max:100',
            'reason_for_transfer' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            // Step 1: Student Info
            'first_name_ar.required' => 'يرجى إدخال الاسم الأول للطالب باللغة العربية.',
            'first_name_ar.string' => 'الاسم الأول يجب أن يكون نصاً.',
            'first_name_ar.max' => 'الاسم الأول طويل جداً (الحد الأقصى 255 حرف).',
            'first_name_ar.regex' => 'الاسم الأول يجب أن يحتوي على أحرف عربية فقط.',

            'family_name_ar.required' => 'يرجى إدخال اسم العائلة للطالب باللغة العربية.',
            'family_name_ar.string' => 'اسم العائلة يجب أن يكون نصاً.',
            'family_name_ar.max' => 'اسم العائلة طويل جداً (الحد الأقصى 255 حرف).',
            'family_name_ar.regex' => 'اسم العائلة يجب أن يحتوي على أحرف عربية فقط.',

            'date_of_birth.required' => 'تاريخ الميلاد مطلوب لتحديد الصف الدراسي المناسب.',
            'date_of_birth.date' => 'صيغة تاريخ الميلاد غير صحيحة.',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون تاريخاً سابقاً لليوم.',

            'gender.required' => 'يرجى تحديد جنس الطالب.',
            'gender.in' => 'الجنس المختار غير صالح.',

            'nationality_id.exists' => 'الجنسية المختارة غير موجودة في النظام.',

            'blood_type.string' => 'فصيلة الدم غير صالحة.',
            'blood_type.max' => 'فصيلة الدم طويلة جداً.',

            'national_id.unique' => 'الرقم القومي هذا مسجل بالفعل لطالب آخر.',
            'national_id.max' => 'الرقم القومي يجب ألا يتجاوز 20 رقماً.',
            'photo.image' => 'ملف الصورة يجب أن يكون صورة.',
            'photo.mimes' => 'الصورة يجب أن تكون بصيغة JPG أو JPEG أو PNG.',
            'photo.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',

            // Step 2: Guardian Info
            'guardian_first_name.required' => 'يرجى إدخال الاسم الأول لولي الأمر.',
            'guardian_last_name.required' => 'يرجى إدخال اسم العائلة لولي الأمر.',
            'guardian_phone.required' => 'رقم الهاتف مطلوب للتواصل.',
            'guardian_phone.unique' => 'رقم الهاتف هذا مسجل بالفعل في النظام.',
            'guardian_national_id.unique' => 'الرقم القومي لولي الأمر مسجل بالفعل.',
            'guardian_nationality_id.exists' => 'جنسية ولي الأمر غير صالحة.',

            // Step 3: Academic Info
            'grade_id.required' => 'يرجى اختيار الصف الدراسي.',
            'grade_id.exists' => 'الصف الدراسي المختار غير صحيح.',
            'class_section_id.exists' => 'الشعبة المختارة غير صحيحة.',

            // Step 4: Address
            'city.required' => 'يرجى إدخال اسم المدينة.',
            'district.required' => 'يرجى إدخال اسم الحي.',
            'street_name.required' => 'يرجى إدخال اسم الشارع.',
            'building_number.max' => 'رقم المبنى طويل جداً.',
            'google_maps_link.url' => 'رابط الموقع الجغرافي غير صالح. يرجى التأكد من نسخ الرابط الصحيح من خرائط جوجل.',

            // Step 4: Transfer Student
            'school_name.required' => 'اسم المدرسة السابقة مطلوب للطلاب المحولين.',
            'previous_curriculum.required' => 'المنهج الدراسي السابق مطلوب.',
            'last_grade_completed.required' => 'آخر صف دراسي تم إتمامه مطلوب.',
            'completion_year.required' => 'سنة الإكمال مطلوبة.',
            'completion_year.integer' => 'سنة الإكمال يجب أن تكون رقماً.',
            'completion_year.min' => 'سنة الإكمال يجب أن تكون بعد عام 2000.',
            'completion_year.max' => 'سنة الإكمال لا يمكن أن تكون في المستقبل.',
            'last_gpa.numeric' => 'المعدل التراكمي يجب أن يكون رقماً.',
            'last_gpa.min' => 'المعدل التراكمي لا يمكن أن يكون أقل من 0.',
            'last_gpa.max' => 'المعدل التراكمي لا يمكن أن يزيد عن 100.',
            'reason_for_transfer.max' => 'سبب النقل طويل جداً.',
        ];
    }

    /* ---------------------------------------------
     |   Custom Logic
     --------------------------------------------- */

    public function validateAgeForGrade()
    {
        if (!$this->grade_id || !$this->date_of_birth) {
            return true;
        }

        $grade = Grade::find($this->grade_id);
        if (!$grade || !$grade->min_age || !$grade->max_age) {
            return true;
        }

        $age = Carbon::parse($this->date_of_birth)->age;

        if ($age < $grade->min_age || $age > $grade->max_age) {
            $this->addError('date_of_birth', "عمر الطالب ($age سنة) غير مناسب. المطلوب بين {$grade->min_age} و {$grade->max_age} سنة.");
            return false;
        }

        return true;
    }

    public function validateGuardians($guardians)
    {
        if (empty($guardians)) {
            $this->addError('guardians', 'يجب إضافة ولي أمر واحد على الأقل.');
            return false;
        }

        $sponsorCount = collect($guardians)->where('is_financial_sponsor', true)->count();

        if ($sponsorCount === 0) {
            $this->addError('guardians', 'يجب تحديد مسؤول مالي واحد.');
            return false;
        }

        if ($sponsorCount > 1) {
            $this->addError('guardians', 'لا يمكن تحديد أكثر من مسؤول مالي واحد.');
            return false;
        }

        return true;
    }

    public function validateDocument($file)
    {
        $validator = Validator::make(
            ['file' => $file],
            ['file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'],
            [
                'file.required' => 'الملف مطلوب.',
                'file.file' => 'الملف المرفق غير صالح.',
                'file.mimes' => 'نوع الملف غير مدعوم. المسموح: PDF, JPG, PNG.',
                'file.max' => 'حجم الملف يجب ألا يتجاوز 2 ميجابايت.',
            ]
        );

        if ($validator->fails()) {
            $this->addError('documents', $validator->errors()->first('file'));
            return false;
        }

        return true;
    }

    public function validateGuardianData($data, $isNew = true)
    {
        $rules = [
            'relationship' => ['required', Rule::enum(GuardianRelationship::class)],
        ];

        if ($isNew) {
            $rules = array_merge($rules, [
                'guardian_first_name' => 'required|string|max:255',
                'guardian_last_name' => 'required|string|max:255',
                'guardian_national_id' => 'nullable|unique:guardians,national_id',
                'guardian_phone' => 'required|unique:guardians,phone',
                'guardian_nationality_id' => 'nullable|exists:countries,id',
            ]);
        } else {
            $rules['selectedGuardianId'] = 'required|exists:guardians,id';
        }

        $validator = Validator::make($data, $rules, [
            'relationship.required' => 'صلة القرابة مطلوبة.',
            'relationship.Illuminate\Validation\Rules\Enum' => 'صلة القرابة غير صالحة.',
            'guardian_first_name.required' => 'الاسم الأول مطلوب.',
            'guardian_last_name.required' => 'اسم العائلة مطلوب.',
            'guardian_phone.required' => 'رقم الهاتف مطلوب.',
            'guardian_phone.unique' => 'رقم الهاتف مسجل مسبقاً.',
            'guardian_national_id.unique' => 'رقم الهوية مسجل مسبقاً.',
            'selectedGuardianId.required' => 'يجب اختيار ولي أمر.',
            'selectedGuardianId.exists' => 'ولي الأمر المختار غير موجود.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->addError('guardian_validation', $error);
            }
            return false;
        }

        return true;
    }

    /* ---------------------------------------------
     |   Multi Step Validation (Fixed and Complete)
     --------------------------------------------- */

    public function validateStep($step, $isTransfer = false)
    {
        // dd($step);
        $rules = $this->rules();
        $messages = $this->messages();
        $stepRules = [];

        if ($step == 1) {
            $stepRules = [
                'first_name_ar' => $rules['first_name_ar'],
                'family_name_ar' => $rules['family_name_ar'],
                'date_of_birth' => $rules['date_of_birth'],
                'gender' => $rules['gender'],
                'nationality_id' => $rules['nationality_id'],
                'blood_type' => $rules['blood_type'],
                'national_id' => $rules['national_id'],
                'photo' => $rules['photo'],
            ];
        } elseif ($step == 2) {
            $stepRules = [
                'guardian_first_name' => $rules['guardian_first_name'],
                'guardian_last_name' => $rules['guardian_last_name'],
                'guardian_national_id' => $rules['guardian_national_id'],
                'guardian_phone' => $rules['guardian_phone'],
                'guardian_nationality_id' => $rules['guardian_nationality_id'],
                'guardian_preferred_language' => $rules['guardian_preferred_language'] ?? 'nullable',
            ];
        } elseif ($step == 3) {
            $stepRules = [
                'grade_id' => $rules['grade_id'],
                'class_section_id' => $rules['class_section_id'],
            ];
        } elseif ($step == 4) {
            // Step 4: Documents - optional, no required validation
            // المستندات اختيارية، لذا نسمح بتجاوز هذه المرحلة بدون validation إلزامي
            return true;
        } elseif ($step == 5) {
            // Step 5: Financials - optional, no required validation
            // المعلومات المالية اختيارية، يمكن تحديد رسوم أو تركها فارغة
            return true;
        }

        if (!empty($stepRules)) {
            $this->validate($stepRules, $messages);
        }

        // Custom validation for Step 3
        if ($step == 3) {
            return $this->validateAgeForGrade();
        }

        return true;
    }
}
