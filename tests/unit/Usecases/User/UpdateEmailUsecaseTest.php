<?php declare(strict_types=1);

use App\DTO\CreateUserDTO;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use App\Usecases\User\UpdateEmailUsecase;
use PHPUnit\Framework\TestCase;

final class UpdateEmailUsecaseTest extends TestCase
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

    public function testThrowsAnEmailFormatException(): void
    {
        $updateEmailContainer = new DI\Container();
        $updateEmailUsecase = $updateEmailContainer->get(UpdateEmailUseCase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Email format is not valid');
        $this->expectExceptionCode(400);

        $updateEmailUsecase->execute('guest', 'new_email@.');
    }

    public function testThrowsAnUserNotFoundException(): void
    {
        $updateEmailContainer = new DI\Container();
        $updateEmailUsecase = $updateEmailContainer->get(UpdateEmailUseCase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(404);

        $updateEmailUsecase->execute('unknown_user', 'new_email@mail.com');
    }

    public function testThrowsAnEmailAlreadyExistsException(): void
    {
        $updateEmailContainer = new DI\Container();
        $updateEmailUsecase = $updateEmailContainer->get(UpdateEmailUseCase::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The email is already in use');
        $this->expectExceptionCode(400);

        $updateEmailUsecase->execute('guest', 'guest@mail.com');
    }

    public function testShouldUpdateEmail(): void
    {
        $updateEmailContainer = new DI\Container();
        $updateEmailUsecase = $updateEmailContainer->get(UpdateEmailUseCase::class);

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        $username = 'guest';
        $email = 'newguest@mail.com';

        $updateEmailUsecase->execute($username, $email);
        $user = $findUserUsecase->execute($username);

        $this->assertSame($email, $user->getEmail());
    }
}
