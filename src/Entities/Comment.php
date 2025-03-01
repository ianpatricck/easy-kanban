<?php declare(strict_types=1);

namespace App\Entities;

class Comment
{
    public function __construct(
        private int $id,
        private string $body,
        private int $owner,
        private int $task,
        private string $created_at,
        private string $updated_at,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function getTask(): int
    {
        return $this->task;
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
