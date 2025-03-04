<?php declare(strict_types=1);

// |========================================|
// | Caso de uso para exclusão de um quadro |
// |========================================|

namespace App\Usecases\Board;

use App\Data\Repositories\BoardRepository;
use Exception;

class DeleteBoardUsecase
{
    public function __construct(
        protected BoardRepository $boardRepository
    ) {}

    public function execute(int $id): void
    {
        $board = $this->boardRepository->findOneById($id);

        if (!$board) {
            throw new Exception('Board not found', 404);
        }

        $this->boardRepository->delete($id);
    }
}
