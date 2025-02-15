<?php declare(strict_types=1);

// |============================================|
// | DTO para atualizar um cartão               |
// |============================================|

namespace App\DTO;

class UpdateCardDTO
{
    public function __construct(
        public string $name,
        public string $hex_bgcolor,
    ) {}
}
