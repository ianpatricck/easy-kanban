<?php

// |============================================|
// | Rotas de visualização (UI)                 |
// |============================================|

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

/*
 * Carrega os templates de UI e os adiciona ao middleware Twig.
 */
$twig = Twig::create(__DIR__ . '/UI/templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

/*
 * Grupo de rotas web.
 *
 * @return void
 */
$app->group('/', function ($group) use ($appContainer) {
    $group->get('', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig');
    });
});
