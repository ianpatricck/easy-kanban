<?php declare(strict_types=1);

namespace App\Data\Repositories;

use App\Data\DAO\BoardDAO;
use App\DTO\CreateBoardDTO;
use App\DTO\UpdateBoardDTO;
use App\Entities\Board;

class BoardRepository
{
    public function __construct(
        private readonly BoardDAO $boardDAO
    ) {}

    public function create(CreateBoardDTO $dto): void
    {
        $query = 'INSERT INTO boards (name, owner, description, active_users) VALUES (?, ?, ?, ?)';
        $this->boardDAO->execute($query, get_object_vars($dto));
    }

    public function findOneById(int $id): Board|null
    {
        $query = 'SELECT * FROM boards WHERE id = ?';
        $board = $this->boardDAO->fetchOne($query, [$id]);

        if ($board) {
            $boardArray = get_object_vars($board);
            $boardEntity = new Board(...array_values($boardArray));
            return $boardEntity;
        }

        return null;
    }

    public function update(int $id, int $owner, UpdateBoardDTO $dto): void
    {
        $query = 'UPDATE boards SET name = ? WHERE id = ? AND owner = ?';
        $values = [$dto->name, $id, $owner];

        if ($dto->description) {
            $query = 'UPDATE boards SET name = ?, description = ? WHERE id = ? AND owner = ?';
            $values = [$dto->name, $dto->description, $id, $owner];
        }

        $this->boardDAO->execute($query, $values);
    }

    public function delete(int $id): void
    {
        $query = 'DELETE FROM boards WHERE id = ?';
        $this->boardDAO->execute($query, [$id]);
    }
}
