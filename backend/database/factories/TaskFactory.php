<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
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
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'assigned_to' => User::factory(),
            'deadline' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create a high priority task.
     */
    public function highPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => TaskPriority::HIGH,
        ]);
    }

    /**
     * Create an urgent task.
     */
    public function urgent(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => TaskPriority::URGENT,
        ]);
    }

    /**
     * Create a todo task.
     */
    public function todo(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatus::TODO,
        ]);
    }

    /**
     * Create an in-progress task.
     */
    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatus::IN_PROGRESS,
        ]);
    }

    /**
     * Create a done task.
     */
    public function done(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatus::DONE,
        ]);
    }

    /**
     * Create an unassigned task.
     */
    public function unassigned(): static
    {
        return $this->state(fn(array $attributes) => [
            'assigned_to' => null,
        ]);
    }

    /**
     * Create a task with a deadline.
     */
    public function withDeadline(): static
    {
        return $this->state(fn(array $attributes) => [
            'deadline' => fake()->dateTimeBetween('now', '+2 weeks'),
        ]);
    }
}
