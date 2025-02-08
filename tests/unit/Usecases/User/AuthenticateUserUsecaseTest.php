<?php declare(strict_types=1);

use App\DTO\AuthenticateUserDTO;
use App\DTO\CreateUserDTO;
use App\Usecases\User\AuthenticateUserUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use App\Usecases\User\CreateUserUsecase;
use PHPUnit\Framework\TestCase;

final class AuthenticateUserUsecaseTest extends TestCase
{
    private static PDO $pdo;

    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('sqlite:development.db');
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        $createUserDTO = new CreateUserDTO(
            username: 'guest',
            name: 'Guest user',
            email: 'guest@mail.com',
            password: 'mypass#123',
        );

        // Create the test user
        $createUserContainer = new DI\Container();
        $createUserUsecase = $createUserContainer->get(CreateUserUsecase::class);
        $createUserUsecase->execute($createUserDTO);
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->prepare('DELETE FROM users')->execute();
    }

    public function testThrowsNotFoundAccountException(): void
    {
        $authenticateUserContainer = new DI\Container();
        $authenticateUserUsecase = $authenticateUserContainer->get(AuthenticateUserUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("This account doesn't exist");
        $this->expectExceptionCode(404);

        $authenticateUserDTO = new AuthenticateUserDTO(email: 'invalid_email', password: 'mypass123');
        $authenticateUserUsecase->execute($authenticateUserDTO);
    }

    public function testThrowsIncorrectPasswordException(): void
    {
        $authenticateUserContainer = new DI\Container();
        $authenticateUserUsecase = $authenticateUserContainer->get(AuthenticateUserUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Incorrect password');
        $this->expectExceptionCode(401);

        $authenticateUserDTO = new AuthenticateUserDTO(email: 'guest@mail.com', password: 'mypass123212');
        $authenticateUserUsecase->execute($authenticateUserDTO);
    }

    public function testShouldAuthenticateAndAuthorizeUser(): void
    {
        // Authenticate user
        $authenticateUserContainer = new DI\Container();
        $authenticateUserUsecase = $authenticateUserContainer->get(AuthenticateUserUsecase::class);
        $authenticateUserDTO = new AuthenticateUserDTO(email: 'guest@mail.com', password: 'mypass#123');

        $token = $authenticateUserUsecase->execute($authenticateUserDTO);

        $this->assertIsString($token);

        // Authorize user
        $authorizeUserUsecase = new AuthorizeUserUsecase();
        $authorized = $authorizeUserUsecase->execute($token);

        $this->assertArrayHasKey('id', get_object_vars($authorized));
        $this->assertArrayHasKey('email', get_object_vars($authorized));
    }
}
