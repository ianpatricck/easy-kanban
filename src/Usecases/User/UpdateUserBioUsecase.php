<?php declare(strict_types=1);

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;

class UpdateUserBioUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string $username, string $bio): void
    {
        $this->userRepository->updateDescription($username, $bio);
    }
}
