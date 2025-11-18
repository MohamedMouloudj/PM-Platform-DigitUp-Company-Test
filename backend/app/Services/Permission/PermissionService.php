<?php

declare(strict_types=1);

namespace App\Services\Permission;

use App\DTOs\Permission\GrantPermissionDTO;
use App\Enums\PermissionType;
use App\Enums\UserRole;
use App\Models\ProjectPermission;
use App\Models\User;
use App\Services\Project\ProjectService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PermissionService
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {}

    public function grantPermission(string $projectId, GrantPermissionDTO $dto, User $user): ProjectPermission
    {
        // Verify user has MANAGE permission on the project
        $project = $this->projectService->getById($projectId, $user, PermissionType::MANAGE);

        // Prevent granting permission to project creator (they have implicit full access)
        if ($project->created_by === $dto->user_id) {
            throw new AccessDeniedHttpException('Cannot grant explicit permissions to project creator');
        }

        return DB::transaction(function () use ($projectId, $dto, $user) {
            // Check if permission already exists
            $existing = ProjectPermission::where('project_id', $projectId)
                ->where('user_id', $dto->user_id)
                ->first();

            if ($existing) {
                // Update existing permission
                $existing->update([
                    'permission' => $dto->permission,
                    'granted_by' => $dto->granted_by,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($existing)
                    ->withProperties([
                        'project_id' => $projectId,
                        'user_id' => $dto->user_id,
                        'permission' => $dto->permission->value,
                    ])
                    ->log('Permission updated');

                return $existing->fresh();
            }

            // Create new permission
            $permission = ProjectPermission::create([
                'project_id' => $projectId,
                'user_id' => $dto->user_id,
                'permission' => $dto->permission,
                'granted_by' => $dto->granted_by,
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($permission)
                ->withProperties([
                    'project_id' => $projectId,
                    'user_id' => $dto->user_id,
                    'permission' => $dto->permission->value,
                ])
                ->log('Permission granted');

            return $permission;
        });
    }

    public function revokePermission(string $projectId, string $userId, User $user): void
    {
        // Verify user has MANAGE permission on the project
        $project = $this->projectService->getById($projectId, $user, PermissionType::MANAGE);

        // Prevent revoking creator's implicit permissions
        if ($project->created_by === $userId) {
            throw new AccessDeniedHttpException('Cannot revoke permissions from project creator');
        }

        DB::transaction(function () use ($projectId, $userId, $user) {
            $permission = ProjectPermission::where('project_id', $projectId)
                ->where('user_id', $userId)
                ->first();

            if ($permission) {
                activity()
                    ->causedBy($user)
                    ->performedOn($permission)
                    ->withProperties([
                        'project_id' => $projectId,
                        'user_id' => $userId,
                        'permission' => $permission->permission->value,
                    ])
                    ->log('Permission revoked');

                $permission->delete();
            }
        });
    }

    public function getProjectPermissions(string $projectId, User $user): Collection
    {
        // Verify user has READ permission on the project
        $this->projectService->getById($projectId, $user, PermissionType::READ);

        return ProjectPermission::with(['user', 'grantedBy'])
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
