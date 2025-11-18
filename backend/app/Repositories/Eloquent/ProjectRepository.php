<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    /**
     * Create a new project
     */
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    /**
     * Find project by ID
     */
    public function findById(string $id): ?Project
    {
        return Project::with(['creator', 'teams', 'tasks', 'permissions'])->find($id);
    }

    /**
     * Update project
     */
    public function update(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    /**
     * Delete project (soft delete)
     */
    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    /**
     * Get projects user has access to
     */
    public function getUserProjects(User $user): Collection
    {
        // Admin can see all projects
        if ($user->role === UserRole::ADMIN) {
            return Project::with(['creator', 'teams', 'permissions'])->get();
        }

        // Get projects user created OR has permissions for OR is part of team assigned to project
        return Project::with(['creator', 'teams', 'permissions'])
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('permissions', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->orWhereHas('teams.members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->get();
    }

    /**
     * Archive project
     */
    public function archive(Project $project): bool
    {
        return $project->delete(); // Soft delete
    }

    /**
     * Restore archived project
     */
    public function restore(string $id): bool
    {
        $project = Project::withTrashed()->find($id);

        if (!$project) {
            return false;
        }

        return $project->restore();
    }
}
