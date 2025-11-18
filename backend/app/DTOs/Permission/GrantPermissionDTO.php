<?php

declare(strict_types=1);

namespace App\DTOs\Permission;

use App\Enums\PermissionType;

readonly class GrantPermissionDTO
{
    public function __construct(
        public string $user_id,
        public PermissionType $permission,
        public string $granted_by,
    ) {}

    public static function fromRequest(array $data, string $grantedBy): self
    {
        return new self(
            user_id: $data['user_id'],
            permission: PermissionType::from($data['permission']),
            granted_by: $grantedBy,
        );
    }
}
