<?php declare(strict_types=1);

// |============================================|
// | DTO para criar uma tarefa                  |
// |============================================|

namespace App\DTO;

class CreateTaskDTO
{
    public function __construct(
        public string $title,
        public string $body,
        public string $hex_bgcolor,
        public int $owner,
        public int $attributed_to,
        public int $card,
    ) {}
}
