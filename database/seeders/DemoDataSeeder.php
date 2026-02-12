<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Shared\Models\User;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\HR\Enums\StaffRole;
use App\Domains\Shared\Enums\Gender;
use App\Domains\Academic\Student\Enums\StudentStatus;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Domains\Academic\AcademicYear\Actions\CreateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\ActivateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Data\AcademicYearData;
use App\Domains\Academic\ClassSection\Actions\CreateClassSectionAction;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use App\Domains\HR\Staff\Actions\CreateStaffAction;
use App\Domains\HR\Staff\Data\StaffOnboardingData;
use App\Domains\Academic\Subject\Actions\CreateSubjectAction;
use App\Domains\Academic\Subject\Data\SubjectData;
use App\Domains\Academic\Subject\Actions\AssignSubjectToGradeAction;
use App\Domains\Academic\Subject\Data\SubjectAssignmentData;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\CourseOffering\Actions\CreateCourseOfferingAction;
use App\Domains\Academic\CourseOffering\Data\CourseOfferingData;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\Student\Actions\RegisterStudentAction;
use App\Domains\Academic\Student\Data\StudentRegistrationData;
use App\Domains\Academic\Timetable\Actions\CreateTimetableTemplateAction;
use App\Domains\Academic\Timetable\Data\TimetableTemplateData;
use App\Domains\Academic\Timetable\Data\TimeSlotData;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Domains\Academic\Timetable\Enums\TimeSlotType;
use App\Domains\Academic\Timetable\Actions\AssignSessionAction;
use App\Domains\Shared\Enums\DayOfWeek;
use App\Domains\Academic\Grading\Actions\CreateGradingTemplateAction;
use App\Domains\Academic\Grading\Actions\ApplyTemplateToGradeAction;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Attendance\Enums\AttendanceResponsibility;
use Carbon\Carbon;

/**
 * DemoDataSeeder - بذور البيانات الوهمية الشاملة
 * 
 * يستخدم نفس Actions النظام لضمان صحة البيانات ومنطق الأعمال
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 بدء إنشاء البيانات الوهمية باستخدام Actions النظام...');
        $this->command->newLine();

        DB::transaction(function () {
            // ═══════════════════════════════════════════════════════════════
            // 1. السنة الدراسية والترمات (CreateAcademicYearAction)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('📅 إنشاء السنة الدراسية...');

            // تحقق من وجود سنة دراسية بنفس الاسم لتجنب التكرار
            if (AcademicYear::where('name', '2025-2026')->exists()) {
                $this->command->warn('⚠️ السنة الدراسية 2025-2026 موجودة بالفعل.');
                $academicYear = AcademicYear::where('name', '2025-2026')->first();

                // تحديث الإعدادات حتى لو السنة موجودة
                AttendanceSetting::updateOrCreate(
                    ['academic_year_id' => $academicYear->id],
                    [
                        'mode' => AttendanceMode::Checkpoints,
                        'responsible_role' => AttendanceResponsibility::HomeroomTeacher,
                        'late_tolerance' => 15
                    ]
                );
                $this->command->info("   ✅ تم تحديث إعدادات الحضور (مربي الفصل + نقاط تفتيش)");
            } else {
                $academicYearData = new AcademicYearData(
                    name: '2025-2026',
                    start_date: Carbon::parse('2025-09-01'),
                    end_date: Carbon::parse('2026-06-30'),
                    status: AcademicYearStatus::Pending, // نبدأ كمسودة ثم نفعلها
                    terms: [
                        [
                            'name' => 'الترم الأول',
                            'start_date' => '2025-09-01',
                            'end_date' => '2026-01-15',
                            'order_index' => 1
                        ],
                        [
                            'name' => 'الترم الثاني',
                            'start_date' => '2026-01-20',
                            'end_date' => '2026-06-30',
                            'order_index' => 2
                        ]
                    ]
                );

                // نستخدم Action النظام لإنشاء السنة والترمات
                // هذا يضمن تشغيل Validation المنطقي (تداخل التواريخ، إلخ)
                $academicYear = app(CreateAcademicYearAction::class)->execute($academicYearData);

                // تفعيل السنة (يجب أن يكون عبر Action منفصل كما في النظام)
                app(ActivateAcademicYearAction::class)->execute($academicYear);
                $this->command->info("   ✅ السنة: {$academicYear->name} (تم تفعيلها)");

                // إعدادات الحضور (نقاط تفتيش - مسؤولية مربي الفصل)
                AttendanceSetting::updateOrCreate(
                    ['academic_year_id' => $academicYear->id],
                    [
                        'mode' => AttendanceMode::Checkpoints,
                        'responsible_role' => AttendanceResponsibility::HomeroomTeacher,
                        'late_tolerance' => 15
                    ]
                );
                $this->command->info("   ✅ تم ضبط إعدادات الحضور: مربي الفصل + نقاط تفتيش (ح 1 و 4)");
            }

            // ═══════════════════════════════════════════════════════════════
            // 2. المراحل والصفوف (لا يوجد Actions خاصة، نستخدم Models)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('🎓 إنشاء الهيكل التعليمي (مراحل وصفوف)...');

            $primaryStage = EducationalStage::firstOrCreate(
                ['name' => 'المرحلة الابتدائية'],
                ['rank' => 1, 'min_passing_percentage' => 50, 'grading_system' => 'standard']
            );

            $middleStage = EducationalStage::firstOrCreate(
                ['name' => 'المرحلة المتوسطة'],
                ['rank' => 2, 'min_passing_percentage' => 50, 'grading_system' => 'standard']
            );

            $grades = [];

            // ابتدائي
            foreach (['الأول', 'الثاني', 'الثالث', 'الرابع', 'الخامس'] as $index => $name) {
                $grades[] = Grade::firstOrCreate(
                    ['educational_stage_id' => $primaryStage->id, 'name' => "الصف {$name}"],
                    ['level_order' => $index + 1, 'min_age' => 6 + $index, 'max_age' => 7 + $index]
                );
            }

            // متوسط
            foreach (['السادس', 'السابع', 'الثامن'] as $index => $name) {
                $grades[] = Grade::firstOrCreate(
                    ['educational_stage_id' => $middleStage->id, 'name' => "الصف {$name}"],
                    ['level_order' => $index + 6, 'min_age' => 11 + $index, 'max_age' => 12 + $index]
                );
            }

            // ═══════════════════════════════════════════════════════════════
            // 2.1. المواد الدراسية والمنهج (CreateSubjectAction & AssignSubjectToGradeAction)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('📚 إنشاء المواد الدراسية وتعيين المناهج...');

            $subjectsList = [
                ['name' => 'القرآن الكريم', 'code' => 'ISL-QR', 'type' => 'theory'],
                ['name' => 'التربية الإسلامية', 'code' => 'ISL-ST', 'type' => 'theory'],
                ['name' => 'اللغة العربية', 'code' => 'ARB', 'type' => 'theory'],
                ['name' => 'الرياضيات', 'code' => 'MATH', 'type' => 'theory'],
                ['name' => 'العلوم', 'code' => 'SCI', 'type' => 'theory'],
                ['name' => 'اللغة الإنجليزية', 'code' => 'ENG', 'type' => 'theory'],
                ['name' => 'الدراسات الاجتماعية', 'code' => 'SOC', 'type' => 'theory'],
                ['name' => 'الحاسب الآلي', 'code' => 'COMP', 'type' => 'both'],
                ['name' => 'التربية الفنية', 'code' => 'ART', 'type' => 'practical'],
                ['name' => 'التربية البدنية', 'code' => 'PE', 'type' => 'practical'],
            ];

            $createdSubjects = [];
            foreach ($subjectsList as $sub) {
                $subjectData = new SubjectData(
                    name: $sub['name'],
                    code: $sub['code'],
                    type: $sub['type']
                );
                // نستخدم firstOrCreate عبر الموديل مباشرة أو check قبل Action لتجنب التكرار
                $subject = Subject::firstOrCreate(
                    ['code' => $sub['code']],
                    ['name' => $sub['name'], 'type' => $sub['type']]
                );
                $createdSubjects[$sub['code']] = $subject;
                $this->command->info("   📖 مادة: {$subject->name}");
            }

            // تعيين المواد للصفوف (المنهج)
            foreach ($grades as $grade) {
                // مواد مشتركة للجميع
                $commonSubjects = ['ISL-QR', 'ISL-ST', 'ARB', 'MATH', 'ART', 'PE'];

                // مواد إضافية حسب المرحلة
                if ($grade->educational_stage_id == $middleStage->id) {
                    // المرحلة المتوسطة
                    $gradeSubjects = array_merge($commonSubjects, ['SCI', 'ENG', 'SOC', 'COMP']);
                } else {
                    // المرحلة الابتدائية
                    if ($grade->level_order >= 4) { // الصف الرابع والخامس
                        $gradeSubjects = array_merge($commonSubjects, ['SCI', 'ENG', 'SOC']);
                    } else { // الصفوف الأولية
                        $gradeSubjects = array_merge($commonSubjects, ['SCI']);
                    }
                }

                foreach ($gradeSubjects as $code) {
                    if (!isset($createdSubjects[$code]))
                        continue;

                    $assignmentData = new SubjectAssignmentData(
                        subject_id: $createdSubjects[$code]->id,
                        credit_hours: 1,
                        term_type: 'full_year', // ممتدة طوال العام
                        is_active: true
                    );

                    try {
                        app(AssignSubjectToGradeAction::class)->execute($grade, $assignmentData);
                    } catch (\Exception $e) {
                        // تجاهل الخطأ إذا كانت المادة معينة مسبقاً
                    }
                }
                $this->command->info("   ✅ تم تعيين المنهج لـ: {$grade->name}");
            }


            // ═══════════════════════════════════════════════════════════════
            // 3. الشعب الدراسية (CreateClassSectionAction)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('🏫 إنشاء الشعب الدراسية...');

            $sections = [];
            foreach ($grades as $grade) {
                foreach (['أ', 'ب'] as $sectionName) {

                    // تحقق قبل الإنشاء
                    $existingSection = ClassSection::where('grade_id', $grade->id)
                        ->where('academic_year_id', $academicYear->id)
                        ->where('name', $sectionName)
                        ->first();

                    if ($existingSection) {
                        $this->command->warn("   ⚠️ الشعبة موجودة مسبقاً: {$grade->name} - {$sectionName}");
                        $sections[] = $existingSection;
                        continue;
                    }

                    $sectionData = new ClassSectionData(
                        name: $sectionName,
                        grade_id: $grade->id,
                        academic_year_id: $academicYear->id,
                        max_capacity: 30,
                        gender_type: SectionGenderType::Mixed,
                        is_active: true
                    );

                    // نستخدم Action النظام لإنشاء الشعبة
                    // هذا يضمن التحقق من عدم التكرار وقواعد العمل الأخرى
                    try {
                        $sections[] = app(CreateClassSectionAction::class)->execute($sectionData);
                        $this->command->info("   ✅ {$grade->name} - {$sectionName}");
                    } catch (\Exception $e) {
                        $this->command->warn("   ⚠️ خطأ في إنشاء الشعبة: {$e->getMessage()}");
                    }
                }
            }

            // ═══════════════════════════════════════════════════════════════
            // 4. المعلمين (CreateStaffAction)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('👨‍🏫 إنشاء المعلمين...');

            // تأكد من وجود الأدوار المطلوبة أولاً
            Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);

            for ($i = 1; $i <= 20; $i++) {
                $email = "teacher0{$i}@school.com";
                if (User::where('email', $email)->exists()) {
                    continue;
                }

                $staffData = new StaffOnboardingData(
                    first_name: "معلم",
                    last_name: "رقم {$i}",
                    email: $email,
                    phone: '05000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    joining_date: '2025-09-01',
                    role: StaffRole::Teacher,
                    job_title: 'معلم',
                    employment_type: 'full_time',
                    specialization: 'تخصص عام',
                    max_weekly_classes: 24,
                    create_account: true,
                    password: 'password'
                );

                // نستخدم Action النظام لإنشاء المعلم
                // هذا ينشئ User, Staff, Teacher ويربطهم ببعض ويرسل الإشعارات (التي نتجاهلها هنا)
                try {
                    app(CreateStaffAction::class)->execute($staffData);
                    $this->command->info("   • teacher0{$i}@school.com / password");
                } catch (\Exception $e) {
                    $this->command->error("   ❌ فشل إنشاء المعلم {$i}: " . $e->getMessage());
                }
            }

            // ═══════════════════════════════════════════════════════════════
            // 4.1. توزيع المنهج على الفصول (CreateCourseOfferingAction)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('📚 توزيع المنهج على الفصول (Course Offerings)...');

            $teachers = Teacher::all(); // جلب كل المعلمين للاختيار العشوائي

            foreach ($sections as $section) {
                // تعيين مربي الفصل (لأغراض الحضور)
                if (!$section->homeroom_teacher_id && $teachers->isNotEmpty()) {
                    $section->homeroom_teacher_id = $teachers->random()->id;
                    $section->save();
                }

                // جلب المواد المعينة لصف هذه الشعبة (المنهج)
                $gradeSubjects = $section->grade->subjects;

                foreach ($gradeSubjects as $subject) {
                    $teacher = $teachers->random(); // اختيار معلم عشوائي للمادة

                    // إنشاء CourseOffering لكل مادة في الشعبة
                    // بما أن المادة قد تكون سنوية، سنقوم بإنشائها للترمين (أو حسب منطق النظام)
                    // هنا سنفترض أننا ننشئ للترمين الموجودين في السنة
                    foreach ($academicYear->terms as $term) {
                        // تحقق من وجود العرض لمنع التكرار
                        $exists = \App\Domains\Academic\CourseOffering\Models\CourseOffering::where([
                            'academic_year_id' => $academicYear->id,
                            'term_id' => $term->id,
                            'class_section_id' => $section->id,
                            'subject_id' => $subject->id,
                        ])->exists();

                        if ($exists)
                            continue;

                        $offeringData = new CourseOfferingData(
                            academic_year_id: $academicYear->id,
                            term_id: $term->id,
                            class_section_id: $section->id,
                            subject_id: $subject->id,
                            teacher_id: $teacher->id
                        );

                        try {
                            app(CreateCourseOfferingAction::class)->execute($offeringData);
                        } catch (\Exception $e) {
                            $this->command->warn("   ⚠️ فشل إنشاء عرض المادة: {$subject->name} - {$section->name} ({$term->name})");
                        }
                    }
                }
                $this->command->info("   ✅ تم توزيع المواد لـ: {$section->name}");
            }

            // ═══════════════════════════════════════════════════════════════
            // 4.2. الجدول الدراسي (CreateTimetableTemplateAction & AssignSessionAction)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('📅 إعداد الجدول الدراسي...');

            // تعريف الحصص لليوم الدراسي
            $dailySlots = [
                ['label' => 'طابور الصباح', 'start' => '07:00', 'end' => '07:15', 'type' => TimeSlotType::Assembly],
                ['label' => 'الحصة الأولى', 'start' => '07:15', 'end' => '08:00', 'type' => TimeSlotType::Academic],
                ['label' => 'الحصة الثانية', 'start' => '08:00', 'end' => '08:45', 'type' => TimeSlotType::Academic],
                ['label' => 'فسحة الإفطار', 'start' => '08:45', 'end' => '09:15', 'type' => TimeSlotType::Break],
                ['label' => 'الحصة الثالثة', 'start' => '09:15', 'end' => '10:00', 'type' => TimeSlotType::Academic],
                ['label' => 'الحصة الرابعة', 'start' => '10:00', 'end' => '10:45', 'type' => TimeSlotType::Academic],
                ['label' => 'صلاة الظهر', 'start' => '10:45', 'end' => '11:15', 'type' => TimeSlotType::Prayer],
                ['label' => 'الحصة الخامسة', 'start' => '11:15', 'end' => '12:00', 'type' => TimeSlotType::Academic],
                ['label' => 'الحصة السادسة', 'start' => '12:00', 'end' => '12:45', 'type' => TimeSlotType::Academic],
            ];

            // تحويلها لـ TimeSlotData لكل يوم من أيام الأسبوع
            $templateSlots = [];
            foreach (DayOfWeek::schoolDays() as $day) {
                foreach ($dailySlots as $index => $slot) {
                    $templateSlots[] = new TimeSlotData(
                        id: null,
                        dayOfWeek: $day->value,
                        label: $slot['label'],
                        orderIndex: $index + 1,
                        startTime: $slot['start'],
                        endTime: $slot['end'],
                        type: $slot['type'],
                        isAttendanceCheckpoint: $slot['type'] === TimeSlotType::Academic && ($index === 1 || $index === 5) // الحصة الأولى والرابعة
                    );
                }
            }

            // إنشاء قالب للمرحلة الابتدائية
            $primaryTemplate = \App\Domains\Academic\Timetable\Models\TimetableTemplate::where('name', 'الدوام الصباحي - ابتدائي')
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if (!$primaryTemplate) {
                $primaryGradeIds = collect($grades)->where('educational_stage_id', $primaryStage->id)->pluck('id')->toArray();
                $primaryTemplateData = new TimetableTemplateData(
                    id: null,
                    name: 'الدوام الصباحي - ابتدائي',
                    description: 'الجدول القياسي للمرحلة الابتدائية',
                    workingDays: DayOfWeek::schoolDays(),
                    isDefault: true,
                    status: TemplateStatus::Active,
                    academicYearId: $academicYear->id,
                    educationalStageId: $primaryStage->id,
                    slots: $templateSlots,
                    gradeIds: $primaryGradeIds
                );
                $primaryTemplate = app(CreateTimetableTemplateAction::class)->execute($primaryTemplateData);
                $this->command->info("   ✅ تم إنشاء قالب الجدول الابتدائي");
            } else {
                $this->command->warn("   ⚠️ قالب الجدول الابتدائي موجود مسبقاً");
            }

            // إنشاء قالب للمرحلة المتوسطة
            $middleTemplate = \App\Domains\Academic\Timetable\Models\TimetableTemplate::where('name', 'الدوام الصباحي - متوسط')
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if (!$middleTemplate) {
                $middleGradeIds = collect($grades)->where('educational_stage_id', $middleStage->id)->pluck('id')->toArray();
                $middleTemplateData = new TimetableTemplateData(
                    id: null,
                    name: 'الدوام الصباحي - متوسط',
                    description: 'الجدول القياسي للمرحلة المتوسطة',
                    workingDays: DayOfWeek::schoolDays(),
                    isDefault: true,
                    status: TemplateStatus::Active,
                    academicYearId: $academicYear->id,
                    educationalStageId: $middleStage->id,
                    slots: $templateSlots,
                    gradeIds: $middleGradeIds
                );
                $middleTemplate = app(CreateTimetableTemplateAction::class)->execute($middleTemplateData);
                $this->command->info("   ✅ تم إنشاء قالب الجدول المتوسط");
            } else {
                $this->command->warn("   ⚠️ قالب الجدول المتوسط موجود مسبقاً");
            }

            // تعبئة الجدول بالحصص
            $this->command->info("   ⏳ جاري تعبئة جداول الحصص...");

            foreach ($sections as $section) {
                // تحديد القالب المناسب
                $template = $section->grade->educational_stage_id === $primaryStage->id ? $primaryTemplate : $middleTemplate;

                // جلب المواد المتاحة للشعبة (Course Offerings) للترم الأول كمثال
                // ملاحظة: الجدول يتم إنشاؤه لكل ترم على حدة
                foreach ($academicYear->terms as $term) {
                    $offerings = \App\Domains\Academic\CourseOffering\Models\CourseOffering::where([
                        'academic_year_id' => $academicYear->id,
                        'term_id' => $term->id,
                        'class_section_id' => $section->id,
                    ])->get();

                    if ($offerings->isEmpty())
                        continue;

                    // الحصص الدراسية في القالب
                    $academicSlots = $template->timeSlots->where('type', TimeSlotType::Academic);

                    foreach ($academicSlots as $slot) {
                        // اختيار مادة عشوائية (مع تكرار بسيط لمحاكاة الواقع)
                        // في الواقع، يجب مراعاة نصاب المعلم وتضارب الحصص، لكن للـ Seeder العشوائية مقبولة
                        $offering = $offerings->random();

                        try {
                            app(AssignSessionAction::class)->execute(
                                yearId: $academicYear->id,
                                termId: $term->id,
                                sectionId: $section->id,
                                subjectId: $offering->subject_id,
                                teacherId: $offering->teacher_id,
                                slotId: $slot->id
                            );
                        } catch (\Exception $e) {
                            // قد يحدث تضارب أو خطأ، نتجاوزه في الـ demo
                        }
                    }
                }
                $this->command->info("   ✅ تم إنشاء جدول: {$section->grade->name} - {$section->name}");
            }

            // ═══════════════════════════════════════════════════════════════
            // 4.3. قوالب الدرجات (CreateGradingTemplateAction & ApplyTemplateToGradeAction)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('📊 إعداد قوالب الدرجات (النظام الموحد 50/50)...');

            // التحقق من وجود القالب مسبقاً
            $stdTemplate = \App\Domains\Academic\Grading\Models\GradingTemplate::where('name', 'القالب الموحد (50 درجة)')
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if (!$stdTemplate) {
                $stdGradingData = [
                    'name' => 'القالب الموحد (50 درجة)',
                    'total_max_score' => 50, // 50 درجة لكل ترم
                    'pass_score' => 25,
                    'academic_year_id' => $academicYear->id,
                ];

                // هيكلة الدرجات: 29 نهائي (58%) + 21 أعمال سنة (42%)
                // أعمال السنة مقسمة على 3 أشهر
                $stdCategories = [
                    // 1. الاختبار النهائي (29 درجة)
                    [
                        'name' => 'اختبار نهاية الفصل',
                        'weight' => 58, // 58% من 50 = 29 درجة
                        'order' => 2,
                        'is_locked' => true,
                    ],
                    // 2. أعمال السنة (21 درجة)
                    [
                        'name' => 'أعمال السنة',
                        'weight' => 42, // 42% من 50 = 21 درجة
                        'order' => 1,
                        'children' => [
                            // الشهر الأول (35% من 21 ≈ 7.35 درجة)
                            [
                                'name' => 'الشهر الأول',
                                'weight' => 35,
                                'order' => 1,
                                'children' => [
                                    ['name' => 'واجبات', 'weight' => 20, 'order' => 1],
                                    ['name' => 'مشاركة', 'weight' => 20, 'order' => 2],
                                    ['name' => 'اختبار قصير', 'weight' => 60, 'order' => 3],
                                ]
                            ],
                            // الشهر الثاني (35% من 21 ≈ 7.35 درجة)
                            [
                                'name' => 'الشهر الثاني',
                                'weight' => 35,
                                'order' => 2,
                                'children' => [
                                    ['name' => 'واجبات', 'weight' => 20, 'order' => 1],
                                    ['name' => 'مشاركة', 'weight' => 20, 'order' => 2],
                                    ['name' => 'اختبار قصير', 'weight' => 60, 'order' => 3],
                                ]
                            ],
                            // الشهر الثالث (30% من 21 ≈ 6.3 درجة)
                            [
                                'name' => 'الشهر الثالث',
                                'weight' => 30,
                                'order' => 3,
                                'children' => [
                                    ['name' => 'واجبات', 'weight' => 20, 'order' => 1],
                                    ['name' => 'مشاركة', 'weight' => 20, 'order' => 2],
                                    ['name' => 'اختبار قصير', 'weight' => 60, 'order' => 3],
                                ]
                            ],
                        ]
                    ],
                ];

                $stdTemplate = app(CreateGradingTemplateAction::class)->execute($stdGradingData, $stdCategories);
                $this->command->info("   ✅ تم إنشاء القالب الموحد");
            } else {
                $this->command->warn("   ⚠️ القالب الموحد موجود مسبقاً");
            }

            // تطبيق القالب على جميع الصفوف (ابتدائي ومتوسط) لكل الترمات
            $this->command->info("   ⏳ جاري تطبيق القالب على المواد...");

            foreach ($academicYear->terms as $term) {
                // تطبيق للمرحلتين بنفس القالب
                app(ApplyTemplateToGradeAction::class)->executeForStage(
                    template: $stdTemplate,
                    stageId: $primaryStage->id,
                    term: $term,
                    options: ['max_score' => 50, 'pass_score' => 25]
                );

                app(ApplyTemplateToGradeAction::class)->executeForStage(
                    template: $stdTemplate,
                    stageId: $middleStage->id,
                    term: $term,
                    options: ['max_score' => 50, 'pass_score' => 25]
                );
            }
            $this->command->info("   ✅ تم تعيين القالب لجميع الصفوف (ترم 1 وترم 2)");


            // ═══════════════════════════════════════════════════════════════
            // 5. الطلاب (RegisterStudentAction)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('👨‍🎓 إنشاء الطلاب وتسجيلهم...');

            $studentCounter = 1;
            foreach ($sections as $section) {
                // فلننشئ طالبين لكل شعبة كبداية لعدم إغراق النظام
                for ($j = 1; $j <= 2; $j++) {
                    $nationalId = '100000' . str_pad($studentCounter, 4, '0', STR_PAD_LEFT);

                    if (Student::where('national_id', $nationalId)->exists()) {
                        $studentCounter++;
                        continue;
                    }

                    // بيانات الطالب - تحاكي تماماً البيانات القادمة من فورم التسجيل الحي
                    $studentData = new StudentRegistrationData(
                        student: [
                            'first_name_ar' => "طالب",
                            'family_name_ar' => "رقم {$studentCounter} ({$section->grade->name})",
                            'date_of_birth' => now()->subYears($section->grade->min_age)->subMonths(rand(0, 11))->format('Y-m-d'), // عمر مناسب للصف
                            'gender' => $j % 2 == 0 ? Gender::Male : Gender::Female,
                            'national_id' => $nationalId,
                            'blood_type' => 'O+',
                            'nationality_id' => 1, // سعودي افتراضياً
                        ],
                        guardians: [
                            [
                                'data' => [
                                    'first_name' => "ولي أمر",
                                    'last_name' => "الطالب {$studentCounter}",
                                    'relationship' => 'father',
                                    'phone' => '055555' . str_pad($studentCounter, 4, '0', STR_PAD_LEFT),
                                    'national_id' => '200000' . str_pad($studentCounter, 4, '0', STR_PAD_LEFT),
                                ],
                                'relationship' => 'father',
                                'is_financial_sponsor' => true,
                                'is_emergency_contact' => true,
                                'lives_with' => true,
                                'has_portal_access' => true
                            ]
                        ],
                        grade_id: $section->grade_id,
                        class_section_id: $section->id, // نحدد الشعبة هنا ليقوم النظام بتسكينه فيها
                        health_data: [],
                        address: [
                            'city' => 'الرياض',
                            'district' => 'حي النزهة',
                            'street_name' => 'شارع عثمان بن عفان',
                            'building_number' => '123'
                        ],
                        documents: [],
                        photo: null,
                        is_transfer: false,
                        previous_history: null,
                        create_invoice: true // إنشاء فاتورة
                    );

                    // نستخدم RegisterStudentAction
                    // هذا سيقوم بكل شيء: إنشاء الطالب، الولي، العنوان، التسجيل في السنة، التسكين في الشعبة، إنشاء الفاتورة
                    try {
                        app(RegisterStudentAction::class)->execute($studentData);
                        $this->command->info("   ✅ طالب {$studentCounter} -> {$section->grade->name} - {$section->name}");
                    } catch (\Exception $e) {
                        $this->command->error("   ❌ فشل تسجيل الطالب {$studentCounter}: " . $e->getMessage());
                    }

                    $studentCounter++;
                }
            }

            // ═══════════════════════════════════════════════════════════════
            // 6. محاكاة أنشطة المعلم الأول (Teacher 01 Simulation)
            // ═══════════════════════════════════════════════════════════════
            $this->command->info('👨‍🏫 محاكاة أنشطة المعلم الأول (teacher01)...');

            $teacherUser = User::where('email', 'teacher01@school.com')->first();
            $teacher = $teacherUser?->staff?->teacher;

            if ($teacher) {
                // ضمان وجود شعبة تحت مسؤولية المعلم (Homeroom)
                $homeroomSection = ClassSection::where('homeroom_teacher_id', $teacher->id)->first();


                if ($homeroomSection) {
                    $this->command->info("   📅 تسجيل الحضور للشعبة: {$homeroomSection->full_name}");

                    // محاكاة يوم دراسي (اليوم)
                    $today = Carbon::today()->format('Y-m-d');
                    // الحصة الأولى (نقطة تفتيش)
                    $firstSlot = \App\Domains\Academic\Timetable\Models\TimeSlot::where('type', TimeSlotType::Academic)
                        ->where('order_index', 1)
                        ->first();

                    if ($firstSlot) {
                        foreach ($homeroomSection->students as $index => $student) {
                            // تنويع الحضور: 80% حاضر، 10% غائب، 10% متأخر
                            $status = match (true) {
                                $index % 10 == 0 => \App\Domains\Academic\Attendance\Enums\AttendanceStatus::ABSENT,
                                $index % 10 == 1 => \App\Domains\Academic\Attendance\Enums\AttendanceStatus::LATE,
                                default => \App\Domains\Academic\Attendance\Enums\AttendanceStatus::PRESENT
                            };

                            \App\Domains\Academic\Attendance\Models\Attendance::firstOrCreate([
                                'student_id' => $student->id,
                                'class_section_id' => $homeroomSection->id,
                                'date' => $today,
                                'time_slot_id' => $firstSlot->id,
                            ], [
                                'academic_year_id' => $academicYear->id,
                                'term_id' => $academicYear->terms->first()->id,
                                'status' => $status,
                                'recorded_by' => $teacherUser->id,
                                'delay_minutes' => $status === \App\Domains\Academic\Attendance\Enums\AttendanceStatus::LATE ? rand(5, 15) : 0,
                            ]);
                        }
                        $this->command->info("   ✅ تم رصد الحضور للحصة الأولى");
                    }
                }

                // 2. إدارة المواد (واجبات ودرجات)
                $offering = \App\Domains\Academic\CourseOffering\Models\CourseOffering::where('teacher_id', $teacher->id)->first();



                if ($offering) {
                    $this->command->info("   📚 إدارة المادة: {$offering->subject->name} - {$offering->classSection->full_name}");

                    // أ. إنشاء واجب منزلي
                    \App\Domains\Academic\Homework\Models\Homework::firstOrCreate([
                        'title' => 'واجب تطبيقي رقم 1',
                        'course_offering_id' => $offering->id
                    ], [
                        'description' => 'يرجى حل الأسئلة في الصفحة 20 وتسليمها.',
                        'status' => \App\Domains\Academic\Homework\Enums\HomeworkStatus::PUBLISHED,
                        'submission_type' => \App\Domains\Academic\Homework\Enums\SubmissionType::ONLINE,
                        'due_date' => Carbon::today()->addDays(3),
                        'max_score' => 10,
                        'allow_late' => true,
                    ]);
                    $this->command->info("   ✅ تم نشر واجب منزلي");

                    // ب. رصد درجات (لشهر وهمي)
                    // نأخذ قالب الدرجات المطبق
                    $template = $offering->grade?->gradingTemplates()->first();
                    // في حالتنا القالب مطبق على الـ Grade وليس CourseOffering مباشرة (حسب ApplyTemplateToGradeAction)

                    // سنقوم بإنشاء شهر تقييم إذا لم يوجد
                    $month = \App\Domains\Academic\Grading\Models\GradebookMonth::firstOrCreate([
                        'name' => 'سبتمبر',
                        'term_id' => $offering->term_id,
                        'academic_year_id' => $academicYear->id,
                    ], [
                        'start_date' => '2025-09-01',
                        'end_date' => '2025-09-30',
                        'is_active' => true,
                        'month_order' => 1
                    ]);

                    // رصد درجات لطلاب الشعبة
                    foreach ($offering->classSection->students as $student) {
                        try {
                            app(\App\Domains\Academic\Grading\Actions\RecordMonthlyGradeAction::class)->execute(
                                offering: $offering,
                                studentId: $student->id,
                                monthId: $month->id,
                                categoryKey: 'hw_1', // مفتاح فريد للفئة/النشاط
                                score: rand(8, 10), // درجة عشوائية ممتازة
                                maxScore: 10,
                                categoryLabel: 'الواجب الأول',
                                gradedByUserId: $teacherUser->id
                            );
                        } catch (\Exception $e) {
                            // تجاهل الأخطاء البسيطة في المحاكاة
                        }
                    }
                    $this->command->info("   ✅ تم رصد درجات شهرية للطلاب");
                }
            }

        });

        $this->command->info('✅ تمت العملية بنجاح باستخدام منطق النظام الأصلي!');
    }
}
