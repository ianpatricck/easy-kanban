<?php declare(strict_types=1);

// |=======================================================|
// | Caso de uso para atualização do nome de um usuário    |
// |=======================================================|

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use Exception;

class UpdateNameUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(string|int $by, string $newName): void
    {
        $validateNameRegex = '/^[a-zA-ZÀ-Úà-ú ]+$/i';

        if (!preg_match($validateNameRegex, $newName)) {
            throw new Exception($newName . ' is not a valid name', 400);
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

        $this->userRepository->updateName($user->getId(), $newName);
    }
}
