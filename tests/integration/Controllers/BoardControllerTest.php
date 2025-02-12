<?php declare(strict_types=1);

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

$dotenv = Dotenv::createImmutable(dirname(__FILE__, 4));
$dotenv->load();

final class BoardControllerTest extends TestCase
{
    private Client $client;
    private string $authToken;

    public function setUp(): void
    {
        error_reporting(E_ALL);

        $this->client = new Client(
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

        $this->client->request('POST', '/api/users/create', ['json' => $body]);;

        // Authenticate user

        $authPayload = [
            'email' => 'guest@mail.com',
            'password' => 'mypassword123'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $authPayload]);
        $authenticated = json_decode($response->getBody()->getContents());

        $this->authToken = $authenticated->token;
    }

    public function tearDown(): void
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
        $response = $this->client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
            ]
        ]);

        $owner = json_decode($response->getBody()->getContents());

        $boardPayload = [
            'name' => 'My first board',
            'owner' => $owner->id,
            'description' => 'Welcome, this is my first board',
        ];

        // Create a board
        $created = $this->client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
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
        $response = $this->client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
            ]
        ]);

        $owner = json_decode($response->getBody()->getContents());

        $boardPayload = [
            'name' => 'My second board',
            'owner' => $owner->id,
            'description' => 'Welcome, this is my second board',
        ];

        // Create a board
        $created = $this->client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
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
        $response = $this->client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
            ]
        ]);

        $owner = json_decode($response->getBody()->getContents());

        $boardPayload = [
            'name' => '',
            'owner' => $owner->id,
            'description' => 'Welcome, this is my second board',
        ];

        // Create a board

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The board's name was not provided");
        $this->expectExceptionCode(400);

        $this->client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
            ]
        ]);
    }

    public function testThrowsOwnerUnauthorizedException(): void
    {
        // Get user data
        $response = $this->client->request('GET', '/api/users/guest', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
            ]
        ]);

        $owner = json_decode($response->getBody()->getContents());

        $boardPayload = [
            'name' => 'My second board',
            'owner' => $owner->id + 5,
            'description' => 'Welcome, this is my second board',
        ];

        // Create a board

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User unauthorized');
        $this->expectExceptionCode(400);

        $this->client->request('POST', '/api/boards/create', [
            'json' => $boardPayload,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
            ]
        ]);
    }
}
