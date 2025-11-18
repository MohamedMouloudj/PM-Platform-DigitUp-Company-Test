<?php

declare(strict_types=1);

namespace App\DTOs\Project;

use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectStatus;

readonly class CreateProjectDTO
{
    public function __construct(
        public string $name,
        public string $description,
        public ProjectStatus $status,
        public ConfidentialityLevel $confidentiality_level,
        public string $created_by,
    ) {}

    public static function fromRequest(array $data, string $userId): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'],
            status: isset($data['status']) ? ProjectStatus::from($data['status']) : ProjectStatus::ACTIVE,
            confidentiality_level: ConfidentialityLevel::from($data['confidentiality_level']),
            created_by: $userId,
        );
    }
}
