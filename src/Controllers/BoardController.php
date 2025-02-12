<?php declare(strict_types=1);

// |============================================|
// | Controlador dos quadros                    |
// |============================================|

namespace App\Controllers;

use App\DTO\CreateBoardDTO;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class BoardController
{
    public function __construct(
        private CreateBoardUsecase $createBoardUsecase,
        private AuthorizeUserUsecase $authorizeUserUsecase,
    ) {}

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
                throw new Exception('User is not authorized', 400);
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
}
