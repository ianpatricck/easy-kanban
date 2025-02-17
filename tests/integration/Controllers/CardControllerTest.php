<?php declare(strict_types=1);

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

$dotenv = Dotenv::createImmutable(dirname(__FILE__, 4));
$dotenv->load();

final class CardControllerTest extends TestCase
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
    }

    public static function tearDownAfterClass(): void
    {
        try {
            $pdo = new PDO('sqlite:development.db');
            $pdo->prepare('DELETE FROM users')->execute();
            $pdo->prepare('DELETE FROM boards')->execute();
            $pdo->prepare('DELETE FROM cards')->execute();
            $pdo = null;
        } catch (PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    public function testThrowsAnUnauthenticatedUserException(): void
    {
        // Get board data
        $boardsResponse = self::$client->request('GET', '/api/boards?limit=1', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $boards = json_decode($boardsResponse->getBody()->getContents());
        $lastBoard = end($boards);

        // Create one card
        $cardPayload = [
            'name' => 'In development',
            'hex_bgcolor' => '#ffffff',
            'board' => $lastBoard->id,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid authenticated user');
        $this->expectExceptionCode(401);

        self::$client->request('POST', '/api/cards/create', [
            'json' => $cardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken . 'not_invalid'
            ]
        ]);
    }

    public function testShouldCreateOneBoard(): void
    {
        // Get board data
        $boardsResponse = self::$client->request('GET', '/api/boards?limit=1', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $boards = json_decode($boardsResponse->getBody()->getContents());
        $lastBoard = end($boards);

        // Create one card
        $cardPayload = [
            'name' => 'In development',
            'hex_bgcolor' => '#ffffff',
            'board' => $lastBoard->id,
        ];

        $created = self::$client->request('POST', '/api/cards/create', [
            'json' => $cardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$authToken
            ]
        ]);

        $this->assertSame(
            json_encode(['message' => 'Card was created successfully']),
            $created->getBody()->getContents()
        );

        $this->assertSame(201, $created->getStatusCode());
    }
}
