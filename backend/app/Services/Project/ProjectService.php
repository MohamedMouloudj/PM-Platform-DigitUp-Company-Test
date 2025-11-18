<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\DTOs\Project\CreateProjectDTO;
use App\DTOs\Project\UpdateProjectDTO;
use App\Enums\ConfidentialityLevel;
use App\Enums\PermissionType;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository
    ) {}

    /**
     * Create a new project
     */
    public function create(CreateProjectDTO $dto, User $user): Project
    {
        return DB::transaction(function () use ($dto, $user) {
            $data = [
                'id' => Str::uuid(),
                'name' => $dto->name,
                'description' => $dto->description,
                'status' => $dto->status,
                'confidentiality_level' => $dto->confidentiality_level,
                'created_by' => $user->id,
            ];

            // Encrypt description if top secret
            if ($dto->confidentiality_level === ConfidentialityLevel::TOP_SECRET) {
                $data['description'] = encrypt($dto->description);
            }

            $project = $this->projectRepository->create($data);

            activity()
                ->causedBy($user)
                ->performedOn($project)
                ->withProperties([
                    'confidentiality_level' => $dto->confidentiality_level->value,
                ])
                ->event('created')
                ->log('Project created');

            return $project;
        });
    }

    /**
     * Update a project
     */
    public function update(string $projectId, UpdateProjectDTO $dto, User $user): Project
    {
        $project = $this->projectRepository->findById($projectId);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        $this->checkAccess($project, $user, PermissionType::WRITE);

        return DB::transaction(function () use ($project, $dto, $user) {
            $data = $dto->toArray();

            // Handle confidentiality level change
            if ($dto->confidentiality_level === ConfidentialityLevel::TOP_SECRET && $dto->description) {
                $data['description'] = encrypt($dto->description);
            }

            $this->projectRepository->update($project, $data);

            activity()
                ->causedBy($user)
                ->performedOn($project)
                ->event('updated')
                ->log('Project updated');

            return $project->fresh();
        });
    }

    /**
     * Delete a project
     */
    public function delete(string $projectId, User $user): bool
    {
        $project = $this->projectRepository->findById($projectId);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        $this->checkAccess($project, $user, PermissionType::DELETE);

        return DB::transaction(function () use ($project, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($project)
                ->event('deleted')
                ->log('Project deleted');

            return $this->projectRepository->delete($project);
        });
    }

    /**
     * Get project by ID
     */
    public function getById(string $projectId, User $user, ?PermissionType $requiredPermission = null): Project
    {
        $project = $this->projectRepository->findById($projectId);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        $this->checkAccess($project, $user, $requiredPermission ?? PermissionType::READ);

        // Decrypt description if top secret and user has access
        if ($project->confidentiality_level === ConfidentialityLevel::TOP_SECRET) {
            try {
                $project->description = decrypt($project->description);
            } catch (\Exception $e) {
                // Description might not be encrypted (backward compatibility)
            }
        }

        return $project;
    }

    /**
     * Get all projects user has access to
     */
    public function getUserProjects(User $user): Collection
    {
        return $this->projectRepository->getUserProjects($user);
    }

    /**
     * Archive a project
     */
    public function archive(string $projectId, User $user): Project
    {
        $project = $this->projectRepository->findById($projectId);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        $this->checkAccess($project, $user, PermissionType::MANAGE);

        DB::transaction(function () use ($project, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($project)
                ->event('archived')
                ->log('Project archived');

            $this->projectRepository->archive($project);
        });

        return $project;
    }

    /**
     * Restore an archived project
     */
    public function restore(string $projectId, User $user): Project
    {
        // Check if user is admin or project creator
        $project = Project::withTrashed()->find($projectId);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        if ($user->role !== UserRole::ADMIN && $project->created_by !== $user->id) {
            throw new AccessDeniedHttpException('You do not have permission to restore this project');
        }

        DB::transaction(function () use ($project, $user) {
            $this->projectRepository->restore($project->id);

            activity()
                ->causedBy($user)
                ->performedOn($project)
                ->event('restored')
                ->log('Project restored');
        });

        return $project->fresh();
    }

    /**
     * Check if user has access to project
     */
    private function checkAccess(Project $project, User $user, PermissionType $requiredPermission): void
    {
        // Admin has full access
        if ($user->role === UserRole::ADMIN) {
            return;
        }

        // Creator has full access
        if ($project->created_by === $user->id) {
            return;
        }

        // Check explicit permissions
        $permission = $project->permissions()
            ->where('user_id', $user->id)
            ->first();

        if ($permission && $this->hasPermission($permission->permission, $requiredPermission)) {
            return;
        }

        // Check team membership
        $hasTeamAccess = $project->teams()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if ($hasTeamAccess && $requiredPermission === PermissionType::READ) {
            return;
        }

        throw new AccessDeniedHttpException('You do not have permission to access this project');
    }

    /**
     * Check if permission level is sufficient
     */
    private function hasPermission(PermissionType $userPermission, PermissionType $requiredPermission): bool
    {
        $hierarchy = [
            PermissionType::READ->value => 1,
            PermissionType::WRITE->value => 2,
            PermissionType::DELETE->value => 3,
            PermissionType::MANAGE->value => 4,
        ];

        return $hierarchy[$userPermission->value] >= $hierarchy[$requiredPermission->value];
    }
}
