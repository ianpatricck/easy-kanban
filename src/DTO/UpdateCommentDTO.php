<?php declare(strict_types=1);

// |============================================|
// | DTO para atualizar um comentário           |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UpdateCommentDTO',
    description: 'Update a comment'
)]
class UpdateCommentDTO
{
    public function __construct(
        #[OA\Property(
            title: 'body',
            example: 'Updated comment'
        )]
        public string $body,
    ) {}
}
