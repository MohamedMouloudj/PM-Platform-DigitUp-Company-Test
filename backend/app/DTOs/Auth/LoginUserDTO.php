<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

readonly class LoginUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            remember: $data['remember'] ?? false,
        );
    }
}
