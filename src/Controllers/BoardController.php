<?php declare(strict_types=1);

// |============================================|
// | Controlador dos quadros                    |
// |============================================|

namespace App\Controllers;

use App\DTO\CreateBoardDTO;
use App\DTO\UpdateBoardDTO;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\Board\FindManyBoardUsecase;
use App\Usecases\Board\UpdateBoardUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class BoardController
{
    public function __construct(
        private AuthorizeUserUsecase $authorizeUserUsecase,
        private CreateBoardUsecase $createBoardUsecase,
        private UpdateBoardUsecase $updateBoardUsecase,
        private FindManyBoardUsecase $findManyBoardUsecase,
    ) {}

    /*
     * Método que retorna vários quadros por um limite definido
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */
    public function findMany(Request $request, Response $response, array $args): Response
    {
        try {
            $boards = $this->findManyBoardUsecase->execute(2);
            $boardsResponse = [];

            foreach ($boards as $board) {
                $boardsResponse[] = [
                    'id' => $board->getId(),
                    'name' => $board->getName(),
                    'description' => $board->getDescription(),
                    'active_users' => $board->getActiveUsers(),
                    'owner' => $board->getOwner(),
                    'created_at' => $board->getCreatedAt(),
                    'updated_at' => $board->getUpdatedAt(),
                ];
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
    public function create(Request $request, Response $response): Response
    {
        try {
            $bearer = $request->getHeaderLine('Authorization');
            $token = explode(' ', $bearer)[1];
            $authorizedUser = $this->authorizeUserUsecase->execute($token);

            $body = $request->getParsedBody();

            if ($authorizedUser->id !== $body['owner']) {
                throw new Exception('User unauthorized', 400);
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
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $bearer = $request->getHeaderLine('Authorization');
            $token = explode(' ', $bearer)[1];
            $authorizedUser = $this->authorizeUserUsecase->execute($token);

            $boardId = (int) $args['id'];
            $owner = (int) $args['ownerId'];
            $body = $request->getParsedBody();

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
}
