<?php declare(strict_types=1);

// |============================================|
// | DTO de resposta para a busca de um quadro  |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'BoardResponseDTO',
    description: 'Board response'
)]
class BoardResponseDTO
{
    public function __construct(
        #[OA\Property(
            title: 'id',
            format: 'int64',
            example: 1
        )]
        public int $id,

        #[OA\Property(
            title: 'name',
            example: 'My first board'
        )]
        public string $name,

        #[OA\Property(
            title: 'description',
            example: 'This is a simple task board'
        )]
        public string $description,

        #[OA\Property(
            title: 'active users',
            example: 1,
        )]
        public int $active_users,

        #[OA\Property(
            title: 'owner',
            format: 'int64',
            example: 1
        )]
        public int $owner,

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
