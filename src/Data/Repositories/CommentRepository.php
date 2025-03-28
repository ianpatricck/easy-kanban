<?php declare(strict_types=1);

// |=========================================================|
// | Repositório de comentários para persistência de dados   |
// |=========================================================|

namespace App\Data\Repositories;

use App\Data\DAO;
use App\DTO\CreateCommentDTO;
use App\DTO\UpdateCommentDTO;
use App\Entities\Comment;

class CommentRepository
{
    public function __construct(
        private readonly DAO $dao
    ) {}

    public function findOne(int $id): Comment|null
    {
        $query = 'SELECT * FROM comments WHERE id = ?';
        $comment = $this->dao->fetchOne($query, [$id]);

        if ($comment) {
            $commentArray = get_object_vars($comment);
            $commentEntity = new Comment(...array_values($commentArray));
            return $commentEntity;
        }

        return null;
    }

    public function findMany(int $limit = null): array
    {
        $comments = [];

        if ($limit) {
            $comments = $this->dao->fetchMany('SELECT * FROM comments LIMIT ?', [$limit]);
        } else {
            $comments = $this->dao->fetchMany('SELECT * FROM comments');
        }

        if (!empty($comments)) {
            $commentEntities = [];

            foreach ($comments as $comment) {
                $commentArray = get_object_vars($comment);
                $commentEntities[] = new Comment(...array_values($commentArray));
            }

            return $commentEntities;
        }

        return [];
    }

    public function create(CreateCommentDTO $dto): void
    {
        $query = 'INSERT INTO comments (body, owner, task) VALUES (?, ?, ?)';
        $this->dao->execute($query, get_object_vars($dto));
    }

    public function update(int $id, UpdateCommentDTO $dto): void
    {
        $query = 'UPDATE comments SET body = ? WHERE id = ?';
        $this->dao->execute($query, [...get_object_vars($dto), $id]);
    }

    public function delete(int $id): void
    {
        $query = 'DELETE FROM comments WHERE id = ?';
        $this->dao->execute($query, [$id]);
    }
}
