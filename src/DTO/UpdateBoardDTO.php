<?php declare(strict_types=1);

// |============================================|
// | DTO para atualizar um usuário              |
// |============================================|

namespace App\DTO;

class UpdateBoardDTO
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}
}
