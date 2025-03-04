<?php declare(strict_types=1);

// |==============================================|
// | Caso de uso para encontrar um usuário        |
// |==============================================|

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use App\Entities\User;
use Exception;

class FindUserUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string $by): User
    {
        $user = $this->userRepository->findOneByUsername($by);

        if (!$user) {
            $user = $this->userRepository->findOneById((int) $by);

            if (!$user) {
                throw new Exception('User not found', 404);
            }
        }

        return $user;
    }
}
