<?php declare(strict_types=1);

// |============================================|
// | DTO para autenticar o usuário              |
// |============================================|

namespace App\DTO;

class AuthenticateUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
