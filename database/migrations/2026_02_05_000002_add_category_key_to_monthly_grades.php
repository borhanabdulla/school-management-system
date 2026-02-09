<?php

use App\Domains\Academic\Grading\Models\GradebookSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->string('category_key')->nullable()->after('category');
            $table->index(['course_offering_id', 'gradebook_month_id', 'student_id', 'category_key'], 'monthly_grades_category_key_index');
        });

        GradebookSettings::query()
            ->select(['id', 'academic_year_id', 'monthly_categories'])
            ->chunkById(100, function ($settings) {
                foreach ($settings as $record) {
                    $normalized = GradebookSettings::normalizeMonthlyCategories($record->monthly_categories ?? []);
                    $record->update(['monthly_categories' => $normalized]);
                }
            });

        DB::table('monthly_grades')
            ->whereNull('category_key')
            ->orderBy('id')
            ->chunkById(500, function ($grades) {
                $monthIds = $grades->pluck('gradebook_month_id')->filter()->unique()->values();
                $months = DB::table('gradebook_months')
                    ->whereIn('id', $monthIds)
                    ->get(['id', 'academic_year_id'])
                    ->keyBy('id');

                $yearIds = $months->pluck('academic_year_id')->filter()->unique()->values();
                $settingsByYear = GradebookSettings::whereIn('academic_year_id', $yearIds)
                    ->get(['academic_year_id', 'monthly_categories'])
                    ->keyBy('academic_year_id');

                foreach ($grades as $grade) {
                    $month = $months->get($grade->gradebook_month_id);
                    $yearId = $month->academic_year_id ?? null;
                    if (! $yearId) {
                        continue;
                    }

                    $settings = $settingsByYear->get($yearId);
                    if (! $settings) {
                        continue;
                    }

                    $categories = GradebookSettings::normalizeMonthlyCategories($settings->monthly_categories ?? []);
                    $key = null;
                    foreach ($categories as $category) {
                        if (($category['label'] ?? null) === $grade->category) {
                            $key = $category['key'] ?? null;
                            break;
                        }
                    }

                    if (! $key) {
                        continue;
                    }

                    DB::table('monthly_grades')
                        ->where('id', $grade->id)
                        ->update(['category_key' => $key]);
                }
            });

        $remaining = DB::table('monthly_grades')->whereNull('category_key')->count();
        if ($remaining > 0) {
            $samples = DB::table('monthly_grades')
                ->whereNull('category_key')
                ->orderBy('id')
                ->limit(10)
                ->get(['id', 'category', 'gradebook_month_id'])
                ->map(fn($row) => "{$row->id}:{$row->category}")
                ->implode(', ');

            throw new RuntimeException(
                "Monthly grade category_key backfill incomplete. Remaining {$remaining}. Samples: {$samples}"
            );
        }
    }

    public function down(): void
    {
        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropIndex('monthly_grades_category_key_index');
            $table->dropColumn('category_key');
        });
    }
};
