<?php declare(strict_types=1);

// |============================================|
// | Caso de uso para exclusão de um comentário |
// |============================================|

namespace App\Usecases\Card;

use App\Data\Repositories\CardRepository;
use Exception;

class DeleteCardUsecase
{
    public function __construct(
        private CardRepository $cardRepository
    ) {}

    public function execute(int $id): void
    {
        $card = $this->cardRepository->findOne($id);

        if (!$card) {
            throw new Exception('The card could not be found', 400);
        }

        $this->cardRepository->delete($id);
    }
}
