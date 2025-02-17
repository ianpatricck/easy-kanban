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

    public function findOne(int $id): Card|null
    {
        $query = 'SELECT * FROM cards WHERE id = ?';
        $card = $this->cardDAO->fetchOne($query, [$id]);

        if ($card) {
            $cardArray = get_object_vars($card);
            $cardEntity = new Card(...array_values($cardArray));
            return $cardEntity;
        }

        return null;
    }

    public function findMany(int $limit): array
    {
        $query = 'SELECT * FROM cards LIMIT ?';
        $cards = $this->cardDAO->fetchMany($query, [$limit]);

        if (!empty($cards)) {
            $cardsEntities = [];

            foreach ($cards as $card) {
                $cardArray = get_object_vars($card);
                $cardsEntities[] = new Card(...array_values($cardArray));
            }

            return $cardsEntities;
        }

        return [];
    }

    public function create(CreateCardDTO $dto): void
    {
        $query = 'INSERT INTO cards (name, hex_bgcolor, board) VALUES (?, ?, ?)';
        $this->cardDAO->execute($query, get_object_vars($dto));
    }

    public function update(int $id, UpdateCardDTO $dto): void
    {
        $query = 'UPDATE cards SET name = ?, hex_bgcolor = ? WHERE id = ?';
        $this->cardDAO->execute($query, [...get_object_vars($dto), $id]);
    }

    public function delete(int $id): void
    {
        $query = 'DELETE FROM cards WHERE id = ?';
        $this->cardDAO->execute($query, [$id]);
    }
}
