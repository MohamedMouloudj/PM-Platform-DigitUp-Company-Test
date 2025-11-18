<?php

declare(strict_types=1);

namespace App\DTOs\Team;

readonly class UpdateTeamDTO
{
    public function __construct(
        public ?string $name,
        public ?string $description,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
        ], fn($value) => $value !== null);
    }
}
