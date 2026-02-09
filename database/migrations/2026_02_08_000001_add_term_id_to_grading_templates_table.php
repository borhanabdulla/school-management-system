<?php

use App\Domains\Academic\Term\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grading_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('grading_templates', 'term_id')) {
                $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        if (! Schema::hasColumn('grading_templates', 'term_id')) {
            return;
        }

        $defaultTermId = Term::query()
            ->orderBy('order_index')
            ->value('id');

        $termsByYear = Term::query()
            ->orderBy('order_index')
            ->get(['id', 'academic_year_id'])
            ->groupBy('academic_year_id')
            ->map(fn($terms) => $terms->first()?->id)
            ->all();

        $templates = DB::table('grading_templates')
            ->whereNull('term_id')
            ->get(['id', 'academic_year_id']);

        foreach ($templates as $template) {
            $termId = $termsByYear[$template->academic_year_id] ?? $defaultTermId;
            if (! $termId) {
                continue;
            }

            DB::table('grading_templates')
                ->where('id', $template->id)
                ->update(['term_id' => $termId]);
        }
    }

    public function down(): void
    {
        Schema::table('grading_templates', function (Blueprint $table) {
            if (Schema::hasColumn('grading_templates', 'term_id')) {
                $table->dropForeign(['term_id']);
                $table->dropColumn('term_id');
            }
        });
    }
};
