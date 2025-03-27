<?php declare(strict_types=1);

use App\DTO\CreateBoardDTO;
use App\DTO\CreateCardDTO;
use App\DTO\CreateCommentDTO;
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
use App\Usecases\Comment\CreateCommentUsecase;
use App\Usecases\Comment\FindManyCommentUsecase;
use App\Usecases\Task\CreateTaskUsecase;
use App\Usecases\Task\FindManyTaskUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\FindUserUsecase;
use PHPUnit\Framework\TestCase;

final class CreateCommentUsecaseTest extends TestCase
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

        $container = new DI\Container();

        // Create user
        $createUserDTO = new CreateUserDTO(
            username: 'guest',
            name: 'Guest user',
            email: 'guest@mail.com',
            password: 'mypass#123',
        );

        $createUserUsecase = $container->get(CreateUserUsecase::class);
        $createUserUsecase->execute($createUserDTO);

        $findUserContainer = new DI\Container();
        $findUserUsecase = $findUserContainer->get(FindUserUsecase::class);

        self::$user = $findUserUsecase->execute($createUserDTO->username);

        // Create board
        $createBoardUsecase = $container->get(CreateBoardUsecase::class);

        $createBoardDTO = new CreateBoardDTO(
            name: 'My test board',
            description: 'This is a simple test board',
            owner: self::$user->getId()
        );

        $createBoardUsecase->execute($createBoardDTO);

        // Fetch the last board
        $findManyBoardUsecase = $container->get(FindManyBoardUsecase::class);

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
        $createCardUsecase = $container->get(CreateCardUsecase::class);

        $createCardDTO = new CreateCardDTO(
            name: 'In development',
            hex_bgcolor: '#887744',
            owner: self::$user->getId(),
            board: self::$board->getId()
        );

        $createCardUsecase->execute($createCardDTO);

        // Fetch the last card
        $findManyCardUsecase = $container->get(FindManyCardUsecase::class);

        $cards = $findManyCardUsecase->execute();
        $card = end($cards);

        self::$card = new Card(
            id: $card->getId(),
            name: $card->getName(),
            hex_bgcolor: $card->getHexBgColor(),
            owner: self::$user->getId(),
            board: $card->getBoard(),
            created_at: $card->getCreatedAt(),
            updated_at: $card->getUpdatedAt(),
        );

        // Create a task
        $createTaskUsecase = $container->get(CreateTaskUsecase::class);

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
        $findManyTaskUsecase = $container->get(FindManyTaskUsecase::class);
        $tasks = $findManyTaskUsecase->execute();

        self::$task = end($tasks);
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->prepare('DELETE FROM users')->execute();
        self::$pdo->prepare('DELETE FROM boards')->execute();
        self::$pdo->prepare('DELETE FROM cards')->execute();
        self::$pdo->prepare('DELETE FROM tasks')->execute();
        self::$pdo->prepare('DELETE FROM comments')->execute();
    }

    public function testThrowsAnEmptyCommentException(): void
    {
        $container = new DI\Container();
        $createCommentUsecase = $container->get(CreateCommentUsecase::class);

        $commentPayload = new CreateCommentDTO(
            body: '',
            owner: self::$user->getId(),
            task: self::$task->getId(),
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The comment cannot be empty');
        $this->expectExceptionCode(400);

        $createCommentUsecase->execute($commentPayload);
    }

    public function testThrowsANotFoundOwnerException(): void
    {
        $container = new DI\Container();
        $createCommentUsecase = $container->get(CreateCommentUsecase::class);

        $commentPayload = new CreateCommentDTO(
            body: 'This is my first comment!',
            owner: self::$user->getId() + 10,
            task: self::$task->getId(),
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Owner cannot be found');
        $this->expectExceptionCode(404);

        $createCommentUsecase->execute($commentPayload);
    }

    public function testThrowsANotFoundTaskException(): void
    {
        $container = new DI\Container();
        $createCommentUsecase = $container->get(CreateCommentUsecase::class);

        $commentPayload = new CreateCommentDTO(
            body: 'This is my first comment!',
            owner: self::$user->getId(),
            task: self::$task->getId() + 10,
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Task cannot be found');
        $this->expectExceptionCode(404);

        $createCommentUsecase->execute($commentPayload);
    }

    public function testShouldCreateOneComment(): void
    {
        $container = new DI\Container();
        $createCommentUsecase = $container->get(CreateCommentUsecase::class);
        $findManyCommentUsecase = $container->get(FindManyCommentUsecase::class);

        $commentPayload = new CreateCommentDTO(
            body: 'This is my first comment!',
            owner: self::$user->getId(),
            task: self::$task->getId(),
        );

        $createCommentUsecase->execute($commentPayload);

        $comments = $findManyCommentUsecase->execute(1);
        $lastComment = end($comments);

        $this->assertSame($commentPayload->body, $lastComment->getBody());
        $this->assertSame($commentPayload->owner, $lastComment->getOwner());
        $this->assertSame($commentPayload->task, $lastComment->getTask());
    }
}
