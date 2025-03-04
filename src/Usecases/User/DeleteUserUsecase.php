<?php declare(strict_types=1);

// |==============================================|
// | Caso de uso para autenticação de um usuário  |
// |==============================================|

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use Exception;

class DeleteUserUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string|int $by): void
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

        $this->userRepository->delete($user->getId());
    }
}
