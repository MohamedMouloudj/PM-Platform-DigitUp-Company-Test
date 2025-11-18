<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function create(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(Task $task): bool;

    public function findById(string $id): ?Task;

    public function getProjectTasks(string $projectId): Collection;

    public function getUserTasks(User $user): Collection;

    public function assign(Task $task, ?string $userId): Task;
}
