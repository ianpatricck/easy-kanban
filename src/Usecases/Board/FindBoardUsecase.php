<?php declare(strict_types=1);

// |======================================|
// | Caso de uso para encontrar um quadro |
// |======================================|

namespace App\Usecases\Board;

use App\Data\Repositories\BoardRepository;
use App\Entities\Board;

class FindBoardUsecase
{
    public function __construct(
        protected BoardRepository $boardRepository
    ) {}

    public function execute(int $id): Board|null
    {
        return $this->boardRepository->findOneById($id);
    }
}
