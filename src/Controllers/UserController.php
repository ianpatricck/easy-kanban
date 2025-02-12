<?php declare(strict_types=1);

// |============================================|
// | Controlador do usuário                     |
// |============================================|

namespace App\Controllers;

use App\DTO\AuthenticateUserDTO;
use App\DTO\CreateUserDTO;
use App\Usecases\User\AuthenticateUserUsecase;
use App\Usecases\User\CreateUserUsecase;
use App\Usecases\User\DeleteUserUsecase;
use App\Usecases\User\FindUserUsecase;
use App\Usecases\User\UpdateEmailUsecase;
use App\Usecases\User\UpdateNameUsecase;
use App\Usecases\User\UpdatePasswordUsecase;
use App\Usecases\User\UpdateUserBioUsecase;
use App\Usecases\User\UpdateUsernameUsecase;
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

    public function findOne(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->findUserUsecase->execute($args['by']);

            $response->getBody()->write(json_encode([
                'id' => $user->getId(),
                'name' => $user->getName(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'bio' => $user->getBio(),
                'avatar' => $user->getAvatar(),
                'created_at' => $user->getCreatedAt(),
                'updated_at' => $user->getUpdatedAt()
            ]));

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
