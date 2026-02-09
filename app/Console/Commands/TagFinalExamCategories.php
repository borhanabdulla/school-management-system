<?php

namespace App\Console\Commands;

use App\Domains\Academic\Grading\Models\TemplateCategory;
use Illuminate\Console\Command;

class TagFinalExamCategories extends Command
{
    protected $signature = 'grading:tag-final-exam-categories {--apply}';
    protected $description = 'Tag final exam template categories based on name heuristics';

    public function handle(): int
    {
        $patterns = [
            'نهائي',
            'اختبار نهائي',
            'final',
            'final exam',
        ];

        $query = TemplateCategory::query()
            ->where('is_final_exam', false)
            ->where(function ($q) use ($patterns) {
                foreach ($patterns as $pattern) {
                    $q->orWhere('name', 'like', '%' . $pattern . '%');
                }
            });

        $matches = $query->get();

        if ($matches->isEmpty()) {
            $this->info('No categories matched.');
            return self::SUCCESS;
        }

        $this->info('Matched categories: ' . $matches->count());
        $matches->each(function (TemplateCategory $category) {
            $this->line(" - #{$category->id}: {$category->name}");
        });

        if (!$this->option('apply')) {
            $this->warn('Dry run only. Re-run with --apply to update.');
            return self::SUCCESS;
        }

        $updated = $query->update(['is_final_exam' => true]);
        $this->info("Updated categories: {$updated}");

        return self::SUCCESS;
    }
}
