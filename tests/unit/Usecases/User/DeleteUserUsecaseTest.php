<?php declare(strict_types=1);

use App\DTO\CreateUserDTO;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\DeleteUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class DeleteUserUsecaseTest extends TestCase
{
    private static PDO $pdo;

    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public static function setUpBeforeClass(): void
    {
        // Initialize PDO connection
        self::$pdo = new PDO('sqlite:development.db');
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        // Create user
        $createUserDTO = new CreateUserDTO(
            username: 'guest',
            name: 'Guest user',
            email: 'guest@mail.com',
            password: 'mypass#123',
        );

        $createUserContainer = new DI\Container();
        $createUserUsecase = $createUserContainer->get(CreateUserUsecase::class);
        $createUserUsecase->execute($createUserDTO);
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->prepare('DELETE FROM users')->execute();
    }

    public function testThrowsANotFoundUserException(): void
    {
        $deleteUserContainer = new DI\Container();
        $deleteUserUsecase = $deleteUserContainer->get(DeleteUserUsecase::class);

        $unknownId = 01233;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(404);

        $deleteUserUsecase->execute($unknownId);
    }

    public function testShouldDeleteAnUser(): void
    {
        $deleteUserContainer = new DI\Container();
        $deleteUserUsecase = $deleteUserContainer->get(DeleteUserUsecase::class);

        $username = 'guest';
        $deleteUserUsecase->execute($username);

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(404);

        $findUserUsecase->execute($username);
    }
}
