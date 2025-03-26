<?php declare(strict_types=1);

use App\DTO\CreateBoardDTO;
use App\DTO\CreateCardDTO;
use App\DTO\CreateUserDTO;
use App\Entities\Board;
use App\Entities\User;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\Card\CreateCardUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class CreateCardUsecaseTest extends TestCase
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

        // Create board
        $createBoardContainer = new DI\Container();
        $createBoardUsecase = $createBoardContainer->get(CreateBoardUsecase::class);

        $createBoardDTO = new CreateBoardDTO(
            name: 'My test board',
            description: 'This is a simple test board',
            owner: self::$user->getId()
        );

        $createBoardUsecase->execute($createBoardDTO);

        // Fetch the last board
        $stmt = self::$pdo->prepare('SELECT * FROM boards ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        $board = $stmt->fetch();

        $stmt = null;

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
        self::$pdo->prepare('DELETE FROM cards')->execute();
    }

    public function testThrowsAnEmptyNameException(): void
    {
        $createCardContainer = new DI\Container();
        $createCardUsecase = $createCardContainer->get(CreateCardUsecase::class);

        $createCardDTO = new CreateCardDTO(
            name: '',
            hex_bgcolor: '',
            owner: self::$user->getId(),
            board: self::$board->getId()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The card's name was not provided");
        $this->expectExceptionCode(400);

        $createCardUsecase->execute($createCardDTO);
    }

    public function testThrowsAnInvalidColorFormatException(): void
    {
        $createCardContainer = new DI\Container();
        $createCardUsecase = $createCardContainer->get(CreateCardUsecase::class);

        $createCardDTO = new CreateCardDTO(
            name: 'In development',
            hex_bgcolor: '3433432134',
            owner: self::$user->getId(),
            board: self::$board->getId()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid color format');
        $this->expectExceptionCode(400);

        $createCardUsecase->execute($createCardDTO);
    }

    public function testThrowsAnInvalidNameException(): void
    {
        $createCardContainer = new DI\Container();
        $createCardUsecase = $createCardContainer->get(CreateCardUsecase::class);

        $createCardDTO = new CreateCardDTO(
            name: 'In development<2>',
            hex_bgcolor: '#887744',
            owner: self::$user->getId(),
            board: self::$board->getId()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Name's format is not valid");
        $this->expectExceptionCode(400);

        $createCardUsecase->execute($createCardDTO);
    }

    public function testShouldCreateACard(): void
    {
        $createCardContainer = new DI\Container();
        $createCardUsecase = $createCardContainer->get(CreateCardUsecase::class);

        $createCardDTO = new CreateCardDTO(
            name: 'In development',
            hex_bgcolor: '#887744',
            owner: self::$user->getId(),
            board: self::$board->getId()
        );

        $createCardUsecase->execute($createCardDTO);

        // Fetch the last card
        $stmt = self::$pdo->prepare('SELECT * FROM cards ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        $card = $stmt->fetch();

        $stmt = null;

        $this->assertSame($createCardDTO->name, $card->name);
    }
}
