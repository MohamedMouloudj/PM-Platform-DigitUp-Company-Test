<?php

namespace Database\Factories;

use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' Project',
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'confidentiality_level' => fake()->randomElement(ConfidentialityLevel::cases()),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create an active project.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ProjectStatus::ACTIVE,
        ]);
    }

    /**
     * Create an archived project.
     */
    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ProjectStatus::ARCHIVED,
        ]);
    }

    /**
     * Create a public project.
     */
    public function public(): static
    {
        return $this->state(fn(array $attributes) => [
            'confidentiality_level' => ConfidentialityLevel::PUBLIC,
        ]);
    }

    /**
     * Create a confidential project.
     */
    public function confidential(): static
    {
        return $this->state(fn(array $attributes) => [
            'confidentiality_level' => ConfidentialityLevel::CONFIDENTIAL,
        ]);
    }

    /**
     * Create a top secret project.
     */
    public function topSecret(): static
    {
        return $this->state(fn(array $attributes) => [
            'confidentiality_level' => ConfidentialityLevel::TOP_SECRET,
            'description' => encrypt(fake()->paragraph()),
        ]);
    }
}
