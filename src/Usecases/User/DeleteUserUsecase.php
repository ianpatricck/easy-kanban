<?php declare(strict_types=1);

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;

class DeleteUserUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(int $id): void
    {
        $user = $this->userRepository->findOneById($id);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        $this->userRepository->delete($id);
    }
}
