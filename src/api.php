<?php

// |============================================|
// | Rotas da API da aplicação                  |
// |============================================|

use App\Controllers\BoardController;
use App\Controllers\CardController;
use App\Controllers\UserController;
use App\Middlewares\UserAuthorizedMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;

/*
 * Grupo de rotas da API.
 *
 * @return void
 */
$app->group('/api', function ($api) use ($appContainer) {
    // Controllers
    $userController = $appContainer->get(UserController::class);
    $boardController = $appContainer->get(BoardController::class);
    $cardController = $appContainer->get(CardController::class);

    /*
     * Middleware para verificar a autenticidade do usuário.
     */
    $userAuthorizedMiddleware = fn(Request $request, RequestHandler $handler) => UserAuthorizedMiddleware::handle($request, $handler);

    /*
     * Corpo da mensagem de boas vindas da rota raiz da API.
     *
     * @return Response
     */
    $welcomeMessageCallback = function (): Response {
        $welcomePayload = json_encode(['message' => 'Easy Kanban API']);

        $response = (new ResponseFactory())->createResponse();
        $response->getBody()->write($welcomePayload);

        return $response->withHeader('Content-Type', 'application/json');
    };

    $api->get('', $welcomeMessageCallback);

    // Users
    $api->post('/users/create', [$userController, 'create']);
    $api->post('/users/login', [$userController, 'login']);
    $api->get('/users/{by}', [$userController, 'findOne'])->add($userAuthorizedMiddleware);
    $api->patch('/users/email/{by}', [$userController, 'updateEmail'])->add($userAuthorizedMiddleware);
    $api->patch('/users/name/{by}', [$userController, 'updateName'])->add($userAuthorizedMiddleware);
    $api->patch('/users/username/{by}', [$userController, 'updateUsername'])->add($userAuthorizedMiddleware);
    $api->patch('/users/description/{by}', [$userController, 'updateBio'])->add($userAuthorizedMiddleware);
    $api->patch('/users/password/{by}', [$userController, 'updatePassword'])->add($userAuthorizedMiddleware);
    $api->delete('/users/{by}', [$userController, 'delete'])->add($userAuthorizedMiddleware);

    // Boards
    $api->get('/boards', [$boardController, 'findMany'])->add($userAuthorizedMiddleware);
    $api->post('/boards/create', [$boardController, 'create'])->add($userAuthorizedMiddleware);
    $api->put('/boards/{id}/{ownerId}', [$boardController, 'update'])->add($userAuthorizedMiddleware);
    $api->delete('/boards/{id}', [$boardController, 'delete'])->add($userAuthorizedMiddleware);

    // Cards
    $api->get('/cards', [$cardController, 'findMany'])->add($userAuthorizedMiddleware);
    $api->get('/cards/{id}', [$cardController, 'findOne'])->add($userAuthorizedMiddleware);
    $api->post('/cards/create', [$cardController, 'create'])->add($userAuthorizedMiddleware);
    $api->put('/cards/{id}', [$cardController, 'update'])->add($userAuthorizedMiddleware);
});
