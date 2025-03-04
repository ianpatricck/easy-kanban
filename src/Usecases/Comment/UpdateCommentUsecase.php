<?php declare(strict_types=1);

// |===============================================|
// | Caso de uso para atualização de um comentário |
// |===============================================|

namespace App\Usecases\Comment;

use App\Data\Repositories\CommentRepository;
use App\Data\Repositories\TaskRepository;
use App\Data\Repositories\UserRepository;
use App\DTO\UpdateCommentDTO;
use Exception;

class UpdateCommentUsecase
{
    public function __construct(
        private UserRepository $userRepository,
        private TaskRepository $taskRepository,
        private CommentRepository $commentRepository,
    ) {}

    public function execute(int $id, UpdateCommentDTO $updateCommentDTO): void
    {
        $commentBody = filter_var($updateCommentDTO->body, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!isset($commentBody) || !(strlen(trim($commentBody)) > 0)) {
            throw new Exception('The comment cannot be empty', 400);
        }

        $updateCommentDTO->body = $commentBody;

        $comment = $this->commentRepository->findOne($id);

        if (!$comment) {
            throw new Exception('Comment cannot be found', 404);
        }

        $this->commentRepository->update($id, $updateCommentDTO);
    }
}
