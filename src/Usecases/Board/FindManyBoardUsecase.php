<?php declare(strict_types=1);

// |==========================================|
// | Caso de uso para resgatar muitos quadros |
// |==========================================|

namespace App\Usecases\Board;

use App\Data\Repositories\BoardRepository;

class FindManyBoardUsecase
{
    public function __construct(
        protected BoardRepository $boardRepository
    ) {}

    public function execute(array $params = []): array|null
    {
        $limit = !empty($params) && $params['limit'] ? (int) $params['limit'] : null;
        return $this->boardRepository->findMany($limit);
    }
}
