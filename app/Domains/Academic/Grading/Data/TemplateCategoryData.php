<?php

namespace App\Domains\Academic\Grading\Data;

use App\Domains\Academic\Grading\Models\TemplateCategory;

final class TemplateCategoryData
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $templateId,
        public readonly ?int $parentId,
        public readonly string $name,
        public readonly float $weight,
        public readonly float $maxRawScore,
        public readonly string $calculationType,
        public readonly bool $isDynamicWeight,
        public readonly bool $isLocked,
        public readonly string $mappingType,
        public readonly bool $passRequired,
        public readonly float $passThreshold,
        public readonly bool $isReadonly,
        public readonly bool $isFinalExam
    ) {
    }

    public static function fromModel(TemplateCategory $category): self
    {
        return new self(
            id: $category->id,
            templateId: (int) $category->grading_template_id,
            parentId: $category->parent_id,
            name: $category->name,
            weight: (float) $category->weight,
            maxRawScore: (float) ($category->max_raw_score ?? 0),
            calculationType: $category->calculation_type,
            isDynamicWeight: (bool) $category->is_dynamic_weight,
            isLocked: (bool) $category->is_locked,
            mappingType: $category->mapping_type ?? 'manual',
            passRequired: (bool) $category->pass_required,
            passThreshold: (float) ($category->pass_threshold ?? 0),
            isReadonly: (bool) $category->is_readonly,
            isFinalExam: (bool) $category->is_final_exam
        );
    }
}
