<?php declare(strict_types=1);

use App\DTO\CreateBoardDTO;
use App\DTO\CreateUserDTO;
use App\Entities\User;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class CreateBoardUsecaseTest extends TestCase
{
    private static PDO $pdo;
    private static ?User $user;

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

        $createUserContainer = new DI\Container();
        $createUserUsecase = $createUserContainer->get(CreateUserUsecase::class);
        $createUserUsecase->execute($createUserDTO);

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        self::$user = $findUserUsecase->execute($createUserDTO->username);
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->prepare('DELETE FROM users')->execute();
        self::$pdo->prepare('DELETE FROM boards')->execute();
    }

    public function testThrowsAnEmptyNameException(): void
    {
        $createBoardContainer = new DI\Container();
        $createBoardUseCase = $createBoardContainer->get(CreateBoardUsecase::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The board's name was not provided");
        $this->expectExceptionCode(400);

        $createBoardUseCase->execute(new CreateBoardDTO(name: '', owner: self::$user->getId()));
    }

    public function testShouldCreateABoard(): void
    {
        $createBoardContainer = new DI\Container();
        $createBoardUseCase = $createBoardContainer->get(CreateBoardUsecase::class);

        $createBoardDTO = new CreateBoardDTO(
            name: 'My test board',
            description: 'This is a simple test board',
            owner: self::$user->getId()
        );

        $createBoardUseCase->execute($createBoardDTO);

        // Fetch the last board
        $stmt = self::$pdo->prepare('SELECT * FROM boards ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        $board = $stmt->fetch();

        $stmt = null;

        $this->assertSame($createBoardDTO->name, $board->name);
    }
}
