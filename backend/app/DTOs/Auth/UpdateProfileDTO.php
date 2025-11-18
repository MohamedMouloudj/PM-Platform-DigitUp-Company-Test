<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

readonly class UpdateProfileDTO
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
        );
    }
}
