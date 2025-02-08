<?php declare(strict_types=1);

namespace App\Entities;

class Card
{
    public function __construct(
        private int $id,
        private string $name,
        private string $hex_bgcolor,
        private int $board,
        private string $created_at,
        private string $updated_at,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }
}
