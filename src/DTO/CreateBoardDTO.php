<?php declare(strict_types=1);

namespace App\DTO;

class CreateBoardDTO
{
    public function __construct(
        public string $name,
        public int $owner,
        public ?string $description = null,
        public int $active_users = 0
    ) {}
}
