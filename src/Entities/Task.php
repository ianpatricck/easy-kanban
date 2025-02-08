<?php declare(strict_types=1);

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
}
