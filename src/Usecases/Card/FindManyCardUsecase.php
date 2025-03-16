<?php declare(strict_types=1);

// |==============================================|
// | Caso de uso para resgatar muitos cartões     |
// |==============================================|

namespace App\Usecases\Card;

use App\Data\Repositories\CardRepository;
use Exception;

class FindManyCardUsecase
{
    public function __construct(
        private CardRepository $cardRepository
    ) {}

    public function execute(array $params = []): array
    {
        $limit = !empty($params) && $params['limit'] ? (int) $params['limit'] : null;
        $cards = $this->cardRepository->findMany($limit);

        if (empty($cards)) {
            throw new Exception('Cards could not be found', 404);
        }

        return $cards;
    }
}
