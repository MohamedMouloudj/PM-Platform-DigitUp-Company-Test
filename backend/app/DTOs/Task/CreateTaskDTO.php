<?php

declare(strict_types=1);

namespace App\DTOs\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

readonly class CreateTaskDTO
{
    public function __construct(
        public string $project_id,
        public string $title,
        public string $description,
        public TaskPriority $priority,
        public TaskStatus $status,
        public ?string $assigned_to,
        public ?string $deadline,
        public string $created_by,
    ) {}

    public static function fromRequest(array $data, string $projectId, string $userId): self
    {
        return new self(
            project_id: $projectId,
            title: $data['title'],
            description: strip_tags($data['description']),
            priority: TaskPriority::from($data['priority']),
            status: isset($data['status']) ? TaskStatus::from($data['status']) : TaskStatus::TODO,
            assigned_to: $data['assigned_to'] ?? null,
            deadline: $data['deadline'] ?? null,
            created_by: $userId,
        );
    }
}
