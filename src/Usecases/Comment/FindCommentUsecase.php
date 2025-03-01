<?php declare(strict_types=1);

namespace App\Usecases\Comment;

use App\Data\Repositories\CommentRepository;
use App\Entities\Comment;
use Exception;

class FindCommentUsecase
{
    public function __construct(
        private CommentRepository $commentRepository,
    ) {}

    public function execute(int $id): Comment
    {
        $comment = $this->commentRepository->findOne($id);

        if (!$comment) {
            throw new Exception('Comment could not be found', 404);
        }

        return $comment;
    }
}
