<?php

declare(strict_types=1);

namespace App\DTOs\Comment;

readonly class UpdateCommentDTO
{
    public function __construct(
        public ?string $content,
    ) {}

    public static function fromRequest(array $data): self
    {
        $content = isset($data['content']) ? strip_tags($data['content']) : null;

        return new self(
            content: $content,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'content' => $this->content,
        ], fn($value) => $value !== null);
    }
}
