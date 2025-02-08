<?php declare(strict_types=1);

namespace App\DTO;

class CreateCardDTO
{
    public function __construct(
        public string $name,
        public string $hex_bgcolor,
        public int $board,
    ) {}
}
