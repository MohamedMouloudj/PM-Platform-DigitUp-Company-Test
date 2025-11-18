<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement(['lead', 'developer', 'designer', 'tester']),
            'joined_at' => now(),
        ];
    }

    /**
     * Create a team lead.
     */
    public function lead(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'lead',
        ]);
    }
}
