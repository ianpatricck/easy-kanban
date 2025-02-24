<?php declare(strict_types=1);

namespace App\Data;

use App\Data\Adapter\DatabaseInMemory;

class DAO extends DatabaseInMemory
{
    public function execute(string $query, array $data): void
    {
        $this->startConnection();
        $this->connection->prepare($query)->execute(array_values($data));
        $this->closeConnection();
    }

    public function fetchOne(string $query, array $values = []): bool|object
    {
        $this->startConnection();

        $stmt = $this->connection->prepare($query);

        if (isset($values)) {
            for ($index = 1; $index <= count($values); $index++) {
                $stmt->bindValue($index, $values[$index - 1]);
            }
        }

        $stmt->execute();

        $result = $stmt->fetch();
        $this->closeConnection();

        return $result;
    }

    public function fetchMany(string $query, array $values): bool|array
    {
        $this->startConnection();

        $stmt = $this->connection->prepare($query);
        for ($index = 1; $index <= count($values); $index++) {
            $stmt->bindValue($index, $values[$index - 1]);
        }

        $stmt->execute();

        $result = $stmt->fetchAll();
        $this->closeConnection();

        return $result;
    }
}
