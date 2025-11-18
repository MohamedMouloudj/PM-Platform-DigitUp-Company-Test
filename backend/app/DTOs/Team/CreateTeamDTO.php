<?php

declare(strict_types=1);

namespace App\DTOs\Team;

readonly class CreateTeamDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $created_by,
    ) {}

    public static function fromRequest(array $data, string $userId): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            created_by: $userId,
        );
    }
}
