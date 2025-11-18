<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Support\Collection;

class CommentRepository implements CommentRepositoryInterface
{
    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        return $comment->fresh();
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }

    public function findById(string $id): ?Comment
    {
        return Comment::with(['task', 'user'])->find($id);
    }

    public function getTaskComments(string $taskId): Collection
    {
        return Comment::with(['user'])
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
