<?php declare(strict_types=1);

use App\DTO\CreateUserDTO;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use App\Usecases\User\UpdateUserBioUsecase;
use PHPUnit\Framework\TestCase;

final class UpdateUserBioUsecaseTest extends TestCase
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

    public function testShouldUpdateUserDescription(): void
    {
        $updateUserBioContainer = new DI\Container();
        $updateUserBioUsecase = $updateUserBioContainer->get(UpdateUserBioUsecase::class);

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        $username = 'guest';
        $bio = "Hello there! I'm noobie here.";

        $updateUserBioUsecase->execute($username, $bio);
        $user = $findUserUsecase->execute($username);

        $this->assertSame($bio, $user->getBio());
    }
}
