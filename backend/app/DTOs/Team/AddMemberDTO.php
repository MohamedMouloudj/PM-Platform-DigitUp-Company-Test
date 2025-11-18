<?php

declare(strict_types=1);

namespace App\DTOs\Team;

readonly class AddMemberDTO
{
    public function __construct(
        public string $user_id,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            user_id: $data['user_id'],
        );
    }
}
