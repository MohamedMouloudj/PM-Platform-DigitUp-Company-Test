<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    /**
     * Create a new project
     */
    public function create(array $data): Project;

    /**
     * Find project by ID
     */
    public function findById(string $id): ?Project;

    /**
     * Update project
     */
    public function update(Project $project, array $data): bool;

    /**
     * Delete project (soft delete)
     */
    public function delete(Project $project): bool;

    /**
     * Get projects user has access to
     */
    public function getUserProjects(User $user): Collection;

    /**
     * Archive project
     */
    public function archive(Project $project): bool;

    /**
     * Restore archived project
     */
    public function restore(string $id): bool;
}
