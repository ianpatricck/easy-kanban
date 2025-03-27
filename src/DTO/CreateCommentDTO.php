<?php declare(strict_types=1);

// |============================================|
// | DTO para criar um comentário               |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CreateCommentDTO',
    description: 'Create a comment'
)]
class CreateCommentDTO
{
    public function __construct(
        #[OA\Property(
            title: 'body',
            example: 'This is a simple comment'
        )]
        public string $body,

        #[OA\Property(
            title: 'owner',
            format: 'int64',
            example: 1
        )]
        public int $owner,

        #[OA\Property(
            title: 'task',
            format: 'int64',
            example: 1
        )]
        public int $task,
    ) {}
}
