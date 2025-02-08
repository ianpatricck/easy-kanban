<?php declare(strict_types=1);

namespace App\DTO;

class CreateUserDTO
{
    public function __construct(
        public string $username,
        public string $name,
        public string $email,
        public string $password,
        public string|null $bio = '',
        public string|null $avatar = '',
    ) {}
}
