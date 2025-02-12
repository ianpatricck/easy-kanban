<?php declare(strict_types=1);

use App\DTO\CreateBoardDTO;
use App\DTO\CreateUserDTO;
use App\Entities\Board;
use App\Entities\User;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\Board\DeleteBoardUsecase;
use App\Usecases\Board\FindBoardUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class DeleteBoardUsecaseTest extends TestCase
{
    private static PDO $pdo;
    private static ?User $user;
    private static ?Board $board;

    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public static function setUpBeforeClass(): void
    {
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

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        self::$user = $findUserUsecase->execute($createUserDTO->username);

        // Create a board
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

        self::$board = new Board(
            id: $board->id,
            name: $board->name,
            description: $board->description,
            active_users: $board->active_users,
            created_at: $board->created_at,
            updated_at: $board->updated_at,
            owner: $board->owner,
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->prepare('DELETE FROM users')->execute();
        self::$pdo->prepare('DELETE FROM boards')->execute();
    }

    public function testThrowsABoardNotFoundException(): void
    {
        $deleteBoardContainer = new DI\Container();
        $deleteBoardUseCase = $deleteBoardContainer->get(DeleteBoardUsecase::class);

        $id = self::$board->getId() + 5;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Board not found');
        $this->expectExceptionCode(404);

        $deleteBoardUseCase->execute($id);
    }

    public function testShouldDeleteAnBoard(): void
    {
        // Delete the board
        $deleteBoardContainer = new DI\Container();
        $deleteBoardUseCase = $deleteBoardContainer->get(DeleteBoardUsecase::class);

        $id = self::$board->getId();
        $deleteBoardUseCase->execute($id);

        // Find the board
        $findBoardContainer = new DI\Container();
        $findBoardUseCase = $findBoardContainer->get(FindBoardUsecase::class);

        $board = $findBoardUseCase->execute($id);

        $this->assertNull($board);
    }
}
