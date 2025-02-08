<?php declare(strict_types=1);

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;

class UpdatePasswordUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string $username, string $oldPassword, string $newPassword): void
    {
        if (strlen($newPassword) < 8) {
            throw new \InvalidArgumentException('Password must be greater than 8 characters', 400);
        }

        $user = $this->userRepository->findOneByUserName($username);

        if (!password_verify($oldPassword, $user->getPassword())) {
            throw new \Exception("Old password don't match", 400);
        }

        $bcryptNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->userRepository->updatePassword($username, $bcryptNewPassword);
    }
}
