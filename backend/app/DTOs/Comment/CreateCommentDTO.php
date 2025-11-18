<?php

declare(strict_types=1);

namespace App\DTOs\Comment;

use Illuminate\Http\UploadedFile;

readonly class CreateCommentDTO
{
    public function __construct(
        public string $task_id,
        public string $content,
        public ?UploadedFile $file,
        public string $created_by,
    ) {}

    public static function fromRequest(array $data, string $taskId, string $userId): self
    {
        return new self(
            task_id: $taskId,
            content: strip_tags($data['content']),
            file: $data['file'] ?? null,
            created_by: $userId,
        );
    }
}
