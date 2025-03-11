<?php declare(strict_types=1);

use App\DTO\CreateUserDTO;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use App\Usecases\User\UpdateUsernameUsecase;
use PHPUnit\Framework\TestCase;

final class UpdateUsernameUsecaseTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public static function setUpBeforeClass(): void
    {
        $createUserDTO = new CreateUserDTO(
            username: 'guest',
            name: 'Guest user',
            email: 'guest@mail.com',
            password: 'mypass#123',
        );

        $createUserContainer = new DI\Container();
        $createUserUsecase = $createUserContainer->get(CreateUserUseCase::class);
        $createUserUsecase->execute($createUserDTO);
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = new PDO('sqlite:development.db');
        $pdo->prepare('DELETE FROM users')->execute();
    }

    public function testThrowsANotAllowedUsernameException(): void
    {
        $updateUsernameContainer = new DI\Container();
        $updateUsernameUsecase = $updateUsernameContainer->get(UpdateUsernameUseCase::class);

        $currentUsername = 'guest';
        $newUsername = 'mynewusername12~@';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($newUsername . ' is not a valid username');
        $this->expectExceptionCode(400);

        $updateUsernameUsecase->execute($currentUsername, $newUsername);
    }

    public function testThrowsAnUsernameAlreadyInUseException(): void
    {
        $updateUsernameContainer = new DI\Container();
        $updateUsernameUsecase = $updateUsernameContainer->get(UpdateUsernameUseCase::class);

        $username = 'guest';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($username . ' is already in use');
        $this->expectExceptionCode(400);

        $updateUsernameUsecase->execute($username, $username);
    }

    public function testShouldUpdateUsername(): void
    {
        $updateUsernameContainer = new DI\Container();
        $updateUsernameUsecase = $updateUsernameContainer->get(UpdateUsernameUseCase::class);

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        $currentUsername = 'guest';
        $newUsername = 'updated_guest';

        $updateUsernameUsecase->execute($currentUsername, $newUsername);
        $user = $findUserUsecase->execute($newUsername);

        $this->assertSame($newUsername, $user->getUsername());
    }
}
