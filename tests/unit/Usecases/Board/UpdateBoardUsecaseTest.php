<?php declare(strict_types=1);

use App\DTO\CreateBoardDTO;
use App\DTO\CreateUserDTO;
use App\DTO\UpdateBoardDTO;
use App\Entities\Board;
use App\Entities\User;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\Board\FindBoardUsecase;
use App\Usecases\Board\UpdateBoardUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class UpdateBoardUsecaseTest extends TestCase
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
        // Initialize PDO connection
        self::$pdo = new PDO('sqlite:development.db');
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        // Create a user
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

    public function testThrowsAnEmptyNameException(): void
    {
        $updateBoardContainer = new DI\Container();
        $updateBoardUsecase = $updateBoardContainer->get(UpdateBoardUsecase::class);

        $id = self::$board->getId();
        $owner = self::$board->getOwner();

        $input = new UpdateBoardDTO(
            name: '',
            description: 'My board description'
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The board's name cannot be empty");
        $this->expectExceptionCode(400);

        $updateBoardUsecase->execute($id, $owner, $input);
    }

    public function testThrowsAnOwnerNotFoundException(): void
    {
        $updateBoardContainer = new DI\Container();
        $updateBoardUsecase = $updateBoardContainer->get(UpdateBoardUsecase::class);

        $id = self::$board->getId();
        $owner = self::$board->getOwner() + 5;
        $input = new UpdateBoardDTO(
            name: 'New board name',
            description: 'My board description'
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Board's owner not found");
        $this->expectExceptionCode(404);

        $updateBoardUsecase->execute($id, $owner, $input);
    }

    public function testThrowsBoardNotFoundException(): void
    {
        $updateBoardContainer = new DI\Container();
        $updateBoardUsecase = $updateBoardContainer->get(UpdateBoardUsecase::class);

        $id = self::$board->getId() + 5;
        $owner = self::$board->getOwner();
        $input = new UpdateBoardDTO(
            name: 'New board name',
            description: 'My board description'
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Board not found');
        $this->expectExceptionCode(404);

        $updateBoardUsecase->execute($id, $owner, $input);
    }

    public function testShouldUpdateTheBoard(): void
    {
        $updateBoardContainer = new DI\Container();
        $updateBoardUsecase = $updateBoardContainer->get(UpdateBoardUsecase::class);

        $findBoardContainer = new DI\Container();
        $findBoardUsecase = $findBoardContainer->get(FindBoardUsecase::class);

        $id = self::$board->getId();
        $owner = self::$board->getOwner();
        $input = new UpdateBoardDTO(
            name: 'Updated board name',
            description: 'My board description'
        );

        $updateBoardUsecase->execute($id, $owner, $input);

        $updatedBoard = $findBoardUsecase->execute($id);

        $this->assertSame($input->name, $updatedBoard->getName());
        $this->assertSame($input->description, $updatedBoard->getDescription());
        $this->assertSame($owner, $updatedBoard->getOwner());
    }
}
