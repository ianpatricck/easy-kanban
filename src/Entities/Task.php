<?php declare(strict_types=1);

// |======================================================|
// | Entidade que representa uma tarefa e seus atributos  |
// |======================================================|

namespace App\Entities;

class Task
{
    public function __construct(
        private int $id,
        private string $title,
        private string $body,
        private string $hex_bgcolor,
        private int $owner,
        private int $attributed_to,
        private int $card,
        private string $created_at,
        private string $updated_at,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHexBgColor(): string
    {
        return $this->hex_bgcolor;
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function getAttributedTo(): int
    {
        return $this->attributed_to;
    }

    public function getCard(): int
    {
        return $this->card;
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
