<?php declare(strict_types=1);

// |=======================================================|
// | Caso de uso para atualização de senha de um usuário   |
// |=======================================================|

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use Exception;

class UpdatePasswordUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string|int $by, string $oldPassword, string $newPassword): void
    {
        if (strlen($newPassword) < 8) {
            throw new \InvalidArgumentException('Password must be greater than 8 characters', 400);
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

        if (!password_verify($oldPassword, $user->getPassword())) {
            throw new \Exception("Old password don't match", 400);
        }

        $bcryptNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->userRepository->updatePassword($user->getId(), $bcryptNewPassword);
    }
}
