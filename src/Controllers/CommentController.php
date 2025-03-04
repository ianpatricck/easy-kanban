<?php declare(strict_types=1);

// |============================================|
// | Controlador dos comentários                |
// |============================================|

namespace App\Controllers;

use App\DTO\CreateCommentDTO;
use App\DTO\UpdateCommentDTO;
use App\Usecases\Comment\CreateCommentUsecase;
use App\Usecases\Comment\DeleteCommentUsecase;
use App\Usecases\Comment\FindCommentUsecase;
use App\Usecases\Comment\FindManyCommentUsecase;
use App\Usecases\Comment\UpdateCommentUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

use function App\utils\isAuthorizedUser;

class CommentController
{
    public function __construct(
        private AuthorizeUserUsecase $authorizeUserUsecase,
        private FindCommentUsecase $findCommentUsecase,
        private FindManyCommentUsecase $findManyCommentUsecase,
        private CreateCommentUsecase $createCommentUsecase,
        private UpdateCommentUsecase $updateCommentUsecase,
        private DeleteCommentUsecase $deleteCommentUsecase,
    ) {}

    /*
     * Método para encontrar um comentário
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * return Response
     */
    public function findOne(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $comment = $this->findCommentUsecase->execute($id);

            $commentResponse = [
                'id' => $comment->getId(),
                'body' => $comment->getBody(),
                'owner' => $comment->getOwner(),
                'task' => $comment->getTask(),
                'created_at' => $comment->getCreatedAt(),
                'updated_at' => $comment->getUpdatedAt(),
            ];

            $response->getBody()->write(json_encode($commentResponse));
            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para encontrar muitos comentários
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */
    public function findMany(Request $request, Response $response): Response
    {
        try {
            $limit = (int) $request->getQueryParams()['limit'];
            $comments = $this->findManyCommentUsecase->execute($limit);
            $commentsResponse = [];

            foreach ($comments as $comment) {
                $commentsResponse[] = [
                    'id' => $comment->getId(),
                    'body' => $comment->getBody(),
                    'owner' => $comment->getOwner(),
                    'task' => $comment->getTask(),
                    'created_at' => $comment->getCreatedAt(),
                    'updated_at' => $comment->getUpdatedAt(),
                ];
            }

            $response->getBody()->write(json_encode($commentsResponse));
            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para criação de um comentário
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));
            $body = $request->getParsedBody();

            $ownerId = (int) $body['owner'];
            $authId = (int) $authorizedUser->id;

            if ($ownerId != $authId) {
                throw new Exception('User unauthorized', 401);
            }

            $createCommentDTO = new CreateCommentDTO(...$body);
            $this->createCommentUsecase->execute($createCommentDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Comment was created successfully'
                ])
            );

            return $response->withStatus(201);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para atualizar um comentário
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * return Response
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));

            $commentId = (int) $args['id'];
            $body = $request->getParsedBody();

            $comment = $this->findCommentUsecase->execute($commentId);

            if ($authorizedUser->id !== $comment->getOwner()) {
                throw new Exception('User unauthorized', 401);
            }

            $updateCommentDTO = new UpdateCommentDTO(...$body);
            $this->updateCommentUsecase->execute($commentId, $updateCommentDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Comment was updated successfully'
                ])
            );

            return $response->withStatus(201);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para deletar um comentário
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * return Response
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));
            $commentId = (int) $args['id'];

            $taskEntity = $this->findCommentUsecase->execute($commentId);

            if ($authorizedUser->id !== $taskEntity->getOwner()) {
                throw new Exception('User unauthorized', 401);
            }

            $this->deleteCommentUsecase->execute($commentId);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Comment was deleted successfully'
                ])
            );

            return $response->withStatus(201);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }
}
