<?php

namespace Database\Factories;

use App\Enums\FileScanStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileValidation>
 */
class FileValidationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['pdf', 'jpg', 'png', 'docx']);

        return [
            'file_path' => 'uploads/' . fake()->uuid() . '.' . $extension,
            'original_name' => fake()->word() . '.' . $extension,
            'mime_type' => $this->getMimeType($extension),
            'size' => fake()->numberBetween(1024, 5242880),
            'hash' => hash('sha256', fake()->uuid()),
            'scan_status' => fake()->randomElement(FileScanStatus::cases()),
            'scan_result' => null,
            'uploaded_by' => User::factory(),
        ];
    }

    /**
     * Get MIME type for extension.
     */
    private function getMimeType(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }

    /**
     * Create a clean file.
     */
    public function clean(): static
    {
        return $this->state(fn(array $attributes) => [
            'scan_status' => FileScanStatus::CLEAN,
            'scan_result' => 'No threats detected',
        ]);
    }

    /**
     * Create an infected file.
     */
    public function infected(): static
    {
        return $this->state(fn(array $attributes) => [
            'scan_status' => FileScanStatus::INFECTED,
            'scan_result' => 'Threat detected: Malware.Generic',
        ]);
    }

    /**
     * Create a pending scan file.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'scan_status' => FileScanStatus::PENDING,
            'scan_result' => null,
        ]);
    }
}
