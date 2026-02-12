<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

final class GradeScaleValidator
{
    /**
     * @return array<int, array{grade: string, min: int, max: int, color: string}>
     */
    public static function defaultScale(): array
    {
        return [
            ['grade' => 'A+', 'min' => 95, 'max' => 100, 'color' => '#22c55e'],
            ['grade' => 'A', 'min' => 90, 'max' => 95, 'color' => '#22c55e'],
            ['grade' => 'B+', 'min' => 85, 'max' => 90, 'color' => '#3b82f6'],
            ['grade' => 'B', 'min' => 80, 'max' => 85, 'color' => '#3b82f6'],
            ['grade' => 'C+', 'min' => 75, 'max' => 80, 'color' => '#eab308'],
            ['grade' => 'C', 'min' => 70, 'max' => 75, 'color' => '#eab308'],
            ['grade' => 'D+', 'min' => 65, 'max' => 70, 'color' => '#f97316'],
            ['grade' => 'D', 'min' => 60, 'max' => 65, 'color' => '#f97316'],
            ['grade' => 'F', 'min' => 0, 'max' => 60, 'color' => '#ef4444'],
        ];
    }

    /**
     * @param array<int, mixed> $scale
     * @return array<int, string>
     */
    public function validate(array $scale): array
    {
        $issues = [];

        if ($scale === []) {
            return ['سلم التقديرات فارغ.'];
        }

        $entries = [];

        foreach ($scale as $index => $entry) {
            if (! is_array($entry)) {
                $issues[] = "السطر رقم {$index} غير صالح.";
                continue;
            }

            if (! array_key_exists('min', $entry) || ! array_key_exists('max', $entry) || ! array_key_exists('grade', $entry)) {
                $issues[] = "السطر رقم {$index} يجب أن يحتوي على min/max/grade.";
                continue;
            }

            if (! is_numeric($entry['min']) || ! is_numeric($entry['max'])) {
                $issues[] = "السطر رقم {$index} يجب أن يحتوي على قيم رقمية لـ min/max.";
                continue;
            }

            $min = (float) $entry['min'];
            $max = (float) $entry['max'];
            $grade = trim((string) $entry['grade']);

            if ($grade === '') {
                $issues[] = "السطر رقم {$index} يجب أن يحتوي على اسم التقدير.";
            }

            if ($min < 0 || $max > 100) {
                $issues[] = "السطر رقم {$index} خارج النطاق 0..100.";
            }

            if ($min > $max) {
                $issues[] = "السطر رقم {$index} يحتوي على min أكبر من max.";
            }

            $entries[] = [
                'min' => $min,
                'max' => $max,
            ];
        }

        if ($issues !== []) {
            return $issues;
        }

        usort($entries, fn ($left, $right) => $left['min'] <=> $right['min']);

        $first = $entries[0];
        $last = $entries[count($entries) - 1];

        if ($first['min'] > 0.01) {
            $issues[] = 'سلم التقديرات يجب أن يبدأ من 0.';
        }

        if ($last['max'] < 99.99) {
            $issues[] = 'سلم التقديرات يجب أن ينتهي عند 100.';
        }

        for ($i = 1; $i < count($entries); $i++) {
            $previous = $entries[$i - 1];
            $current = $entries[$i];
            $gap = $current['min'] - $previous['max'];

            if ($gap > 0.01) {
                $issues[] = 'يوجد فجوة بين نطاقات سلم التقدير.';
                break;
            }

            if ($gap < -0.01) {
                $issues[] = 'يوجد تداخل بين نطاقات سلم التقدير.';
                break;
            }
        }

        return $issues;
    }
}
