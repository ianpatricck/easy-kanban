<?php declare(strict_types=1);

// |============================================|
// | Controlador do usuário                     |
// |============================================|

namespace App\Controllers;

use App\DTO\AuthenticateUserDTO;
use App\DTO\CreateUserDTO;
use App\DTO\UserResponseDTO;
use App\Usecases\User\AuthenticateUserUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\DeleteUserUsecase;
use App\Usecases\User\FindUserUsecase;
use App\Usecases\User\UpdateEmailUsecase;
use App\Usecases\User\UpdateNameUsecase;
use App\Usecases\User\UpdatePasswordUsecase;
use App\Usecases\User\UpdateUserBioUsecase;
use App\Usecases\User\UpdateUsernameUsecase;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class UserController
{
    public function __construct(
        private CreateUserUsecase $createUserUsecase,
        private FindUserUsecase $findUserUsecase,
        private AuthenticateUserUsecase $authenticateUserUsecase,
        private UpdateEmailUsecase $updateEmailUsecase,
        private UpdateNameUsecase $updateNameUsecase,
        private UpdateUsernameUsecase $updateUsernameUsecase,
        private UpdateUserBioUsecase $updateUserBioUsecase,
        private UpdatePasswordUsecase $updatePasswordUsecase,
        private DeleteUserUsecase $deleteUserUsecase
    ) {}

    /*
     * Método para resgatar um usuário
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * return Response
     */

    #[OA\Get(
        path: '/api/users/{by}',
        tags: ['User'],
        summary: 'Find one user',
        description: 'Get user by id or name',
        operationId: 'findUser',
        parameters: [
            new OA\PathParameter(
                name: 'by',
                description: 'username or user id',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: [
                new OA\JsonContent(
                    ref: UserResponseDTO::class
                )
            ]),
            new OA\Response(response: 404, description: 'User not found'),
        ],
    )]
    public function findOne(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->findUserUsecase->execute($args['by']);

            $userResponseDto = new UserResponseDTO(
                id: $user->getId(),
                name: $user->getName(),
                username: $user->getUsername(),
                email: $user->getEmail(),
                bio: $user->getBio(),
                avatar: $user->getAvatar(),
                created_at: $user->getCreatedAt(),
                updated_at: $user->getUpdatedAt()
            );

            $response->getBody()->write(json_encode($userResponseDto));

            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para criação de usuário
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Post(
        path: '/api/users/create',
        tags: ['User'],
        summary: 'Create a new user',
        description: 'This basically create a new user in the application',
        operationId: 'createrUser',
        responses: [
            new OA\Response(response: 201, description: 'User created successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Create an user',
            required: true,
            content: new OA\JsonContent(
                ref: CreateUserDTO::class
            )
        )
    )]
    public function create(Request $request, Response $response): Response
    {
        try {
            $body = $request->getParsedBody();
            $createUserDTO = new CreateUserDTO(...$body);

            $this->createUserUsecase->execute($createUserDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'User created successfully'
                ])
            );

            return $response->withStatus(201);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para validação de dados e geração do token de
     * autenticação
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */
    #[OA\Post(
        path: '/api/users/login',
        tags: ['User'],
        summary: 'Authenticate user',
        description: 'This authenticate a user in the application',
        operationId: 'loginUser',
        responses: [
            new OA\Response(response: 201, description: 'User authenticated successfully'),
            new OA\Response(response: 404, description: "This account doesn't exist"),
            new OA\Response(response: 401, description: 'Incorrect password'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Authenticate an user',
            required: true,
            content: new OA\JsonContent(
                ref: AuthenticateUserDTO::class
            )
        )
    )]
    public function login(Request $request, Response $response): Response
    {
        try {
            $body = $request->getParsedBody();
            $authenticateUserDTO = new AuthenticateUserDTO(...$body);
            $token = $this->authenticateUserUsecase->execute($authenticateUserDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'User authenticated successfully',
                    'token' => $token
                ])
            );

            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para atualização do email
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Patch(
        path: '/api/users/email/{by}',
        tags: ['User'],
        summary: 'Update the email',
        description: 'This update an user email in the application',
        operationId: 'updateEmail',
        parameters: [
            new OA\PathParameter(
                name: 'by',
                description: 'username or user id',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Email was updated successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'User not found'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update an email',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'email',
                            description: 'Updated email of the user',
                            example: 'updated_john@example.com',
                            type: 'string'
                        ),
                    ]
                )
            )
        )
    )]
    public function updateEmail(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody();
            $this->updateEmailUsecase->execute($args['by'], $body['email']);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Email was updated successfully'
                ])
            );

            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para atualização do nome do usuário
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Patch(
        path: '/api/users/name/{by}',
        tags: ['User'],
        summary: 'Update the name',
        description: 'This update the name in the application',
        operationId: 'updateName',
        parameters: [
            new OA\PathParameter(
                name: 'by',
                description: 'username or user id',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Name was updated successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'User not found'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update an name',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'name',
                            description: 'Updated name of the user',
                            example: 'John Kennedy Smith',
                            type: 'string'
                        ),
                    ]
                )
            )
        )
    )]
    public function updateName(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody();
            $this->updateNameUsecase->execute($args['by'], $body['name']);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Name was updated successfully'
                ])
            );

            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para atualização do apelido do usuário
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Patch(
        path: '/api/users/username/{by}',
        tags: ['User'],
        summary: 'Update the username',
        description: 'This update the username in the application',
        operationId: 'updateUsername',
        parameters: [
            new OA\PathParameter(
                name: 'by',
                description: 'username or user id',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Username was updated successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'User not found'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update an username',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'username',
                            description: 'Updated username of the user',
                            example: 'Johnsmith',
                            type: 'string'
                        ),
                    ]
                )
            )
        )
    )]
    public function updateUsername(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody();
            $this->updateUsernameUsecase->execute($args['by'], $body['username']);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Username was updated successfully'
                ])
            );

            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para atualização da descrição do usuário
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Patch(
        path: '/api/users/description/{by}',
        tags: ['User'],
        summary: 'Update the description',
        description: 'This update the user description in the application',
        operationId: 'updateBio',
        parameters: [
            new OA\PathParameter(
                name: 'by',
                description: 'username or user id',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'User description was updated successfully'),
            new OA\Response(response: 404, description: 'User not found'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update an user description',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'bio',
                            description: 'Updated the description of the user',
                            example: 'Hello, I updated the description',
                            type: 'string'
                        ),
                    ]
                )
            )
        )
    )]
    public function updateBio(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody();
            $this->updateUserBioUsecase->execute($args['by'], $body['bio']);

            $response->getBody()->write(
                json_encode([
                    'message' => 'User description was updated successfully'
                ])
            );

            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para atualização da senha
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Patch(
        path: '/api/users/password/{by}',
        tags: ['User'],
        summary: 'Update the password',
        description: 'This update the user password in the application',
        operationId: 'updatePassword',
        parameters: [
            new OA\PathParameter(
                name: 'by',
                description: 'username or user id',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'User password was updated successfully'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 400, description: 'Something was wrong'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update an user password',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'old_password',
                            description: "User old's password",
                            example: 'johnpass2123',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'new_password',
                            description: "User news's password",
                            example: 'john_newpassword0321',
                            type: 'string'
                        ),
                    ]
                )
            )
        )
    )]
    public function updatePassword(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody();
            $this->updatePasswordUsecase->execute($args['by'], $body['old_password'], $body['new_password']);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Password was updated successfully'
                ])
            );

            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }

    /*
     * Método para deletar um usuário
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Delete(
        path: '/api/users/{by}',
        tags: ['User'],
        summary: 'Delete the user',
        description: 'This delete the user',
        operationId: 'deleteUser',
        parameters: [
            new OA\PathParameter(
                name: 'by',
                description: 'username or user id',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'User was deleted successfully'),
            new OA\Response(response: 404, description: 'User not found'),
        ],
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deleteUserUsecase->execute($args['by']);

            $response->getBody()->write(
                json_encode([
                    'message' => 'User was deleted successfully'
                ])
            );

            return $response->withStatus(200);
        } catch (Exception $exception) {
            $response->getBody()->write(
                json_encode([
                    'message' => $exception->getMessage()
                ])
            );

            return $response->withStatus($exception->getCode());
        }
    }
}
