<?php declare(strict_types=1);

namespace App\Usecases\Board;

use App\Data\Repositories\BoardRepository;
use App\Data\Repositories\UserRepository;
use App\DTO\UpdateBoardDTO;

class UpdateBoardUsecase
{
    public function __construct(
        private BoardRepository $boardRepository,
        private UserRepository $userRepository
    ) {}

    public function execute(int $id, int $ownerId, UpdateBoardDTO $updateBoardDTO)
    {
        $name = $updateBoardDTO->name;

        if (!isset($name) || !(strlen(trim($name)) > 0)) {
            throw new \InvalidArgumentException("The board's name cannot be empty", 400);
        }

        $board = $this->boardRepository->findOneById($id);

        if (!$board) {
            throw new \Exception('Board not found', 404);
        }

        $owner = $this->userRepository->findOneById($ownerId);

        if (!$owner) {
            throw new \Exception("Board's owner not found", 404);
        }

        $this->boardRepository->update($id, $ownerId, $updateBoardDTO);
    }
}
