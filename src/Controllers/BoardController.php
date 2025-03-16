<?php declare(strict_types=1);

// |============================================|
// | Controlador dos quadros                    |
// |============================================|

namespace App\Controllers;

use App\DTO\BoardResponseDTO;
use App\DTO\CreateBoardDTO;
use App\DTO\UpdateBoardDTO;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\Board\DeleteBoardUsecase;
use App\Usecases\Board\FindBoardUsecase;
use App\Usecases\Board\FindManyBoardUsecase;
use App\Usecases\Board\UpdateBoardUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

use function App\utils\isAuthorizedUser;

class BoardController
{
    public function __construct(
        private AuthorizeUserUsecase $authorizeUserUsecase,
        private CreateBoardUsecase $createBoardUsecase,
        private UpdateBoardUsecase $updateBoardUsecase,
        private FindManyBoardUsecase $findManyBoardUsecase,
        private DeleteBoardUsecase $deleteBoardUsecase,
        private FindBoardUsecase $findBoardUsecase,
    ) {}

    /*
     * Método que retorna vários quadros por um limite definido ou sem nenhum
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Get(
        path: '/api/boards',
        tags: ['Board'],
        summary: 'Find boards',
        description: 'Get boards',
        operationId: 'findManyBoards',
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'limit value that needed to be considered for filter boards',
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
                            ref: BoardResponseDTO::class
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
            $boards = $this->findManyBoardUsecase->execute($params);
            $boardsResponse = [];

            foreach ($boards as $board) {
                $boardsResponse[] = new BoardResponseDTO(
                    id: $board->getId(),
                    name: $board->getName(),
                    description: $board->getDescription(),
                    active_users: $board->getActiveUsers(),
                    owner: $board->getOwner(),
                    created_at: $board->getCreatedAt(),
                    updated_at: $board->getUpdatedAt(),
                );
            }

            $response->getBody()->write(json_encode($boardsResponse));
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
     * Método para criação de um quadro
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Post(
        path: '/api/boards/create',
        tags: ['Board'],
        summary: 'Create a new board',
        description: 'This create a new board in the application',
        operationId: 'createBoard',
        responses: [
            new OA\Response(response: 201, description: 'Board was created successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Create a board',
            required: true,
            content: new OA\JsonContent(
                ref: CreateBoardDTO::class
            )
        )
    )]
    public function create(Request $request, Response $response): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));
            $body = $request->getParsedBody();

            if ($authorizedUser->id !== $body['owner']) {
                throw new Exception('User unauthorized', 401);
            }

            $createBoardDTO = new CreateBoardDTO(...$body);
            $this->createBoardUsecase->execute($createBoardDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Board was created successfully'
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
     * Método para atualizar um quadro
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Put(
        path: '/api/boards/{id}',
        tags: ['Board'],
        summary: 'Update a board',
        description: 'This update a board in the application',
        operationId: 'updateBoard',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'board id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Board was updated successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'Board not found'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update a board',
            required: true,
            content: new OA\JsonContent(
                ref: UpdateBoardDTO::class
            )
        )
    )]
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));

            $boardId = (int) $args['id'];

            $body = $request->getParsedBody();
            $owner = $body['owner'];

            if ($authorizedUser->id != $owner) {
                throw new Exception('User unauthorized', 400);
            }

            $updateBoardDTO = new UpdateBoardDTO(...$body);
            $this->updateBoardUsecase->execute($boardId, $owner, $updateBoardDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Board was updated successfully'
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
     * Método para deletar um quadro
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Delete(
        path: '/api/boards/{id}',
        tags: ['Board'],
        summary: 'Delete a board',
        description: 'This delete a board in the application',
        operationId: 'deleteBoard',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'board id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Board was deleted successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'Board not found'),
        ],
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));

            $boardId = (int) $args['id'];
            $board = $this->findBoardUsecase->execute($boardId);

            if ($authorizedUser->id !== $board->getOwner()) {
                throw new Exception('User unauthorized', 400);
            }

            $this->deleteBoardUsecase->execute($boardId);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Board was deleted successfully'
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
