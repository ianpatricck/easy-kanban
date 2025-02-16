<?php declare(strict_types=1);

namespace App\Usecases\Card;

use App\Data\Repositories\CardRepository;
use App\DTO\UpdateCardDTO;
use Exception;

class UpdateCardUsecase
{
    public function __construct(
        private CardRepository $cardRepository
    ) {}

    public function execute(int $id, UpdateCardDTO $updateCardDTO)
    {
        $card = $this->cardRepository->findOne($id);

        if (!$card) {
            throw new Exception('The card could not be found', 400);
        }

        if (!isset($updateCardDTO->name) || !(strlen(trim($updateCardDTO->name)) > 0)) {
            throw new Exception("Card's name cannot be empty", 400);
        }

        if (preg_match('/[^a-zA-Z0-9\s*]/', $updateCardDTO->name)) {
            throw new Exception("Card's name format is not valid", 400);
        }

        if (!preg_match('/^#[a-f0-9]{6}$/i', $updateCardDTO->hex_bgcolor)) {
            throw new Exception("Card's color format is not valid", 400);
        }

        $this->cardRepository->update($id, $updateCardDTO);
    }
}
