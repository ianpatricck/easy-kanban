<?php declare(strict_types=1);

// |============================================|
// | DTO de resposta para a busca de um cartão  |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CardResponseDTO',
    description: 'Card response'
)]
class CardResponseDTO
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
            example: 'Backlog'
        )]
        public string $name,

        #[OA\Property(
            title: 'hex_bgcolor',
            example: '#333333'
        )]
        public string $hex_bgcolor,

        #[OA\Property(
            title: 'board',
            format: 'int64',
            example: 1,
        )]
        public int $board,

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
