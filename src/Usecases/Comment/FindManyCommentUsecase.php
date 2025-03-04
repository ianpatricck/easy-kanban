<?php declare(strict_types=1);

// |==============================================|
// | Caso de uso para resgatar muitos comentários |
// |==============================================|

namespace App\Usecases\Comment;

use App\Data\Repositories\CommentRepository;
use Exception;

class FindManyCommentUsecase
{
    public function __construct(
        private CommentRepository $commentRepository,
    ) {}

    public function execute(int $limit): array
    {
        $comments = $this->commentRepository->findMany($limit);

        if (empty($comments)) {
            throw new Exception('Comments could not be found', 404);
        }

        return $comments;
    }
}
