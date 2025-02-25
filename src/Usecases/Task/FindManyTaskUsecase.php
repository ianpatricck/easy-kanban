<?php declare(strict_types=1);

namespace App\Usecases\Task;

use App\Data\Repositories\TaskRepository;
use Exception;

class FindManyTaskUsecase
{
    public function __construct(
        private TaskRepository $taskRepository,
    ) {}

    public function execute(int $limit): array
    {
        $tasks = $this->taskRepository->findMany($limit);

        if (empty($tasks)) {
            throw new Exception('Tasks could not be found', 404);
        }

        return $tasks;
    }
}
