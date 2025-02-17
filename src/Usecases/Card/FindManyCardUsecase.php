<?php declare(strict_types=1);

namespace App\Usecases\Card;

use App\Data\Repositories\CardRepository;
use Exception;

class FindManyCardUsecase
{
    public function __construct(
        private CardRepository $cardRepository
    ) {}

    public function execute(int $limit): array
    {
        $cards = $this->cardRepository->findMany($limit);

        if (empty($cards)) {
            throw new Exception('Cards could not be found', 404);
        }

        return $cards;
    }
}
