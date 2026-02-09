<?php

namespace App\Domains\Shared\Services\Dashboard\DTOs\Alerts;

final readonly class DashboardAlert
{
    public function __construct(
        public string $type,
        public string $title,
        public string $message,
        public ?string $route = null,
        public array $routeParams = [],
        public ?string $drawer = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'route' => $this->route,
            'route_params' => $this->routeParams,
            'drawer' => $this->drawer,
        ];
    }
}
