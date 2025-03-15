<?php declare(strict_types=1);

// |============================================|
// | DTO para atualizar um quadro               |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UpdateUserDTO',
    description: 'Update a board'
)]
class UpdateBoardDTO
{
    public function __construct(
        #[OA\Property(
            title: 'name',
            example: 'My updated board'
        )]
        public string $name,

        #[OA\Property(
            title: 'owner',
            example: 1,
            format: 'int64'
        )]
        public int $owner,

        #[OA\Property(
            title: 'description',
            example: 'This is a simple updated board'
        )]
        public ?string $description = null,
    ) {}
}
