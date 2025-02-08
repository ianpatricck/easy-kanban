<?php declare(strict_types=1);

namespace App\Entities;

class Board
{
    public function __construct(
        private int $id,
        private string $name,
        private string $description,
        private int $active_users,
        private int $owner,
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function getActiveUsers(): int
    {
        return $this->active_users;
    }
}
