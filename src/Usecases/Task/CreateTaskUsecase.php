<?php declare(strict_types=1);

namespace App\Usecases\Task;

use App\Data\Repositories\CardRepository;
use App\Data\Repositories\TaskRepository;
use App\Data\Repositories\UserRepository;
use App\DTO\CreateTaskDTO;
use Exception;

class CreateTaskUsecase
{
    public function __construct(
        private UserRepository $userRepository,
        private CardRepository $cardRepository,
        private TaskRepository $taskRepository,
    ) {}

    public function execute(CreateTaskDTO $createTaskDTO)
    {
        if (!isset($createTaskDTO->title) || !(strlen(trim($createTaskDTO->title)) > 0)) {
            throw new Exception("The task's title was not provided", 401);
        }

        $hexBackgroundColor = $createTaskDTO->hex_bgcolor;

        if (!preg_match('/^#[a-f0-9]{6}$/i', $hexBackgroundColor)) {
            throw new Exception('Invalid color format', 401);
        }

        $owner = $this->userRepository->findOneById($createTaskDTO->owner);

        if (!$owner) {
            throw new Exception('Owner not found', 404);
        }

        $attributedUser = $this->userRepository->findOneById($createTaskDTO->attributed_to);

        if (!$attributedUser) {
            throw new Exception('User not found', 404);
        }

        $card = $this->cardRepository->findOne($createTaskDTO->card);

        if (!$card) {
            throw new Exception('Card not found', 404);
        }

        $this->taskRepository->create($createTaskDTO);
    }
}
