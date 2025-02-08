<?php

// |============================================|
// | Entrypoint da aplicação                    |
// |============================================|

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

/*
 * Container que injeta as dependências dos controladores.
 */
$appContainerBuilder = new ContainerBuilder();
$appContainerBuilder->addDefinitions(__DIR__ . '/di-config.php');
$appContainer = $appContainerBuilder->build();

require __DIR__ . '/api.php';
require __DIR__ . '/web.php';

$app->run();
