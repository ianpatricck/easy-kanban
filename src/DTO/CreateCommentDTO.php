<?php declare(strict_types=1);

// |============================================|
// | DTO para criar um comentário               |
// |============================================|

namespace App\DTO;

class CreateCommentDTO
{
    public function __construct(
        public string $body,
        public int $owner,
        public int $task,
    ) {}
}
