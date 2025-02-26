<?php declare(strict_types=1);

use App\DTO\CreateBoardDTO;
use App\DTO\CreateCardDTO;
use App\DTO\CreateTaskDTO;
use App\DTO\CreateUserDTO;
use App\Entities\Board;
use App\Entities\Card;
use App\Entities\Task;
use App\Entities\User;
use App\Usecases\Board\CreateBoardUsecase;
use App\Usecases\Board\FindManyBoardUsecase;
use App\Usecases\Card\CreateCardUsecase;
use App\Usecases\Card\FindManyCardUsecase;
use App\Usecases\Task\CreateTaskUsecase;
use App\Usecases\Task\DeleteTaskUsecase;
use App\Usecases\Task\FindManyTaskUsecase;
use App\Usecases\Task\FindTaskUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class DeleteTaskUsecaseTest extends TestCase
{
    private static PDO $pdo;
    private static ?User $user;
    private static ?Board $board;
    private static ?Card $card;
    private static ?Task $task;

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

        $boards = $findManyBoardUsecase->execute(limit: 2);
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
            board: self::$board->getId()
        );

        $createCardUsecase->execute($createCardDTO);

        // Fetch the last card
        $findManyCardContainer = new DI\Container();
        $findManyCardUsecase = $findManyCardContainer->get(FindManyCardUsecase::class);

        $cards = $findManyCardUsecase->execute(limit: 2);
        $card = end($cards);

        self::$card = new Card(
            id: $card->getId(),
            name: $card->getName(),
            hex_bgcolor: $card->getHexBgColor(),
            board: $card->getBoard(),
            created_at: $card->getCreatedAt(),
            updated_at: $card->getUpdatedAt(),
        );

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

        self::$task = end($tasks);
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->prepare('DELETE FROM users')->execute();
        self::$pdo->prepare('DELETE FROM boards')->execute();
        self::$pdo->prepare('DELETE FROM cards')->execute();
        self::$pdo->prepare('DELETE FROM tasks')->execute();
    }

    public function testThrowsATaskNotFoundException(): void
    {
        $taskContainer = new DI\Container();
        $deleteTaskUsecase = $taskContainer->get(DeleteTaskUsecase::class);

        $this->expectExceptionMessage(Exception::class);
        $this->expectExceptionMessage('Task not found');
        $this->expectExceptionCode(404);

        $deleteTaskUsecase->execute(self::$task->getId() + 10);
    }

    public function testShouldDeleteTask(): void
    {
        $taskContainer = new DI\Container();
        $deleteTaskUsecase = $taskContainer->get(DeleteTaskUsecase::class);
        $findManyTaskUsecase = $taskContainer->get(FindManyTaskUsecase::class);

        // Deleting the task
        $deleteTaskUsecase->execute(self::$task->getId());

        // Trying to find any task
        $this->expectExceptionMessage(Exception::class);
        $this->expectExceptionMessage('Tasks could not be found');
        $this->expectExceptionCode(404);

        $findManyTaskUsecase->execute(1);
    }
}
