<?php declare(strict_types=1);

// |=====================================================|
// | Repositório dos cartões para persistência de dados  |
// |=====================================================|

namespace App\Data\Repositories;

use App\Data\DAO;
use App\DTO\CreateCardDTO;
use App\DTO\UpdateCardDTO;
use App\Entities\Card;

class CardRepository
{
    public function __construct(
        private readonly DAO $dao
    ) {}

    public function findOne(int $id): Card|null
    {
        $query = 'SELECT * FROM cards WHERE id = ?';
        $card = $this->dao->fetchOne($query, [$id]);

        if ($card) {
            $cardArray = get_object_vars($card);
            $cardEntity = new Card(...array_values($cardArray));
            return $cardEntity;
        }

        return null;
    }

    public function findMany(int $limit = null): array
    {
        $cards = [];

        if ($limit) {
            $cards = $this->dao->fetchMany('SELECT * FROM cards LIMIT ?', [$limit]);
        } else {
            $cards = $this->dao->fetchMany('SELECT * FROM cards');
        }

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
        $query = 'INSERT INTO cards (name, hex_bgcolor, owner, board) VALUES (?, ?, ?, ?)';
        $this->dao->execute($query, get_object_vars($dto));
    }

    public function update(int $id, UpdateCardDTO $dto): void
    {
        $query = 'UPDATE cards SET name = ?, hex_bgcolor = ? WHERE id = ?';
        $this->dao->execute($query, [...get_object_vars($dto), $id]);
    }

    public function delete(int $id): void
    {
        $query = 'DELETE FROM cards WHERE id = ?';
        $this->dao->execute($query, [$id]);
    }
}
