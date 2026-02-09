<?php

namespace App\Domains\Academic\Control\Services;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Domains\Academic\Student\Services\StudentLookupService;

class SecrecyService
{
    public function __construct(
        protected StudentLookupService $studentLookup
    ) {
    }
    /**
     * توليد أرقام الجلوس والأرقام السرية لمجموعة طلاب
     *
     * @param ExamSession $session الدورة الامتحانية
     * @param Collection|array $studentIds قائمة معرفات الطلاب
     * @param int $seatStartFrom بداية أرقام الجلوس (مثال: 1000)
     * @return Collection<ExamSeating>
     */
    public function generateNumbers(
        ExamSession $session,
        Collection|array $studentIds,
        int $seatStartFrom = 1
    ): Collection {
        $studentIds = collect($studentIds);
        $generated = collect();

        DB::transaction(function () use ($session, $studentIds, $seatStartFrom, &$generated) {
            $seatNumber = $seatStartFrom;
            $usedSecrets = $this->getExistingSecretNumbers($session->id);

            foreach ($studentIds as $studentId) {
                // تخطي إذا كان الطالب موجوداً مسبقاً
                if ($this->studentExists($session->id, $studentId)) {
                    continue;
                }

                // توليد رقم سري فريد
                $secretNumber = $this->generateUniqueSecretNumber($usedSecrets);
                $usedSecrets[] = $secretNumber;

                $seating = ExamSeating::create([
                    'exam_session_id' => $session->id,
                    'student_id' => $studentId,
                    'seat_number' => (string) $seatNumber,
                    'secret_number' => $secretNumber,
                ]);

                $generated->push($seating);
                $seatNumber++;
            }
        });

        return $generated;
    }

    /**
     * توليد أرقام لجميع طلاب صف دراسي معين
     */
    public function generateForClassSection(
        ExamSession $session,
        ClassSection $classSection,
        int $seatStartFrom = 1
    ): Collection {
        $studentIds = $classSection->students()->pluck('students.id');
        return $this->generateNumbers($session, $studentIds, $seatStartFrom);
    }

    /**
     * توليد أرقام لجميع طلاب الدورة (كل الصفوف المرتبطة بالترم)
     */
    public function generateForSession(ExamSession $session, int $seatStartFrom = 1): Collection
    {
        // جلب جميع الطلاب المسجلين في السنة الدراسية الحالية
        // جلب جميع الطلاب المسجلين في السنة الدراسية الحالية
        $studentIds = $this->studentLookup->studentsActiveEnrolledInYear($session->academic_year_id)
            ->pluck('id');

        return $this->generateNumbers($session, $studentIds, $seatStartFrom);
    }

    /**
     * توليد رقم سري فريد (6 أحرف/أرقام عشوائية)
     */
    protected function generateUniqueSecretNumber(array $existingNumbers): string
    {
        do {
            // صيغة الرقم السري: 2 حرف + 4 أرقام (مثال: AB1234)
            $secret = strtoupper(Str::random(2)) . rand(1000, 9999);
        } while (in_array($secret, $existingNumbers));

        return $secret;
    }

    /**
     * جلب الأرقام السرية المستخدمة في دورة معينة
     */
    protected function getExistingSecretNumbers(int $sessionId): array
    {
        return ExamSeating::where('exam_session_id', $sessionId)
            ->pluck('secret_number')
            ->toArray();
    }

    /**
     * التحقق من وجود طالب في الدورة
     */
    protected function studentExists(int $sessionId, int $studentId): bool
    {
        return ExamSeating::where('exam_session_id', $sessionId)
            ->where('student_id', $studentId)
            ->exists();
    }

    /**
     * إعادة توليد الأرقام السرية (حالة طوارئ)
     * ⚠️ خطير - يجب استخدامه فقط قبل بدء الامتحانات
     */
    public function regenerateSecretNumbers(ExamSession $session): int
    {
        if (!$session->isSetup()) {
            throw new \App\Domains\Academic\Control\Exceptions\CannotRegenerateSecretsException();
        }

        $seatings = $session->seatings;
        $usedSecrets = [];
        $count = 0;

        DB::transaction(function () use ($seatings, &$usedSecrets, &$count) {
            foreach ($seatings as $seating) {
                $newSecret = $this->generateUniqueSecretNumber($usedSecrets);
                $usedSecrets[] = $newSecret;

                $seating->update(['secret_number' => $newSecret]);
                $count++;
            }
        });

        return $count;
    }
}
