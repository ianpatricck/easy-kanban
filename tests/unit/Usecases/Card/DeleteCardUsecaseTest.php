<?php declare(strict_types=1);

use App\DTO\CreateBoardDTO;
use App\DTO\CreateCardDTO;
use App\DTO\CreateUserDTO;
use App\Entities\Board;
use App\Entities\Card;
use App\Entities\User;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\Card\CreateCardUsecase;
use App\Usecases\Card\DeleteCardUsecase;
use App\Usecases\Card\FindCardUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class DeleteCardUsecaseTest extends TestCase
{
    private static PDO $pdo;
    private static ?User $user;
    private static ?Board $board;
    private static ?Card $card;

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

        // Find the created user
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

        // Create a card
        $createCardContainer = new DI\Container();
        $createCardUsecase = $createCardContainer->get(CreateCardUsecase::class);

        $createCardDTO = new CreateCardDTO(
            name: 'In development',
            hex_bgcolor: '#887744',
            owner: self::$user->getId(),
            board: self::$board->getId()
        );

        $createCardUsecase->execute($createCardDTO);

        // Fetch the last card created
        $stmt = self::$pdo->prepare('SELECT * FROM cards ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        $card = $stmt->fetch();

        $stmt = null;

        self::$card = new Card(
            id: $card->id,
            name: $card->name,
            hex_bgcolor: $card->hex_bgcolor,
            owner: $card->owner,
            board: $card->board,
            created_at: $card->created_at,
            updated_at: $card->updated_at
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->prepare('DELETE FROM users')->execute();
        self::$pdo->prepare('DELETE FROM boards')->execute();
        self::$pdo->prepare('DELETE FROM cards')->execute();
    }

    public function testThrowsANotFoundCardException(): void
    {
        $deleteCardConteiner = new DI\Container();
        $deleteCardUsecase = $deleteCardConteiner->get(DeleteCardUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The card could not be found');
        $this->expectExceptionCode(400);

        $deleteCardUsecase->execute(self::$card->getId() + 2);
    }

    public function testShouldDeleteOneCard(): void
    {
        // Delete one card
        $deleteCardConteiner = new DI\Container();
        $deleteCardUsecase = $deleteCardConteiner->get(DeleteCardUsecase::class);

        $deleteCardUsecase->execute(self::$card->getId());

        // Trying find the card
        $findCardConteiner = new DI\Container();
        $findCardUsecase = $findCardConteiner->get(FindCardUsecase::class);

        $card = $findCardUsecase->execute(self::$card->getId());

        $this->assertNull($card);
    }
}
