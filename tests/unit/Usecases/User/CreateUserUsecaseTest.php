<?php declare(strict_types=1);

use App\DTO\CreateUserDTO;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class CreateUserUsecaseTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = new PDO('sqlite:development.db');
        $pdo->prepare('DELETE FROM users')->execute();
    }

    public function testShouldCreateAnUser(): void
    {
        $createUserDTO = new CreateUserDTO(
            username: 'guest',
            name: 'Guest user',
            email: 'guest@mail.com',
            password: 'userpass#123',
        );

        $container = new DI\Container();
        $createUserUsecase = $container->get(CreateUserUseCase::class);

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        $createUserUsecase->execute($createUserDTO);
        $user = $findUserUsecase->execute($createUserDTO->username);

        $this->assertSame($createUserDTO->username, $user->getUsername());
    }

    public function testThrowsADuplicateUsernameException(): void
    {
        $createUserDTO = new CreateUserDTO(
            username: 'guest',
            name: 'Guest user',
            email: 'anotherguest@mail.com',
            password: 'userpass#123',
        );

        $container = new DI\Container();
        $createUserUsecase = $container->get(CreateUserUseCase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The username is already in use');
        $this->expectExceptionCode(400);

        $createUserUsecase->execute($createUserDTO);
    }

    public function testThrowsADuplicateEmailException(): void
    {
        $createUserDTO = new CreateUserDTO(
            username: 'anotherguest',
            name: 'Guest user',
            email: 'guest@mail.com',
            password: 'userpass#123',
        );

        $container = new DI\Container();
        $createUserUsecase = $container->get(CreateUserUseCase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The email is already in use');
        $this->expectExceptionCode(400);

        $createUserUsecase->execute($createUserDTO);
    }

    public function testThrowsAnInvalidEmailException(): void
    {
        $createUserDTO = new CreateUserDTO(
            username: 'guest_2',
            name: 'Guest user',
            email: 'guestmail.com',
            password: 'userpass#123',
        );

        $container = new DI\Container();
        $createUserUsecase = $container->get(CreateUserUseCase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Email format is not valid');
        $this->expectExceptionCode(400);

        $createUserUsecase->execute($createUserDTO);
    }

    public function testThrowsAnInvalidPasswordSizeException(): void
    {
        $createUserDTO = new CreateUserDTO(
            username: 'guest_3',
            name: 'Guest user',
            email: 'guest3@mail.com',
            password: 'mypass',
        );

        $container = new DI\Container();
        $createUserUsecase = $container->get(CreateUserUseCase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must be greater than 8 characters');
        $this->expectExceptionCode(400);

        $createUserUsecase->execute($createUserDTO);
    }
}
