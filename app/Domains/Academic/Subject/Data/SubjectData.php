<?php

namespace App\Domains\Academic\Subject\Data;

class SubjectData
{
    public function __construct(
        public string $name,
        public ?string $code,
        public string $type,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            code: $data['code'] ?? null,
            type: $data['type'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
        ];
    }
}
