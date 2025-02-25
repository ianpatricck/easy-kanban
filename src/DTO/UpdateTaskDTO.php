<?php declare(strict_types=1);

// |============================================|
// | DTO para atualizar uma tarefa              |
// |============================================|

namespace App\DTO;

class UpdateTaskDTO
{
    public function __construct(
        public string $title,
        public string $body,
        public string $hex_bgcolor,
        public int $attributed_to,
    ) {}
}
