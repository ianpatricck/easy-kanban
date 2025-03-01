<?php declare(strict_types=1);

namespace App\Usecases\Comment;

use App\Data\Repositories\CommentRepository;
use App\Data\Repositories\TaskRepository;
use App\Data\Repositories\UserRepository;
use App\DTO\CreateCommentDTO;
use Exception;

class CreateCommentUsecase
{
    public function __construct(
        private UserRepository $userRepository,
        private TaskRepository $taskRepository,
        private CommentRepository $commentRepository,
    ) {}

    public function execute(CreateCommentDTO $createCommentDTO): void
    {
        $comment = filter_var($createCommentDTO->body, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!isset($comment) || !(strlen(trim($comment)) > 0)) {
            throw new Exception('The comment cannot be empty', 400);
        }

        $createCommentDTO->body = $comment;

        $owner = $this->userRepository->findOneById($createCommentDTO->owner);

        if (!$owner) {
            throw new Exception('Owner cannot be found', 404);
        }

        $task = $this->taskRepository->findOne($createCommentDTO->task);

        if (!$task) {
            throw new Exception('Task cannot be found', 404);
        }

        $this->commentRepository->create($createCommentDTO);
    }
}
