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
}
