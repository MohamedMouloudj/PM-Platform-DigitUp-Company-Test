<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Comment;
use Illuminate\Support\Collection;

interface CommentRepositoryInterface
{
    public function create(array $data): Comment;

    public function update(Comment $comment, array $data): Comment;

    public function delete(Comment $comment): bool;

    public function findById(string $id): ?Comment;

    public function getTaskComments(string $taskId): Collection;
}
