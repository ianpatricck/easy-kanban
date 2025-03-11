<?php declare(strict_types=1);

// |============================================|
// | DTO de resposta para a busca de um usuário |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UserResponseDTO',
    description: 'User response'
)]
class UserResponseDTO
{
    public function __construct(
        #[OA\Property(
            title: 'id',
            format: 'int64',
            example: 1
        )]
        public int $id,

        #[OA\Property(
            title: 'username',
            example: 'johns'
        )]
        public string $username,

        #[OA\Property(
            title: 'name',
            example: 'John Smith'
        )]
        public string $name,

        #[OA\Property(
            title: 'email',
            format: 'email',
            example: 'john@example.com'
        )]
        public string $email,

        #[OA\Property(
            title: 'bio',
            example: "Hello! I'm John"
        )]
        public string $bio,

        #[OA\Property(
            title: 'avatar',
            example: 'jsmithavatar2312312.png'
        )]
        public string $avatar,

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
