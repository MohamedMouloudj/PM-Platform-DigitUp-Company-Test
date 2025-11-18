<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(UserRole::cases()),
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::ADMIN,
        ]);
    }

    /**
     * Create a manager user.
     */
    public function manager(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::MANAGER,
        ]);
    }

    /**
     * Create a member user.
     */
    public function member(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::MEMBER,
        ]);
    }

    /**
     * Create a user with 2FA enabled.
     */
    public function with2FA(): static
    {
        return $this->state(fn(array $attributes) => [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('test_secret_key'),
        ]);
    }
}
