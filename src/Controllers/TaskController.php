<?php declare(strict_types=1);

// |============================================|
// | Controlador das tarefas                    |
// |============================================|

namespace App\Controllers;

use App\DTO\CreateTaskDTO;
use App\DTO\TaskResponseDTO;
use App\DTO\UpdateTaskDTO;
use App\Usecases\Task\CreateTaskUsecase;
use App\Usecases\Task\DeleteTaskUsecase;
use App\Usecases\Task\FindManyTaskUsecase;
use App\Usecases\Task\FindTaskUsecase;
use App\Usecases\Task\UpdateTaskUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use OpenApi\Attributes as OA;
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
        private UpdateTaskUsecase $updateTaskUsecase,
        private DeleteTaskUsecase $deleteTaskUsecase,
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

    #[OA\Get(
        path: '/api/tasks/{id}',
        tags: ['Task'],
        summary: 'Find one task',
        description: 'Get tasks by id',
        operationId: 'findTask',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'task id',
                required: true,
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: [
                new OA\JsonContent(
                    ref: TaskResponseDTO::class
                )
            ]),
            new OA\Response(response: 404, description: 'Task not found'),
        ],
    )]
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

    #[OA\Get(
        path: '/api/tasks',
        tags: ['Task'],
        summary: 'Find tasks',
        description: 'Get many tasks',
        operationId: 'findManyTasks',
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'limit value that needed to be considered for filter tasks',
                required: false,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: [
                    new OA\JsonContent(
                        type: 'array',
                        items: new OA\Items(
                            ref: TaskResponseDTO::class
                        )
                    )
                ]
            )
        ],
    )]
    public function findMany(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $tasks = $this->findManyTaskUsecase->execute($params);
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

    #[OA\Post(
        path: '/api/tasks/create',
        tags: ['Task'],
        summary: 'Create a new task',
        description: 'This create a new task in the application',
        operationId: 'createTask',
        responses: [
            new OA\Response(response: 201, description: 'Task was created successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Create a task',
            required: true,
            content: new OA\JsonContent(
                ref: CreateTaskDTO::class
            )
        )
    )]
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

    /*
     * Método para atualizar uma tarefa
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * return Response
     */

    #[OA\Put(
        path: '/api/tasks/{id}',
        tags: ['Task'],
        summary: 'Update a task',
        description: 'This update a task in the application',
        operationId: 'updateTask',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'task id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Task was updated successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'Task not found'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update a task',
            required: true,
            content: new OA\JsonContent(
                ref: UpdateTaskDTO::class
            )
        )
    )]
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));

            $taskId = (int) $args['id'];
            $body = $request->getParsedBody();

            $task = $this->findTaskUsecase->execute($taskId);

            if ($authorizedUser->id !== $task->getOwner()) {
                throw new Exception('User unauthorized', 401);
            }

            $updateTaskDTO = new UpdateTaskDTO(...$body);
            $this->updateTaskUsecase->execute($taskId, $updateTaskDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Task was updated successfully'
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
     * Método para deletar uma tarefa
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * return Response
     */

    #[OA\Delete(
        path: '/api/tasks/{id}',
        tags: ['Task'],
        summary: 'Delete a task',
        description: 'This delete a task in the application',
        operationId: 'deleteTask',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'task id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Task was deleted successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'Task not found'),
        ],
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));
            $taskId = (int) $args['id'];

            $taskEntity = $this->findTaskUsecase->execute($taskId);

            if ($authorizedUser->id !== $taskEntity->getOwner()) {
                throw new Exception('User unauthorized', 401);
            }

            $this->deleteTaskUsecase->execute($taskId);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Task was deleted successfully'
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
