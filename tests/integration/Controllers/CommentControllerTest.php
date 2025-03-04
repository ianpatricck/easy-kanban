<?php declare(strict_types=1);

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

$dotenv = Dotenv::createImmutable(dirname(__FILE__, 4));
$dotenv->load();

final class CommentControllerTest extends TestCase
{
    private static Client $client;
    private static string $authToken;
    private static int $ownerId;
    private static int $taskId;

    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public static function setUpBeforeClass(): void
    {
        error_reporting(E_ALL);

        self::$client = new Client(
            [
                'base_uri' => $_ENV['APP_URL'],
                'timeout' => 2.0,
            ]
        );

        // Create user
        $body = [
            'username' => 'guest',
            'name' => 'Guestname',
            'email' => 'guest@mail.com',
            'password' => 'mypassword123'
        ];

        self::$client->request('POST', '/api/users/create', ['json' => $body]);;

        // Authenticate user
        $authPayload = [
            'email' => 'guest@mail.com',
            'password' => 'mypassword123'
        ];

        $response = self::$client->request('POST', '/api/users/login', ['json' => $authPayload]);
        $authenticated = json_decode($response->getBody()->getContents());

        self::$authToken = $authenticated->token;

        // Get user data
        $user = self::$client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $owner = json_decode($user->getBody()->getContents());
        self::$ownerId = $owner->id;

        // Create a board
        $boardPayload = [
            'name' => 'My first board',
            'owner' => $owner->id,
            'description' => 'Welcome, this is my first board',
        ];

        self::$client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        // GET the boards
        $boards = self::$client->request('GET', '/api/boards?limit=1', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $boardsContents = json_decode($boards->getBody()->getContents());
        $lastBoard = end($boardsContents);

        // Create one card
        $cardPayload = [
            'name' => 'In development',
            'hex_bgcolor' => '#ffffff',
            'board' => $lastBoard->id,
        ];

        self::$client->request('POST', '/api/cards/create', [
            'json' => $cardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $card = self::$client->request('GET', '/api/cards?limit=2', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $cardContents = json_decode($card->getBody()->getContents());
        $lastCard = end($cardContents);

        // Create one task
        $taskPayload = [
            'title' => 'first task!',
            'body' => 'This is my first task',
            'hex_bgcolor' => '#000000',
            'owner' => self::$ownerId,
            'attributed_to' => self::$ownerId,
            'card' => $lastCard->id,
        ];

        self::$client->request('POST', '/api/tasks/create', [
            'json' => $taskPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        // Find the task
        $tasks = self::$client->request('GET', '/api/tasks?limit=2', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $tasksContents = json_decode($tasks->getBody()->getContents());
        $lastTask = end($tasksContents);

        self::$taskId = $lastTask->id;
    }

    public static function tearDownAfterClass(): void
    {
        try {
            $pdo = new PDO('sqlite:development.db');
            $pdo->prepare('DELETE FROM users')->execute();
            $pdo->prepare('DELETE FROM boards')->execute();
            $pdo->prepare('DELETE FROM cards')->execute();
            $pdo->prepare('DELETE FROM tasks')->execute();
            $pdo->prepare('DELETE FROM comments')->execute();
            $pdo = null;
        } catch (PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    public function testThrowsAnUnauthorizedUserException(): void
    {
        $commentPayload = [
            'body' => 'This is a random comment',
            'owner' => self::$ownerId + 10,
            'task' => self::$taskId,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User unauthorized');
        $this->expectExceptionCode(401);

        self::$client->request('POST', '/api/comments/create', [
            'json' => $commentPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);
    }

    public function testShouldCreateOneComment(): void
    {
        // Create a comment
        $commentPayload = [
            'body' => 'This is a random comment',
            'owner' => self::$ownerId,
            'task' => self::$taskId,
        ];

        $createdComment = self::$client->request('POST', '/api/comments/create', [
            'json' => $commentPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $createdCommentContents = json_decode($createdComment->getBody()->getContents());

        $this->assertSame('Comment was created successfully', $createdCommentContents->message);
        $this->assertSame(201, $createdComment->getStatusCode());
    }
}
