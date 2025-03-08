<?php

// |============================================|
// | Entrypoint da aplicação                    |
// |============================================|

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/utils/isAuthorizedUser.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

/*
 * Container que injeta as dependências dos controladores.
 */
$appContainerBuilder = new ContainerBuilder();
$appContainerBuilder->addDefinitions(__DIR__ . '/di-config.php');
$appContainer = $appContainerBuilder->build();

/*
 * Configurando o Twig template engine.
 */
$twig = Twig::create(__DIR__ . '/', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

require __DIR__ . '/api.php';

$app->run();
