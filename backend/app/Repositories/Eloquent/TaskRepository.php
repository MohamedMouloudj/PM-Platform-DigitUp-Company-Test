<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function findById(string $id): ?Task
    {
        return Task::with(['project', 'assignedTo', 'creator', 'comments'])
            ->find($id);
    }

    public function getProjectTasks(string $projectId): Collection
    {
        return Task::with(['assignedTo', 'creator'])
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUserTasks(User $user): Collection
    {
        // Get tasks where user is assigned OR user created the task OR user has access to the project
        return Task::with(['project', 'assignedTo', 'creator'])
            ->where(function ($query) use ($user) {
                $query->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('project', function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                            ->orWhereHas('permissions', function ($pq) use ($user) {
                                $pq->where('user_id', $user->id);
                            })
                            ->orWhereHas('teams.members', function ($tm) use ($user) {
                                $tm->where('user_id', $user->id);
                            });
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function assign(Task $task, ?string $userId): Task
    {
        $task->update(['assigned_to' => $userId]);
        return $task->fresh();
    }
}
