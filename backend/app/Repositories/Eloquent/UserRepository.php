<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Update user login information
     */
    public function updateLoginInfo(User $user, string $ip): void
    {
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }
}
