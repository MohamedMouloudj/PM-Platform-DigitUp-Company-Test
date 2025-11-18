<?php

declare(strict_types=1);

namespace App\DTOs\Task;

readonly class AssignTaskDTO
{
    public function __construct(
        public string $assigned_to,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            assigned_to: $data['assigned_to'],
        );
    }
}
