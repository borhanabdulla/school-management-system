<?php

namespace App\Domains\Academic\ClassSection\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Events\StructureChanged;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Infrastructure\Exceptions\CannotDeleteException;
use Illuminate\Support\Facades\DB;

class DeleteClassSectionAction
{
    public function __construct(
        protected ClassSectionLookupService $lookupService,
        protected AcademicWriteGuard $writeGuard
    ) {
    }

    public function execute(ClassSection $section): void
    {
        $this->writeGuard->assertYearNotClosed($section->academic_year_id);

        $blockers = $this->getDeletionBlockers($section);
        if (!empty($blockers)) {
            throw CannotDeleteException::forRelations('الشعبة', $blockers);
        }

        $gradeId = $section->grade_id;
        $yearId = $section->academic_year_id;

        $section->delete();

        $this->lookupService->invalidateCache($gradeId, $yearId);

        StructureChanged::dispatch(
            StructureChanged::TYPE_SECTION,
            StructureChanged::ACTION_DELETED,
            $section
        );
    }

    /**
     * Direct DB checks to avoid cascade data loss.
     *
     * @return array<string>
     */
    protected function getDeletionBlockers(ClassSection $section): array
    {
        $checks = [
            ['table' => 'students', 'column' => 'current_class_section_id', 'label' => 'طلاب'],
            ['table' => 'student_enrollments', 'column' => 'class_section_id', 'label' => 'تسجيلات طلاب'],
            ['table' => 'course_offerings', 'column' => 'class_section_id', 'label' => 'عروض مواد'],
            ['table' => 'timetables', 'column' => 'class_section_id', 'label' => 'جداول الحصص'],
            ['table' => 'attendances', 'column' => 'class_section_id', 'label' => 'سجلات حضور'],
            ['table' => 'promotions', 'column' => 'to_class_section_id', 'label' => 'قرارات ترحيل'],
        ];

        $blockers = [];
        foreach ($checks as $check) {
            $count = DB::table($check['table'])
                ->where($check['column'], $section->id)
                ->count();

            if ($count > 0) {
                $blockers[] = "{$check['label']} ({$count})";
            }
        }

        return $blockers;
    }
}
