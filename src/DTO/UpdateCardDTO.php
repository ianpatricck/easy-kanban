<?php declare(strict_types=1);

// |============================================|
// | DTO para atualizar um cartão               |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UpdateCardDTO',
    description: 'Update a card'
)]
class UpdateCardDTO
{
    public function __construct(
        #[OA\Property(
            title: 'name',
            example: 'A Updated Card'
        )]
        public string $name,

        #[OA\Property(
            title: 'hex_bgcolor',
            example: '#f1f1f1'
        )]
        public string $hex_bgcolor,
    ) {}
}
