<?php declare(strict_types=1);

// |============================================|
// | DTO para autenticar o usuário              |
// |============================================|

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AuthenticateUserDTO',
    description: 'Authenticate an user'
)]
class AuthenticateUserDTO
{
    public function __construct(
        #[OA\Property(
            title: 'email',
            format: 'email',
            example: 'john@example.com'
        )]
        public string $email,

        #[OA\Property(
            title: 'password',
            example: 'johnpass2123'
        )]
        public string $password,
    ) {}
}
