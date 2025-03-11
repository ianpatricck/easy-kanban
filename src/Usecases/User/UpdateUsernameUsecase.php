<?php declare(strict_types=1);

// |==========================================================|
// | Caso de uso para atualização do nickname de um usuário   |
// |==========================================================|

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use Exception;

class UpdateUsernameUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string|int $by, string $newUsername): void
    {
        $validateUsernameRegex = '/^[0-9]|[\W]/i';

        if (preg_match($validateUsernameRegex, $newUsername)) {
            throw new Exception($newUsername . ' is not a valid username', 400);
        }

        $user = null;
        $id = (int) $by;

        if ($id) {
            $user = $this->userRepository->findOneById($id);
        } else if (gettype($by) == 'string') {
            $user = $this->userRepository->findOneByUsername($by);
        }

        if (!$user) {
            throw new Exception('User not found', 404);
        }

        if ($newUsername == $user->getUsername()) {
            throw new Exception($newUsername . ' is already in use', 400);
        }

        $this->userRepository->updateUsername($user->getId(), $newUsername);
    }
}
