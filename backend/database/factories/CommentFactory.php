<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
            'file_path' => null,
            'file_name' => null,
            'file_mime_type' => null,
            'file_size' => null,
        ];
    }

    /**
     * Create a comment with a file attachment.
     */
    public function withFile(): static
    {
        return $this->state(fn(array $attributes) => [
            'file_path' => 'uploads/' . fake()->uuid() . '.pdf',
            'file_name' => fake()->word() . '.pdf',
            'file_mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 5242880),
        ]);
    }
}
