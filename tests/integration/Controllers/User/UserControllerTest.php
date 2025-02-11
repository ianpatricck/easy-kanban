<?php declare(strict_types=1);

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

$dotenv = Dotenv::createImmutable(dirname(__FILE__, 5));
$dotenv->load();

final class UserControllerTest extends TestCase
{
    private Client $client;

    public function setUp(): void
    {
        error_reporting(E_ALL);
        $this->client = new Client(
            [
                'base_uri' => $_ENV['APP_URL'],
                'timeout' => 2.0,
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        try {
            $pdo = new PDO('sqlite:development.db');
            $pdo->prepare('DELETE FROM users')->execute();
            $pdo = null;
        } catch (PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    public function testThrowsAnInvalidEmailException(): void
    {
        $body = [
            'username' => 'guest',
            'name' => 'Guestname',
            'email' => 'guestmail.com',
            'password' => 'mystrongpass#12!'
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Email format is not valid');
        $this->expectExceptionCode(400);

        $this->client->request('POST', '/api/users/create', ['json' => $body]);
    }

    public function testThrowsAnInvalidPasswordFormatException(): void
    {
        $body = [
            'username' => 'guest',
            'name' => 'Guestname',
            'email' => 'guest@mail.com',
            'password' => 'mypass'
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password must be greater than 8 characters');
        $this->expectExceptionCode(400);

        $this->client->request('POST', '/api/users/create', ['json' => $body]);
    }

    public function testShouldCreateAnUser(): void
    {
        $body = [
            'username' => 'guest',
            'name' => 'Guestname',
            'email' => 'guest@mail.com',
            'password' => 'mypassword321'
        ];

        $created = $this->client->request('POST', '/api/users/create', ['json' => $body]);

        $this->assertSame(
            json_encode(['message' => 'User created successfully']),
            $created->getBody()->getContents()
        );

        $this->assertSame(201, $created->getStatusCode());
    }

    public function testThrowsAnExistingUserException(): void
    {
        $body = [
            'username' => 'guest',
            'name' => 'Guestname',
            'email' => 'guest@mail.com',
            'password' => 'mypassword321'
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The username is already in use');
        $this->expectExceptionCode(400);

        $this->client->request('POST', '/api/users/create', ['json' => $body]);
    }

    public function testThrowsANotFoundAccountException(): void
    {
        $body = [
            'email' => 'invalid_email',
            'password' => 'invalid_pass'
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("This account doesn't exist");
        $this->expectExceptionCode(404);

        $this->client->request('POST', '/api/users/login', ['json' => $body]);
    }

    public function testThrowsAnInvalidPasswordException(): void
    {
        $body = [
            'email' => 'guest@mail.com',
            'password' => 'invalid_pass'
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Incorrect password');
        $this->expectExceptionCode(401);

        $this->client->request('POST', '/api/users/login', ['json' => $body]);
    }

    public function testShouldAuthenticateTheUser(): void
    {
        $body = [
            'email' => 'guest@mail.com',
            'password' => 'mypassword321'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);

        $authenticated = json_decode($response->getBody()->getContents());

        $this->assertSame('User authenticated successfully', $authenticated->message);
        $this->assertIsString($authenticated->token);
    }

    public function testThrowsAnUnauthorizedUser(): void
    {
        $username = 'guest';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unauthorized user');
        $this->expectExceptionCode(401);

        $this->client->request(
            'GET',
            "/api/users/{$username}",
        );
    }

    public function testThrowsAWrongTokenException(): void
    {
        // Authenticate user

        $body = [
            'email' => 'guest@mail.com',
            'password' => 'mypassword321'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);
        $authenticated = json_decode($response->getBody()->getContents());

        // Verify authorization

        $username = 'guest';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid authenticated user');
        $this->expectExceptionCode(401);

        $this->client->request('GET', "/api/users/{$username}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $authenticated->token . 'wrong token lol :)'
            ]
        ]);
    }

    public function testShouldAuthorizateTokenHandleRequest(): void
    {
        // Authenticate user

        $body = [
            'email' => 'guest@mail.com',
            'password' => 'mypassword321'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);
        $authenticated = json_decode($response->getBody()->getContents());

        // Verify authorization

        $username = 'guest';

        $user = $this->client->request('GET', "/api/users/{$username}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $authenticated->token
            ]
        ]);

        $this->assertNotEmpty($user->getBody()->getContents());
    }

    public function testShouldUpdateEmail(): void
    {
        // Authenticate user
        $body = [
            'email' => 'guest@mail.com',
            'password' => 'mypassword321'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);
        $authenticated = json_decode($response->getBody()->getContents());

        // Update email
        $username = 'guest';
        $updateData = ['email' => 'newemail@mail.com'];

        $updated = $this->client->request('PATCH', "/api/users/email/{$username}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $authenticated->token
            ],
            'json' => $updateData
        ]);

        $this->assertSame(
            json_encode([
                'message' => 'Email was updated successfully'
            ]), $updated->getBody()->getContents()
        );
    }

    public function testShouldUpdateName(): void
    {
        // Authenticate user
        $body = [
            'email' => 'newemail@mail.com',
            'password' => 'mypassword321'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);
        $authenticated = json_decode($response->getBody()->getContents());

        // Update name
        $username = 'guest';
        $updateData = ['name' => 'new name'];

        $updated = $this->client->request('PATCH', "/api/users/name/{$username}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $authenticated->token
            ],
            'json' => $updateData
        ]);

        $this->assertSame(
            json_encode([
                'message' => 'Name was updated successfully'
            ]), $updated->getBody()->getContents()
        );
    }

    public function testShouldUpdateUsername(): void
    {
        // Authenticate user
        $body = [
            'email' => 'newemail@mail.com',
            'password' => 'mypassword321'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);
        $authenticated = json_decode($response->getBody()->getContents());

        // Update username
        $username = 'guest';
        $updateData = ['username' => '_newusername'];

        $updated = $this->client->request('PATCH', "/api/users/username/{$username}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $authenticated->token
            ],
            'json' => $updateData
        ]);

        $this->assertSame(
            json_encode([
                'message' => 'Username was updated successfully'
            ]), $updated->getBody()->getContents()
        );
    }

    public function testShouldUpdateBio(): void
    {
        // Authenticate user
        $body = [
            'email' => 'newemail@mail.com',
            'password' => 'mypassword321'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);
        $authenticated = json_decode($response->getBody()->getContents());

        // Update description
        $username = '_newusername';
        $updateData = ['bio' => 'Another description'];

        $updated = $this->client->request('PATCH', "/api/users/description/{$username}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $authenticated->token
            ],
            'json' => $updateData
        ]);

        $this->assertSame(
            json_encode([
                'message' => 'User description was updated successfully'
            ]), $updated->getBody()->getContents()
        );
    }

    public function testShouldUpdatePassword(): void
    {
        // Authenticate user
        $body = [
            'email' => 'newemail@mail.com',
            'password' => 'mypassword321'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);
        $authenticated = json_decode($response->getBody()->getContents());

        // Update password
        $username = '_newusername';
        $updateData = [
            'old_password' => 'mypassword321',
            'new_password' => 'mynewpassword123'
        ];

        $updated = $this->client->request('PATCH', "/api/users/password/{$username}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $authenticated->token
            ],
            'json' => $updateData
        ]);

        $this->assertSame(
            json_encode([
                'message' => 'Password was updated successfully'
            ]), $updated->getBody()->getContents()
        );
    }

    public function testShouldDeleteAnUser(): void
    {
        // Authenticate user
        $body = [
            'email' => 'newemail@mail.com',
            'password' => 'mynewpassword123'
        ];

        $response = $this->client->request('POST', '/api/users/login', ['json' => $body]);
        $authenticated = json_decode($response->getBody()->getContents());

        $username = '_newusername';
        $updated = $this->client->request('DELETE', "/api/users/{$username}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $authenticated->token
            ],
        ]);

        $this->assertSame(
            json_encode([
                'message' => 'User was deleted successfully'
            ]), $updated->getBody()->getContents()
        );
    }
}
