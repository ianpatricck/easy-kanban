<?php declare(strict_types=1);

namespace App\Usecases\Board;

use App\Data\Repositories\BoardRepository;
use App\Entities\Board;

class FindBoardUsecase
{
    public function __construct(
        protected BoardRepository $userRepository
    ) {}

    public function execute(int $id): Board|null
    {
        return $this->userRepository->findOneById($id);
    }
}
