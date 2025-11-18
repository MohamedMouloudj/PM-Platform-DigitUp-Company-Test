<?php

namespace Database\Factories;

use App\Enums\PermissionType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectPermission>
 */
class ProjectPermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'permission' => fake()->randomElement(PermissionType::cases()),
            'granted_by' => User::factory(),
            'granted_at' => now(),
        ];
    }

    /**
     * Create a read permission.
     */
    public function read(): static
    {
        return $this->state(fn(array $attributes) => [
            'permission' => PermissionType::READ,
        ]);
    }

    /**
     * Create a write permission.
     */
    public function write(): static
    {
        return $this->state(fn(array $attributes) => [
            'permission' => PermissionType::WRITE,
        ]);
    }

    /**
     * Create a manage permission.
     */
    public function manage(): static
    {
        return $this->state(fn(array $attributes) => [
            'permission' => PermissionType::MANAGE,
        ]);
    }
}
