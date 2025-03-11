<?php declare(strict_types=1);

// |============================================|
// | DTO para criar um usuário                  |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CreateUserDTO',
    description: 'Create an user'
)]
class CreateUserDTO
{
    public function __construct(
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
            title: 'password',
            maximum: 200,
            example: 'johnpass2123'
        )]
        public string $password,

        #[OA\Property(
            title: 'bio',
            example: "Hello! I'm John"
        )]
        public string|null $bio = '',

        #[OA\Property(
            title: 'avatar',
            example: 'jsmithavatar2312312.png'
        )]
        public string|null $avatar = '',
    ) {}
}
