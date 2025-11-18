<?php

declare(strict_types=1);

namespace App\Services\Comment;

use App\DTOs\Comment\CreateCommentDTO;
use App\DTOs\Comment\UpdateCommentDTO;
use App\Enums\FileScanStatus;
use App\Enums\PermissionType;
use App\Models\Comment;
use App\Models\FileValidation;
use App\Models\User;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Services\Task\TaskService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentService
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
        private readonly TaskService $taskService,
    ) {}

    public function create(CreateCommentDTO $dto, User $user): Comment
    {
        // Verify user has WRITE access to the task's project
        $task = $this->taskService->getById($dto->task_id, $user);

        return DB::transaction(function () use ($dto, $user, $task) {
            $data = [
                'task_id' => $dto->task_id,
                'content' => $dto->content,
                'user_id' => $dto->user_id,
            ];

            // Handle file upload if present
            if ($dto->file) {
                $fileData = $this->handleFileUpload($dto->file, $user);
                $data = array_merge($data, $fileData);
            }

            $comment = $this->commentRepository->create($data);

            // Parse and log @mentions
            $mentions = $this->parseMentions($dto->content);
            if (!empty($mentions)) {
                activity()
                    ->causedBy($user)
                    ->performedOn($comment)
                    ->withProperties([
                        'mentions' => $mentions,
                        'task_id' => $task->id,
                    ])
                    ->log('Comment created with mentions');
            } else {
                activity()
                    ->causedBy($user)
                    ->performedOn($comment)
                    ->withProperties(['task_id' => $task->id])
                    ->log('Comment created');
            }

            return $comment;
        });
    }

    public function update(string $commentId, UpdateCommentDTO $dto, User $user): Comment
    {
        $comment = $this->getCommentWithAccessCheck($commentId, $user);

        return DB::transaction(function () use ($comment, $dto, $user) {
            $comment = $this->commentRepository->update($comment, $dto->toArray());

            activity()
                ->causedBy($user)
                ->performedOn($comment)
                ->log('Comment updated');

            return $comment;
        });
    }

    public function delete(string $commentId, User $user): void
    {
        $comment = $this->getCommentWithAccessCheck($commentId, $user);

        DB::transaction(function () use ($comment, $user) {
            // Delete associated file if exists
            if ($comment->file_path) {
                Storage::delete($comment->file_path);
            }

            activity()
                ->causedBy($user)
                ->performedOn($comment)
                ->withProperties(['content' => $comment->content])
                ->log('Comment deleted');

            $this->commentRepository->delete($comment);
        });
    }

    public function getById(string $commentId, User $user): Comment
    {
        $comment = $this->commentRepository->findById($commentId);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        // Verify user has READ access to the task's project
        $this->taskService->getById($comment->task_id, $user);

        return $comment;
    }

    public function getTaskComments(string $taskId, User $user): Collection
    {
        // Verify user has READ access to the task
        $this->taskService->getById($taskId, $user);

        return $this->commentRepository->getTaskComments($taskId);
    }

    private function getCommentWithAccessCheck(string $commentId, User $user): Comment
    {
        $comment = $this->commentRepository->findById($commentId);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        // Only comment creator can update/delete their own comments
        if ($comment->user_id !== $user->id) {
            throw new AccessDeniedHttpException('You can only modify your own comments');
        }

        // Also verify user still has access to the task
        $this->taskService->getById($comment->task_id, $user);

        return $comment;
    }

    private function handleFileUpload(UploadedFile $file, User $user): array
    {
        // Generate SHA-256 hash
        $hash = hash_file('sha256', $file->getRealPath());

        // Store file in private storage
        $path = $file->store('private/uploads', 'local');

        // Create file validation record
        FileValidation::create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'hash' => $hash,
            'scan_status' => FileScanStatus::PENDING,
            'uploaded_by' => $user->id,
        ]);

        return [
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_mime_type' => $file->getMimeType(),
        ];
    }

    private function parseMentions(string $content): array
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        return array_unique($matches[1] ?? []);
    }
}
