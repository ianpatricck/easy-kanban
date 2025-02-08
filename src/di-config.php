<?php

// |============================================|
// | Exporta os controladores com DI            |
// |============================================|

use App\Controllers\UserController;

use function DI\create;

return [
    'userController' => create(UserController::class)
];
