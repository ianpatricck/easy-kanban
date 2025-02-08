<?php declare(strict_types=1);

namespace App\Usecases\User;

use App\Data\Repositories\UserRepository;
use App\DTO\AuthenticateUserDTO;
use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Exception;

$dotenv = Dotenv::createImmutable(dirname(__FILE__, 4));
$dotenv->load();

class AuthenticateUserUsecase
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(AuthenticateUserDTO $authenticateUserDTO): string
    {
        $user = $this->userRepository->findOneByEmail($authenticateUserDTO->email);

        if (!$user) {
            throw new Exception("This account doesn't exist", 404);
        }

        if (!password_verify($authenticateUserDTO->password, $user->getPassword())) {
            throw new Exception('Incorrect password', 401);
        }

        $payload = [
            'exp' => time() + (60 * 10),
            'iat' => time(),
            'id' => $user->getId(),
            'email' => $user->getEmail()
        ];

        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITM']);

        return $token;
    }
}
