<?php declare(strict_types=1);

// |=====================================================|
// | Repositório de tarefas para persistência de dados   |
// |=====================================================|

namespace App\Data\Repositories;

use App\Data\DAO;
use App\DTO\CreateTaskDTO;
use App\DTO\UpdateTaskDTO;
use App\Entities\Task;

class TaskRepository
{
    public function __construct(
        private readonly DAO $dao
    ) {}

    public function findOne(int $id): Task|null
    {
        $query = 'SELECT * FROM tasks WHERE id = ?';
        $task = $this->dao->fetchOne($query, [$id]);

        if ($task) {
            $taskArray = get_object_vars($task);
            $taskEntity = new Task(...array_values($taskArray));
            return $taskEntity;
        }

        return null;
    }

    public function findMany(int $limit = null): array
    {
        $tasks = [];

        if ($limit) {
            $tasks = $this->dao->fetchMany('SELECT * FROM tasks LIMIT ?', [$limit]);
        } else {
            $tasks = $this->dao->fetchMany('SELECT * FROM tasks');
        }

        if (!empty($tasks)) {
            $taskEntities = [];

            foreach ($tasks as $task) {
                $taskArray = get_object_vars($task);
                $taskEntities[] = new Task(...array_values($taskArray));
            }

            return $taskEntities;
        }

        return [];
    }

    public function create(CreateTaskDTO $dto): void
    {
        $query = 'INSERT INTO tasks (title, body, hex_bgcolor, owner, attributed_to, card)
                VALUES (?, ?, ?, ?, ?, ?)';

        $this->dao->execute($query, get_object_vars($dto));
    }

    public function update(int $id, UpdateTaskDTO $dto): void
    {
        $query = 'UPDATE tasks SET title = ?, body = ?, hex_bgcolor = ?, attributed_to = ? WHERE id = ?';
        $this->dao->execute($query, [...get_object_vars($dto), $id]);
    }

    public function delete(int $id): void
    {
        $query = 'DELETE FROM tasks WHERE id = ?';
        $this->dao->execute($query, [$id]);
    }
}
