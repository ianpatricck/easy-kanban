<?php declare(strict_types=1);

use App\DTO\CreateUserDTO;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use App\Usecases\User\UpdatePasswordUsecase;
use PHPUnit\Framework\TestCase;

final class UpdatePasswordUsecaseTest extends TestCase
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

    public function testThrowsAnInvalidPasswordSizeException(): void
    {
        $updatePasswordContainer = new DI\Container();
        $updatePasswordUsecase = $updatePasswordContainer->get(UpdatePasswordUseCase::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be greater than 8 characters');
        $this->expectExceptionCode(400);

        $updatePasswordUsecase->execute('guest', 'mypass#123', 'newpass');
    }

    public function testThrowsAnOldPasswordDoNotMatchException(): void
    {
        $updatePasswordContainer = new DI\Container();
        $updatePasswordUsecase = $updatePasswordContainer->get(UpdatePasswordUseCase::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Old password don't match");
        $this->expectExceptionCode(400);

        $updatePasswordUsecase->execute('guest', 'mypass#321', 'newpassword21');
    }

    public function testShouldUpdatePassword(): void
    {
        $updatePasswordContainer = new DI\Container();
        $updatePasswordUsecase = $updatePasswordContainer->get(UpdatePasswordUseCase::class);

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        $username = 'guest';
        $oldPassword = 'mypass#123';
        $newPassword = 'newpassword123';

        $updatePasswordUsecase->execute($username, $oldPassword, $newPassword);

        $user = $findUserUsecase->execute($username);

        $this->assertTrue(password_verify($newPassword, $user->getPassword()));
    }
}
