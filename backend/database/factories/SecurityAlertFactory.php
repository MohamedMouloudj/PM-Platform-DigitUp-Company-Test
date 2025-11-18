<?php

namespace Database\Factories;

use App\Enums\AlertSeverity;
use App\Enums\AlertType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SecurityAlert>
 */
class SecurityAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'alert_type' => fake()->randomElement(AlertType::cases()),
            'severity' => fake()->randomElement(AlertSeverity::cases()),
            'ip_address' => fake()->ipv4(),
            'location' => fake()->city() . ', ' . fake()->country(),
            'details' => [
                'user_agent' => fake()->userAgent(),
                'timestamp' => now()->toISOString(),
            ],
            'is_resolved' => false,
            'resolved_at' => null,
            'resolved_by' => null,
        ];
    }

    /**
     * Create a resolved alert.
     */
    public function resolved(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => User::factory(),
        ]);
    }

    /**
     * Create a critical alert.
     */
    public function critical(): static
    {
        return $this->state(fn(array $attributes) => [
            'severity' => AlertSeverity::CRITICAL,
        ]);
    }

    /**
     * Create a suspicious login alert.
     */
    public function suspiciousLogin(): static
    {
        return $this->state(fn(array $attributes) => [
            'alert_type' => AlertType::SUSPICIOUS_LOGIN,
            'severity' => AlertSeverity::HIGH,
        ]);
    }

    /**
     * Create a new location alert.
     */
    public function newLocation(): static
    {
        return $this->state(fn(array $attributes) => [
            'alert_type' => AlertType::NEW_LOCATION,
            'severity' => AlertSeverity::MEDIUM,
        ]);
    }
}
