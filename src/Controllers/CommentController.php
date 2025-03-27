<?php declare(strict_types=1);

// |============================================|
// | Controlador dos comentários                |
// |============================================|

namespace App\Controllers;

use App\DTO\CommentResponseDTO;
use App\DTO\CreateCommentDTO;
use App\DTO\UpdateCommentDTO;
use App\Usecases\Comment\CreateCommentUsecase;
use App\Usecases\Comment\DeleteCommentUsecase;
use App\Usecases\Comment\FindCommentUsecase;
use App\Usecases\Comment\FindManyCommentUsecase;
use App\Usecases\Comment\UpdateCommentUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use OpenApi\Attributes as OA;
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

    #[OA\Get(
        path: '/api/comments/{id}',
        tags: ['Comment'],
        summary: 'Find one comment',
        description: 'Get comments by id',
        operationId: 'findComment',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'comment id',
                required: true,
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: [
                new OA\JsonContent(
                    ref: CommentResponseDTO::class
                )
            ]),
            new OA\Response(response: 404, description: 'Comment not found'),
        ],
    )]
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

    #[OA\Get(
        path: '/api/comments',
        tags: ['Comment'],
        summary: 'Find comments',
        description: 'Get many comments',
        operationId: 'findManyComments',
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'limit value that needed to be considered for filter comments',
                required: false,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: [
                    new OA\JsonContent(
                        type: 'array',
                        items: new OA\Items(
                            ref: CommentResponseDTO::class
                        )
                    )
                ]
            )
        ],
    )]
    public function findMany(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $comments = $this->findManyCommentUsecase->execute($params);
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

    #[OA\Post(
        path: '/api/comments/create',
        tags: ['Comment'],
        summary: 'Create a new comment',
        description: 'This create a new comment in the application',
        operationId: 'createComment',
        responses: [
            new OA\Response(response: 201, description: 'Comment was created successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Create a comment',
            required: true,
            content: new OA\JsonContent(
                ref: CreateCommentDTO::class
            )
        )
    )]
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

    #[OA\Patch(
        path: '/api/comments/{id}',
        tags: ['Comment'],
        summary: 'Update a comment',
        description: 'This update a comment in the application',
        operationId: 'updateComment',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'comment id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Comment was updated successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'Comment not found'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update a comment',
            required: true,
            content: new OA\JsonContent(
                ref: UpdateCommentDTO::class
            )
        )
    )]
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

    #[OA\Delete(
        path: '/api/comments/{id}',
        tags: ['Comment'],
        summary: 'Delete a comment',
        description: 'This delete a comment in the application',
        operationId: 'deleteComment',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'comment id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Comment was deleted successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'Comment not found'),
        ],
    )]
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
