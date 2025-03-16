<?php declare(strict_types=1);

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

$dotenv = Dotenv::createImmutable(dirname(__FILE__, 4));
$dotenv->load();

final class BoardControllerTest extends TestCase
{
    private static Client $client;
    private static string $authToken;

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
    }

    public static function tearDownAfterClass(): void
    {
        try {
            $pdo = new PDO('sqlite:development.db');
            $pdo->prepare('DELETE FROM users')->execute();
            $pdo->prepare('DELETE FROM boards')->execute();
            $pdo = null;
        } catch (PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    public function testShouldCreateOneBoard(): void
    {
        // Get user data
        $user = static::$client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $owner = json_decode($user->getBody()->getContents());

        // Create a board
        $boardPayload = [
            'name' => 'My first board',
            'owner' => $owner->id,
            'description' => 'Welcome, this is my first board',
        ];

        $created = static::$client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $this->assertSame(
            json_encode(['message' => 'Board was created successfully']),
            $created->getBody()->getContents()
        );

        $this->assertSame(201, $created->getStatusCode());
    }

    public function testShouldCreateAnotherBoard(): void
    {
        // Get user data
        $user = static::$client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $owner = json_decode($user->getBody()->getContents());

        // Create a board
        $boardPayload = [
            'name' => 'My second board',
            'owner' => $owner->id,
            'description' => 'Welcome, this is my second board',
        ];

        $created = static::$client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $this->assertSame(
            json_encode(['message' => 'Board was created successfully']),
            $created->getBody()->getContents()
        );

        $this->assertSame(201, $created->getStatusCode());
    }

    public function testThrowsNameWasNotProvidedException(): void
    {
        // Get user data
        $response = static::$client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $owner = json_decode($response->getBody()->getContents());

        // Create a board
        $boardPayload = [
            'name' => '',
            'owner' => $owner->id,
            'description' => 'Welcome, this is my second board',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The board's name was not provided");
        $this->expectExceptionCode(400);

        static::$client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);
    }

    public function testThrowsOwnerUnauthorizedException(): void
    {
        // Get user data
        $user = static::$client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $owner = json_decode($user->getBody()->getContents());

        // Create a board
        $boardPayload = [
            'name' => 'My second board',
            'owner' => $owner->id + 5,
            'description' => 'Welcome, this is my second board',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User unauthorized');
        $this->expectExceptionCode(400);

        static::$client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);
    }

    public function testShouldUpdateOneBoard(): void
    {
        // Get the last board
        $responseBoards = static::$client->request('GET', '/api/boards?limit=2', [
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $boards = json_decode($responseBoards->getBody()->getContents());
        $lastBoard = end($boards);

        // Get the owner
        $user = static::$client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $owner = json_decode($user->getBody()->getContents());

        // Update the board
        $updateBoardPayload = [
            'name' => 'My updated board',
            'owner' => $owner->id,
            'description' => 'This is a updated board'
        ];

        $updated = static::$client->request('PUT', "/api/boards/{$lastBoard->id}", [
            'json' => $updateBoardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $this->assertSame(
            json_encode(['message' => 'Board was updated successfully']),
            $updated->getBody()->getContents()
        );

        $this->assertSame(201, $updated->getStatusCode());
    }

    public function testShouldDeleteOneBoard(): void
    {
        // Get the last board
        $responseBoards = static::$client->request('GET', '/api/boards?limit=1', [
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $boards = json_decode($responseBoards->getBody()->getContents());
        $lastBoard = end($boards);

        $deleted = static::$client->request('DELETE', "/api/boards/{$lastBoard->id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . static::$authToken
            ]
        ]);

        $this->assertSame(
            json_encode(['message' => 'Board was deleted successfully']),
            $deleted->getBody()->getContents()
        );

        $this->assertSame(201, $deleted->getStatusCode());
    }
}
