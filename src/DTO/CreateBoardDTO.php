<?php declare(strict_types=1);

// |============================================|
// | DTO para criar um quadro                   |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CreateBordDTO',
    description: 'Create a board'
)]
class CreateBoardDTO
{
    public function __construct(
        #[OA\Property(
            title: 'name',
            example: 'A Simple Board'
        )]
        public string $name,

        #[OA\Property(
            title: 'owner',
            format: 'int64',
            example: 1
        )]
        public int $owner,

        #[OA\Property(
            title: 'description',
            example: 'This is my first board'
        )]
        public ?string $description = null,

        #[OA\Property(
            title: 'active users',
            format: 'int64',
            example: 1
        )]
        public int $active_users = 0
    ) {}
}
