<?php declare(strict_types=1);

// |===========================================|
// | Caso de uso para criação de um usuário    |
// |===========================================|

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use App\DTO\CreateUserDTO;
use Exception;

class CreateUserUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(CreateUserDTO $createUserDTO): void
    {
        $usernameAlreadyExists = $this->userRepository->findOneByUsername($createUserDTO->username);
        $emailAlreadyExists = $this->userRepository->findOneByEmail($createUserDTO->email);

        if ($usernameAlreadyExists) {
            throw new Exception('The username is already in use', 400);
        }

        if ($emailAlreadyExists) {
            throw new Exception('The email is already in use', 400);
        }

        if (!filter_var($createUserDTO->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email format is not valid', 400);
        }

        if (strlen($createUserDTO->password) < 8) {
            throw new Exception('Password must be greater than 8 characters', 400);
        }

        $createUserDTO->password = password_hash($createUserDTO->password, PASSWORD_BCRYPT);

        $this->userRepository->create($createUserDTO);
    }
}
