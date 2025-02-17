<?php

// |============================================|
// | Exporta os controladores com DI            |
// |============================================|

use App\Controllers\BoardController;
use App\Controllers\CardController;
use App\Controllers\UserController;

use function DI\create;

return [
    'userController' => create(UserController::class),
    'boardController' => create(BoardController::class),
    'cardController' => create(CardController::class),
];
