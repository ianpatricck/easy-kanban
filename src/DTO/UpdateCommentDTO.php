<?php declare(strict_types=1);

// |============================================|
// | DTO para atualizar um comentário           |
// |============================================|

namespace App\DTO;

class UpdateCommentDTO
{
    public function __construct(
        public string $body,
    ) {}
}
