<?php declare(strict_types=1);

namespace App\Usecases\Card;

use App\Data\Repositories\CardRepository;
use App\DTO\CreateCardDTO;

class CreateCardUsecase
{
    public function __construct(
        private CardRepository $cardRepository
    ) {}

    public function execute(CreateCardDTO $createCardDTO)
    {
        $name = $createCardDTO->name;

        if (!isset($name) || !(strlen(trim($name)) > 0)) {
            throw new \InvalidArgumentException("The card's name was not provided", 400);
        }

        $hexBackgroundColor = $createCardDTO->hex_bgcolor;

        if (!preg_match('/^#[a-f0-9]{6}$/i', $hexBackgroundColor)) {
            throw new \InvalidArgumentException('Invalid color format', 400);
        }

        if (preg_match('/[^a-zA-Z0-9\s*]/', $name)) {
            throw new \InvalidArgumentException("Name's format is not valid", 400);
        }

        $this->cardRepository->create($createCardDTO);
    }
}
