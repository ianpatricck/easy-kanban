<?php declare(strict_types=1);

// |===========================================================|
// | Caso de uso para atualização da descrição de um usuário   |
// |===========================================================|

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use Exception;

class UpdateUserBioUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string|int $by, string $bio): void
    {
        $user = null;

        if (gettype($by) == 'int') {
            $user = $this->userRepository->findOneById($by);
        } else if (gettype($by) == 'string') {
            $user = $this->userRepository->findOneByUsername($by);
        }

        if (!$user) {
            throw new Exception('User not found', 404);
        }

        $this->userRepository->updateDescription($by, $bio);
    }
}
