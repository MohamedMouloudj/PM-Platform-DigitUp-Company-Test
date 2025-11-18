<?php

declare(strict_types=1);

namespace App\DTOs\Project;

use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectStatus;

readonly class UpdateProjectDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?ProjectStatus $status = null,
        public ?ConfidentialityLevel $confidentiality_level = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            status: isset($data['status']) ? ProjectStatus::from($data['status']) : null,
            confidentiality_level: isset($data['confidentiality_level']) ? ConfidentialityLevel::from($data['confidentiality_level']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status?->value,
            'confidentiality_level' => $this->confidentiality_level?->value,
        ], fn($value) => $value !== null);
    }
}
