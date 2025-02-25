<?php declare(strict_types=1);

namespace App\Usecases\Task;

use App\Data\Repositories\TaskRepository;
use App\Entities\Task;
use Exception;

class FindTaskUsecase
{
    public function __construct(
        private TaskRepository $taskRepository,
    ) {}

    public function execute(int $id): Task
    {
        $task = $this->taskRepository->findOne($id);

        if (!$task) {
            throw new Exception('Task not found', 404);
        }

        return $task;
    }
}
