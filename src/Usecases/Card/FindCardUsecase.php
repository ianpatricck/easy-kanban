<?php declare(strict_types=1);

namespace App\Usecases\Card;

use App\Data\Repositories\CardRepository;
use App\Entities\Card;

class FindCardUsecase
{
    public function __construct(
        private CardRepository $cardRepository
    ) {}

    public function execute(int $id): Card|null
    {
        $card = $this->cardRepository->findOne($id);
        return $card;
    }
}
