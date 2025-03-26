<?php declare(strict_types=1);

use App\DTO\CreateBoardDTO;
use App\DTO\CreateCardDTO;
use App\DTO\CreateTaskDTO;
use App\DTO\CreateUserDTO;
use App\Entities\Board;
use App\Entities\Card;
use App\Entities\User;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\Board\FindManyBoardUsecase;
use App\Usecases\Card\CreateCardUsecase;
use App\Usecases\Card\FindManyCardUsecase;
use App\Usecases\Task\CreateTaskUsecase;
use App\Usecases\Task\FindManyTaskUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class CreateTaskUsecaseTest extends TestCase
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
        $findManyBoardContainer = new DI\Container();
        $findManyBoardUsecase = $findManyBoardContainer->get(FindManyBoardUsecase::class);

        $boards = $findManyBoardUsecase->execute();
        $board = end($boards);

        self::$board = new Board(
            id: $board->getId(),
            name: $board->getName(),
            description: $board->getDescription(),
            active_users: $board->getActiveUsers(),
            created_at: $board->getCreatedAt(),
            updated_at: $board->getUpdatedAt(),
            owner: $board->getOwner(),
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

        // Fetch the last card
        $findManyCardContainer = new DI\Container();
        $findManyCardUsecase = $findManyCardContainer->get(FindManyCardUsecase::class);

        $cards = $findManyCardUsecase->execute();
        $card = end($cards);

        self::$card = new Card(
            id: $card->getId(),
            name: $card->getName(),
            hex_bgcolor: $card->getHexBgColor(),
            owner: $card->getId(),
            board: $card->getBoard(),
            created_at: $card->getCreatedAt(),
            updated_at: $card->getUpdatedAt(),
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->prepare('DELETE FROM users')->execute();
        self::$pdo->prepare('DELETE FROM boards')->execute();
        self::$pdo->prepare('DELETE FROM cards')->execute();
        self::$pdo->prepare('DELETE FROM tasks')->execute();
    }

    public function testThrowsAnEmptyTitleException(): void
    {
        $createTaskContainer = new DI\Container();
        $createTaskUsecase = $createTaskContainer->get(CreateTaskUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The task's title was not provided");
        $this->expectExceptionCode(401);

        $taskPayload = new CreateTaskDTO(
            title: '',
            body: 'This is my first task',
            hex_bgcolor: '#ffffff',
            owner: 2,
            attributed_to: 2,
            card: 1
        );

        $createTaskUsecase->execute($taskPayload);
    }

    public function testThrowsAnInvalidColorFormatException(): void
    {
        $createTaskContainer = new DI\Container();
        $createTaskUsecase = $createTaskContainer->get(CreateTaskUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid color format');
        $this->expectExceptionCode(401);

        $taskPayload = new CreateTaskDTO(
            title: 'This is the first task!',
            body: 'This is my first task',
            hex_bgcolor: 'ff1fff',
            owner: 2,
            attributed_to: 2,
            card: 1
        );

        $createTaskUsecase->execute($taskPayload);
    }

    public function testThrowsAnOwnerNotFoundException(): void
    {
        $createTaskContainer = new DI\Container();
        $createTaskUsecase = $createTaskContainer->get(CreateTaskUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Owner not found');
        $this->expectExceptionCode(404);

        $taskPayload = new CreateTaskDTO(
            title: 'This is the first task!',
            body: 'This is my first task',
            hex_bgcolor: '#000000',
            owner: self::$user->getId() * 10,
            attributed_to: 4,
            card: 1
        );

        $createTaskUsecase->execute($taskPayload);
    }

    public function testThrowsAnAttributedUserNotFoundException(): void
    {
        $createTaskContainer = new DI\Container();
        $createTaskUsecase = $createTaskContainer->get(CreateTaskUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(404);

        $taskPayload = new CreateTaskDTO(
            title: 'This is the first task!',
            body: 'This is my first task',
            hex_bgcolor: '#000000',
            owner: self::$user->getId(),
            attributed_to: self::$user->getId() * 10,
            card: 1
        );

        $createTaskUsecase->execute($taskPayload);
    }

    public function testThrowsACardNotFoundException(): void
    {
        $createTaskContainer = new DI\Container();
        $createTaskUsecase = $createTaskContainer->get(CreateTaskUsecase::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Card not found');
        $this->expectExceptionCode(404);

        $taskPayload = new CreateTaskDTO(
            title: 'first task!',
            body: 'This is my first task',
            hex_bgcolor: '#000000',
            owner: self::$user->getId(),
            attributed_to: self::$user->getId(),
            card: self::$card->getId() * 10,
        );

        $createTaskUsecase->execute($taskPayload);
    }

    public function testShouldCrateOneTask(): void
    {
        // Create a task
        $taskContainer = new DI\Container();
        $createTaskUsecase = $taskContainer->get(CreateTaskUsecase::class);

        $taskPayload = new CreateTaskDTO(
            title: 'first task!',
            body: 'This is my first task',
            hex_bgcolor: '#000000',
            owner: self::$user->getId(),
            attributed_to: self::$user->getId(),
            card: self::$card->getId(),
        );

        $createTaskUsecase->execute($taskPayload);

        // Find the task
        $findManyTaskUsecase = $taskContainer->get(FindManyTaskUsecase::class);
        $tasks = $findManyTaskUsecase->execute(2);

        $lastTask = end($tasks);

        $this->assertSame($taskPayload->title, $lastTask->getTitle());
        $this->assertSame($taskPayload->body, $lastTask->getBody());
        $this->assertSame($taskPayload->owner, $lastTask->getOwner());
        $this->assertSame($taskPayload->attributed_to, $lastTask->getAttributedTo());
        $this->assertSame($taskPayload->card, $lastTask->getCard());
    }
}
