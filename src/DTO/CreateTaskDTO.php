<?php declare(strict_types=1);

// |============================================|
// | DTO para criar uma tarefa                  |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CreateTaskDTO',
    description: 'Create a task'
)]
class CreateTaskDTO
{
    public function __construct(
        #[OA\Property(
            title: 'title',
            example: 'Task One'
        )]
        public string $title,

        #[OA\Property(
            title: 'body',
            example: 'This is a long task...'
        )]
        public string $body,

        #[OA\Property(
            title: 'hex_bgcolor',
            example: '#e1e1e1'
        )]
        public string $hex_bgcolor,

        #[OA\Property(
            title: 'owner',
            format: 'int64',
            example: 1,
        )]
        public int $owner,

        #[OA\Property(
            title: 'attributed_to',
            format: 'int64',
            example: 1,
        )]
        public int $attributed_to,

        #[OA\Property(
            title: 'card',
            format: 'int64',
            example: 1,
        )]
        public int $card,
    ) {}
}
