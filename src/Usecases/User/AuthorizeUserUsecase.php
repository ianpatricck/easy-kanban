<?php declare(strict_types=1);

namespace App\Usecases\User;

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv::createImmutable(dirname(__FILE__, 4));
$dotenv->load();

class AuthorizeUserUsecase
{
    public function execute(string $token)
    {
        $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITM']));
        return $decoded;
    }
}
