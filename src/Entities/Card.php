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

    public function getName(): string
    {
        return $this->name;
    }

    public function getHexBgcolor(): string
    {
        return $this->hex_bgcolor;
    }

    public function getBoard(): int
    {
        return $this->board;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }
}
