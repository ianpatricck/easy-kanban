<?php declare(strict_types=1);

// |============================================|
// | Controlador das tarefas                    |
// |============================================|

namespace App\Controllers;

use App\DTO\CreateTaskDTO;
use App\Usecases\Task\CreateTaskUsecase;
use App\Usecases\Task\FindManyTaskUsecase;
use App\Usecases\Task\FindTaskUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

use function App\utils\isAuthorizedUser;

class TaskController
{
    public function __construct(
        private AuthorizeUserUsecase $authorizeUserUsecase,
        private CreateTaskUsecase $createTaskUsecase,
        private FindTaskUsecase $findTaskUsecase,
        private FindManyTaskUsecase $findManyTaskUsecase,
    ) {}

    /*
     * Método para encontrar uma tarefa
     *
     * @param Request $request
     * @param Response $response
     * @param array  $args
     *
     * return Response
     */
    public function findOne(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $task = $this->findTaskUsecase->execute($id);

            $taskResponse = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'body' => $task->getBody(),
                'hex_bgcolor' => $task->getOwner(),
                'attributed_to' => $task->getCard(),
                'created_at' => $task->getCreatedAt(),
                'updated_at' => $task->getUpdatedAt(),
            ];

            $response->getBody()->write(json_encode($taskResponse));
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
     * Método para encontrar muitas tarefas
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */
    public function findMany(Request $request, Response $response): Response
    {
        try {
            $limit = (int) $request->getQueryParams()['limit'];
            $tasks = $this->findManyTaskUsecase->execute($limit);
            $tasksResponse = [];

            foreach ($tasks as $task) {
                $tasksResponse[] = [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'body' => $task->getBody(),
                    'hex_bgcolor' => $task->getOwner(),
                    'attributed_to' => $task->getCard(),
                    'created_at' => $task->getCreatedAt(),
                    'updated_at' => $task->getUpdatedAt(),
                ];
            }

            $response->getBody()->write(json_encode($tasksResponse));
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
     * Método para criação de uma tarefa
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));
            $body = $request->getParsedBody();

            $ownerId = (int) $body['owner'];
            $authId = (int) $authorizedUser->id;

            if ($ownerId != $authId) {
                throw new Exception('User unauthorized', 401);
            }

            $createTaskDTO = new CreateTaskDTO(...$body);
            $this->createTaskUsecase->execute($createTaskDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Task was created successfully'
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
}
