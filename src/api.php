<?php

// |============================================|
// | Rotas da API da aplicação                  |
// |============================================|

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
$app->group('/api', function ($group) use ($appContainer) {
    $userController = $appContainer->get(UserController::class);

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

    $group->get('', $welcomeMessageCallback);
    $group->post('/users/create', [$userController, 'create']);
    $group->post('/users/login', [$userController, 'login']);
    $group
        ->get('/users/{by}', [$userController, 'findOne'])
        ->add($userAuthorizedMiddleware);
    $group
        ->patch('/users/email/update/{by}', [$userController, 'updateEmail'])
        ->add($userAuthorizedMiddleware);
});
