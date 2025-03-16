<?php declare(strict_types=1);

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

$dotenv = Dotenv::createImmutable(dirname(__FILE__, 4));
$dotenv->load();

final class TaskControllerTest extends TestCase
{
    private static Client $client;
    private static string $authToken;
    private static int $owner;
    private static int $cardId;

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
        self::$owner = $owner->id;

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

        self::$cardId = $lastCard->id;
    }

    public static function tearDownAfterClass(): void
    {
        try {
            $pdo = new PDO('sqlite:development.db');
            $pdo->prepare('DELETE FROM users')->execute();
            $pdo->prepare('DELETE FROM boards')->execute();
            $pdo->prepare('DELETE FROM cards')->execute();
            $pdo->prepare('DELETE FROM tasks')->execute();
            $pdo = null;
        } catch (PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    public function testShouldCreateOneTask(): void
    {
        // Create one task
        $taskPayload = [
            'title' => 'first task!',
            'body' => 'This is my first task',
            'hex_bgcolor' => '#000000',
            'owner' => self::$owner,
            'attributed_to' => self::$owner,
            'card' => self::$cardId,
        ];

        $createdTask = self::$client->request('POST', '/api/tasks/create', [
            'json' => $taskPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $this->assertSame(201, $createdTask->getStatusCode());

        // Find the task
        $tasks = self::$client->request('GET', '/api/tasks?limit=2', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $tasksContents = json_decode($tasks->getBody()->getContents());
        $lastTask = end($tasksContents);

        $this->assertSame($taskPayload['title'], $lastTask->title);
    }

    public function testThrowsAnUnauthorizedException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unauthorized user');
        $this->expectExceptionCode(401);

        self::$client->request('GET', '/api/tasks?limit=2');
    }

    public function testShouldUpdateOneTask(): void
    {
        // Find a task
        $tasks = self::$client->request('GET', '/api/tasks?limit=2', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $tasksContents = json_decode($tasks->getBody()->getContents());
        $lastTask = end($tasksContents);

        // Update the task
        $updateTaskPayload = [
            'title' => 'Updated task',
            'body' => 'A New task',
            'hex_bgcolor' => '#ffffff',
            'attributed_to' => self::$owner
        ];

        $updatedTask = self::$client->request('PUT', "/api/tasks/{$lastTask->id}", [
            'json' => $updateTaskPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $this->assertSame(201, $updatedTask->getStatusCode());
    }

    public function testShouldDeleteOneTask(): void
    {
        // Find a task
        $tasks = self::$client->request('GET', '/api/tasks?limit=2', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $tasksContents = json_decode($tasks->getBody()->getContents());
        $lastTask = end($tasksContents);

        // Delete the task
        $deletedTask = self::$client->request('DELETE', "/api/tasks/{$lastTask->id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $this->assertSame(201, $deletedTask->getStatusCode());
        $this->assertSame(
            'Task was deleted successfully',
            json_decode(
                $deletedTask->getBody()->getContents()
            )->message
        );
    }
}
