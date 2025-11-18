<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\DTOs\Task\AssignTaskDTO;
use App\DTOs\Task\CreateTaskDTO;
use App\DTOs\Task\UpdateTaskDTO;
use App\Enums\PermissionType;
use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\Project\ProjectService;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectService $projectService,
    ) {}

    public function create(CreateTaskDTO $dto, User $user): Task
    {
        // Verify user has WRITE access to the project
        $project = $this->projectService->getById($dto->project_id, $user, PermissionType::WRITE);

        $task = $this->taskRepository->create([
            'project_id' => $dto->project_id,
            'title' => $dto->title,
            'description' => $dto->description,
            'priority' => $dto->priority,
            'status' => $dto->status,
            'assigned_to' => $dto->assigned_to,
            'deadline' => $dto->deadline,
            'created_by' => $dto->created_by,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($task)
            ->withProperties([
                'project_id' => $project->id,
                'project_name' => $project->name,
            ])
            ->log('Task created');

        return $task;
    }

    public function update(string $taskId, UpdateTaskDTO $dto, User $user): Task
    {
        $task = $this->getTaskWithAccessCheck($taskId, $user, PermissionType::WRITE);

        $updateData = array_filter([
            'title' => $dto->title,
            'description' => $dto->description,
            'priority' => $dto->priority,
            'status' => $dto->status,
            'assigned_to' => $dto->assigned_to,
            'deadline' => $dto->deadline,
        ], fn($value) => $value !== null);

        $task = $this->taskRepository->update($task, $updateData);

        activity()
            ->causedBy($user)
            ->performedOn($task)
            ->withProperties(['updated_fields' => array_keys($updateData)])
            ->log('Task updated');

        return $task;
    }

    public function delete(string $taskId, User $user): void
    {
        $task = $this->getTaskWithAccessCheck($taskId, $user, PermissionType::DELETE);

        activity()
            ->causedBy($user)
            ->performedOn($task)
            ->withProperties([
                'task_title' => $task->title,
                'project_id' => $task->project_id,
            ])
            ->log('Task deleted');

        $this->taskRepository->delete($task);
    }

    public function getById(string $taskId, User $user): Task
    {
        return $this->getTaskWithAccessCheck($taskId, $user, PermissionType::READ);
    }

    public function getProjectTasks(string $projectId, User $user): Collection
    {
        // Verify user has READ access to the project
        $this->projectService->getById($projectId, $user, PermissionType::READ);

        return $this->taskRepository->getProjectTasks($projectId);
    }

    public function getAllUserTasks(User $user): Collection
    {
        // Get all projects the user has access to
        $projects = $this->projectService->getUserProjects($user);
        $projectIds = $projects->pluck('id')->toArray();

        // Return tasks from all accessible projects
        return Task::with(['project', 'assignedTo', 'creator'])
            ->whereIn('project_id', $projectIds)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUserTasks(User $user): Collection
    {
        if ($user->role === UserRole::ADMIN) {
            // Admin can see all tasks
            return Task::with(['project', 'assignedTo', 'creator'])->get();
        }

        return $this->taskRepository->getUserTasks($user);
    }

    public function assign(string $taskId, AssignTaskDTO $dto, User $user): Task
    {
        $task = $this->getTaskWithAccessCheck($taskId, $user, PermissionType::WRITE);

        $task = $this->taskRepository->assign($task, $dto->assigned_to);

        activity()
            ->causedBy($user)
            ->performedOn($task)
            ->withProperties([
                'assigned_to' => $dto->assigned_to,
                'previous_assignee' => $task->assigned_to,
            ])
            ->log('Task assigned');

        return $task;
    }

    private function getTaskWithAccessCheck(string $taskId, User $user, PermissionType $requiredPermission): Task
    {
        $task = $this->taskRepository->findById($taskId);

        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        // Check if user has required permission on the parent project
        $this->projectService->getById($task->project_id, $user, $requiredPermission);

        return $task;
    }
}
