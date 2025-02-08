<?php declare(strict_types=1);

namespace App\Data\Repositories;

use App\Data\DAO\CardDAO;
use App\DTO\CreateCardDTO;
use App\DTO\UpdateCardDTO;
use App\Entities\Card;

class CardRepository
{
    public function __construct(
        private readonly CardDAO $cardDAO
    ) {}

    public function create(CreateCardDTO $dto): void
    {
        $query = 'INSERT INTO cards (name, hex_bgcolor, board) VALUES (?, ?, ?)';
        $this->cardDAO->execute($query, get_object_vars($dto));
    }
}
