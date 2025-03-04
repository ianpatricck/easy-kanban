<?php declare(strict_types=1);

// |=============================================|
// | Caso de uso para atualização de uma tarefa  |
// |=============================================|

namespace App\Usecases\Task;

use App\Data\Repositories\TaskRepository;
use App\Data\Repositories\UserRepository;
use App\DTO\UpdateTaskDTO;
use Exception;

class UpdateTaskUsecase
{
    public function __construct(
        private UserRepository $userRepository,
        private TaskRepository $taskRepository,
    ) {}

    public function execute(int $id, UpdateTaskDTO $updateTaskDTO)
    {
        if (!isset($updateTaskDTO->title) || !(strlen(trim($updateTaskDTO->title)) > 0)) {
            throw new Exception("The task's title was not provided", 401);
        }

        if (!isset($updateTaskDTO->body) || !(strlen(trim($updateTaskDTO->body)) > 0)) {
            throw new Exception("The task's body was not provided", 401);
        }

        $hexBackgroundColor = $updateTaskDTO->hex_bgcolor;

        if (!preg_match('/^#[a-f0-9]{6}$/i', $hexBackgroundColor)) {
            throw new Exception('Invalid color format', 401);
        }

        $task = $this->taskRepository->findOne($id);

        if (!$task) {
            throw new Exception('Task not found', 404);
        }

        $attributedUser = $this->userRepository->findOneById($updateTaskDTO->attributed_to);

        if (!$attributedUser) {
            throw new Exception('Attributed user not found', 404);
        }

        $this->taskRepository->update($id, $updateTaskDTO);
    }
}
