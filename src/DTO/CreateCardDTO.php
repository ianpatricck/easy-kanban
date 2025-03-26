<?php declare(strict_types=1);

// |============================================|
// | DTO para criar um cartão                   |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CreateCardDTO',
    description: 'Create a board'
)]
class CreateCardDTO
{
    public function __construct(
        #[OA\Property(
            title: 'name',
            example: 'Backlog'
        )]
        public string $name,

        #[OA\Property(
            title: 'hex_bgcolor',
            example: '#e1e1e1'
        )]
        public string $hex_bgcolor,

        #[OA\Property(
            title: 'owner',
            format: 'int64',
            example: 1
        )]
        public int $owner,

        #[OA\Property(
            title: 'board',
            format: 'int64',
            example: 1
        )]
        public int $board,
    ) {}
}
