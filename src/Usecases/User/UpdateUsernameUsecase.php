<?php declare(strict_types=1);

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;

class UpdateUsernameUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string $currentUsername, string $newUsername): void
    {
        $validateUsernameRegex = '/^[0-9]|[\W]/i';

        if (preg_match($validateUsernameRegex, $newUsername)) {
            throw new \InvalidArgumentException($newUsername . ' is not a valid username', 400);
        }

        $user = $this->userRepository->findOneByUsername($newUsername);

        if ($user) {
            throw new \Exception($newUsername . ' is already in use', 400);
        }

        $this->userRepository->updateUsername($currentUsername, $newUsername);
    }
}
