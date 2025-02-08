<?php declare(strict_types=1);

// |============================================|
// | Middleware de autorização do usuário       |
// |============================================|

namespace App\Middlewares;

use App\Usecases\User\AuthorizeUserUsecase;
use Firebase\JWT\SignatureInvalidException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;
use Exception;

final class UserAuthorizedMiddleware
{
    /*
     * Middleware que verifica se o usuário está autorizado.
     *
     * @param Request $request
     * @param RequestHandler $handler
     *
     * @return Response
     */
    public static function handle(Request $request, RequestHandler $handler): Response
    {
        $responseFactory = new ResponseFactory();
        $authToken = $request->getHeaderLine('Authorization');

        try {
            if (!$authToken) {
                throw new Exception('Unauthorized user', 401);
            }

            $token = explode(' ', $authToken)[1] ?? null;

            if (!$token) {
                throw new Exception('Unauthorized user', 401);
            }

            $authorizateUserUsecase = new AuthorizeUserUsecase();
            $authorized = $authorizateUserUsecase->execute($token);

            if ($authorized) {
                return $handler->handle($request);
            }
        } catch (SignatureInvalidException $exception) {
            $response = $responseFactory->createResponse();
            $response->getBody()->write(
                json_encode([
                    'message' => 'Invalid authenticated user'
                ])
            );

            return $response->withStatus(401);
        } catch (Exception $exception) {
            $response = $responseFactory->createResponse();
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }
}
