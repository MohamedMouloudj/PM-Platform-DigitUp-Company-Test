<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Enums\UserRole;

readonly class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role = UserRole::MEMBER,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: isset($data['role']) ? UserRole::from($data['role']) : UserRole::MEMBER,
        );
    }
}
