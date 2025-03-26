<?php declare(strict_types=1);

// |============================================|
// | DTO de resposta para a busca de uma tarefa |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'TaskResponseDTO',
    description: 'Task response'
)]
class TaskResponseDTO
{
    public function __construct(
        #[OA\Property(
            title: 'id',
            format: 'int64',
            example: 1
        )]
        public int $id,

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
            example: '#f1f2f3'
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

        #[OA\Property(
            title: 'created_at',
            example: '2025-03-10 19:45:24'
        )]
        public string $created_at,

        #[OA\Property(
            title: 'updated_at',
            example: '2025-03-10 19:45:24'
        )]
        public string $updated_at,
    ) {}
}
