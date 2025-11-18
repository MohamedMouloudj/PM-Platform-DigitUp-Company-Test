<?php

declare(strict_types=1);

namespace App\DTOs\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

readonly class UpdateTaskDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?TaskPriority $priority = null,
        public ?TaskStatus $status = null,
        public ?string $assigned_to = null,
        public ?string $deadline = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: isset($data['description']) ? strip_tags($data['description']) : null,
            priority: isset($data['priority']) ? TaskPriority::from($data['priority']) : null,
            status: isset($data['status']) ? TaskStatus::from($data['status']) : null,
            assigned_to: $data['assigned_to'] ?? null,
            deadline: $data['deadline'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority?->value,
            'status' => $this->status?->value,
            'assigned_to' => $this->assigned_to,
            'deadline' => $this->deadline,
        ], fn($value) => $value !== null);
    }
}
