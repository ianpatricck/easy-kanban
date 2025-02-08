<?php declare(strict_types=1);

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use Exception;

class UpdateNameUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string $username, string $newName): void
    {
        $validateNameRegex = '/^[a-zA-ZÀ-Úà-ú ]+$/i';

        if (!preg_match($validateNameRegex, $newName)) {
            throw new Exception($newName . ' is not a valid name', 400);
        }

        $this->userRepository->updateName($username, $newName);
    }
}
