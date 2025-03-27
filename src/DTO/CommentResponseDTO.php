<?php declare(strict_types=1);

// |===============================================|
// | DTO de resposta para a busca de um comentário |
// |===============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CommentResponseDTO',
    description: 'Comment response'
)]
class CommentResponseDTO
{
    public function __construct(
        #[OA\Property(
            title: 'id',
            format: 'int64',
            example: 1
        )]
        public int $id,

        #[OA\Property(
            title: 'body',
            example: 'This is a simple commentary'
        )]
        public string $body,

        #[OA\Property(
            title: 'owner',
            format: 'int64',
            example: 1
        )]
        private int $owner,

        #[OA\Property(
            title: 'task',
            format: 'int64',
            example: 1
        )]
        private int $task,

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
