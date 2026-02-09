<?php

namespace App\Domains\Shared\Services\Dashboard\DTOs\Insights;

final readonly class DashboardInsight
{
    public function __construct(
        public string $type,
        public string $title,
        public string $message
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
}
