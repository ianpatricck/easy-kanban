<?php declare(strict_types=1);

// |=======================================================|
// | Caso de uso para atualização de email de um usuário   |
// |=======================================================|

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use Exception;

class UpdateEmailUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string|int $by, string $updatedEmail): void
    {
        if (!filter_var($updatedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email format is not valid', 400);
        }

        $user = null;

        if (gettype($by) == 'int') {
            $user = $this->userRepository->findOneById($by);
        } else if (gettype($by) == 'string') {
            $user = $this->userRepository->findOneByUsername($by);
        }

        if (!$user) {
            throw new Exception('User not found', 404);
        }

        $emailAlreadyExists = $this->userRepository->findOneByEmail($updatedEmail);

        if ($emailAlreadyExists) {
            throw new Exception('The email is already in use', 400);
        }

        $this->userRepository->updateEmail($user->getId(), $updatedEmail);
    }
}
