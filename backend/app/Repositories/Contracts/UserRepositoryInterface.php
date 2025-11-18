<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user
     */
    public function create(array $data): User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Update user login information
     */
    public function updateLoginInfo(User $user, string $ip): void;
}
