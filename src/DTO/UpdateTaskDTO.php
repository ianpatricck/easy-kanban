<?php declare(strict_types=1);

// |============================================|
// | DTO para atualizar uma tarefa              |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UpdateTaskDTO',
    description: 'Update a task'
)]
class UpdateTaskDTO
{
    public function __construct(
        #[OA\Property(
            title: 'title',
            example: 'Updated Task'
        )]
        public string $title,

        #[OA\Property(
            title: 'body',
            example: 'New task asked'
        )]
        public string $body,

        #[OA\Property(
            title: 'hex_bgcolor',
            example: '#dddddd'
        )]
        public string $hex_bgcolor,

        #[OA\Property(
            title: 'attributed_to',
            format: 'int64',
            example: 1,
        )]
        public int $attributed_to,
    ) {}
}
