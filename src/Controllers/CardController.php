<?php declare(strict_types=1);

// |============================================|
// | Controlador dos cartões                    |
// |============================================|

namespace App\Controllers;

use App\DTO\CardResponseDTO;
use App\DTO\CreateCardDTO;
use App\DTO\UpdateCardDTO;
use App\Usecases\Board\FindBoardUsecase;
use App\Usecases\Card\CreateCardUsecase;
use App\Usecases\Card\DeleteCardUsecase;
use App\Usecases\Card\FindCardUsecase;
use App\Usecases\Card\FindManyCardUsecase;
use App\Usecases\Card\UpdateCardUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

use function App\utils\isAuthorizedUser;

class CardController
{
    public function __construct(
        private AuthorizeUserUsecase $authorizeUserUsecase,
        private CreateCardUsecase $createCardUsecase,
        private UpdateCardUsecase $updateCardUsecase,
        private FindBoardUsecase $findBoardUsecase,
        private FindManyCardUsecase $findManyCardUsecase,
        private FindCardUsecase $findCardUsecase,
        private DeleteCardUsecase $deleteCardUsecase,
    ) {}

    /*
     * Método para encontrar um cartão
     *
     * @param Request $request
     * @param Response $response
     * @param array  $args
     *
     * return Response
     */

    #[OA\Get(
        path: '/api/cards/{id}',
        tags: ['Card'],
        summary: 'Find one card',
        description: 'Get card by id',
        operationId: 'findCard',
        parameters: [
            new OA\PathParameter(
                name: 'by',
                description: 'card id',
                required: true,
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: [
                new OA\JsonContent(
                    ref: CardResponseDTO::class
                )
            ]),
            new OA\Response(response: 404, description: 'Card not found'),
        ],
    )]
    public function findOne(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $card = $this->findCardUsecase->execute($id);

            $cardResponse = new CardResponseDTO(
                id: $card->getId(),
                name: $card->getName(),
                hex_bgcolor: $card->getHexBgColor(),
                owner: $card->getOwner(),
                board: $card->getBoard(),
                created_at: $card->getCreatedAt(),
                updated_at: $card->getUpdatedAt(),
            );

            $response->getBody()->write(json_encode($cardResponse));
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
     * Método para encontrar muitos cartões
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Get(
        path: '/api/cards',
        tags: ['Card'],
        summary: 'Find cards',
        description: 'Get many cards',
        operationId: 'findManyCards',
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'limit value that needed to be considered for filter cards',
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
                            ref: CardResponseDTO::class
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
            $cards = $this->findManyCardUsecase->execute($params);

            $cardsResponse = [];

            foreach ($cards as $card) {
                $cardsResponse[] = new CardResponseDTO(
                    id: $card->getId(),
                    name: $card->getName(),
                    hex_bgcolor: $card->getHexBgColor(),
                    owner: $card->getOwner(),
                    board: $card->getBoard(),
                    created_at: $card->getCreatedAt(),
                    updated_at: $card->getUpdatedAt(),
                );
            }

            $response->getBody()->write(json_encode($cardsResponse));
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
     * Método para criação de um cartão
     *
     * @param Request $request
     * @param Response $response
     *
     * return Response
     */

    #[OA\Post(
        path: '/api/cards/create',
        tags: ['Card'],
        summary: 'Create a new card',
        description: 'This create a new card in the application',
        operationId: 'createCard',
        responses: [
            new OA\Response(response: 201, description: 'Card was created successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Create a card',
            required: true,
            content: new OA\JsonContent(
                ref: CreateCardDTO::class
            )
        )
    )]
    public function create(Request $request, Response $response): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));
            $body = $request->getParsedBody();

            $board = $this->findBoardUsecase->execute($body['board']);

            $ownerId = (int) $board->getOwner();
            $authId = (int) $authorizedUser->id;

            if ($ownerId != $authId) {
                throw new Exception('User unauthorized', 401);
            }

            $createCardDTO = new CreateCardDTO(...$body);
            $this->createCardUsecase->execute($createCardDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Card was created successfully'
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
     * Método para atualizar um cartão
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * return Response
     */

    #[OA\Put(
        path: '/api/cards/{id}',
        tags: ['Card'],
        summary: 'Update a card',
        description: 'This update a card in the application',
        operationId: 'updateCard',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'card id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Card was updated successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'Card not found'),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update a card',
            required: true,
            content: new OA\JsonContent(
                ref: UpdateCardDTO::class
            )
        )
    )]
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));

            $cardId = (int) $args['id'];
            $body = $request->getParsedBody();

            $cardEntity = $this->findCardUsecase->execute($cardId);
            $boardEntity = $this->findBoardUsecase->execute($cardEntity->getBoard());

            if ($authorizedUser->id !== $boardEntity->getOwner()) {
                throw new Exception('User unauthorized', 401);
            }

            $updateCardDTO = new UpdateCardDTO(...$body);
            $this->updateCardUsecase->execute($cardId, $updateCardDTO);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Card was updated successfully'
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
     * Método para deletar um cartão
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * return Response
     */

    #[OA\Delete(
        path: '/api/cards/{id}',
        tags: ['Card'],
        summary: 'Delete a card',
        description: 'This delete a card in the application',
        operationId: 'deleteCard',
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'card id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Card was deleted successfully'),
            new OA\Response(response: 400, description: 'Something was wrong'),
            new OA\Response(response: 404, description: 'Card not found'),
        ],
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $authorizedUser = isAuthorizedUser($request->getHeaderLine('Authorization'));
            $cardId = (int) $args['id'];

            $cardEntity = $this->findCardUsecase->execute($cardId);
            $boardEntity = $this->findBoardUsecase->execute($cardEntity->getBoard());

            if ($authorizedUser->id !== $boardEntity->getOwner()) {
                throw new Exception('User unauthorized', 401);
            }

            $this->deleteCardUsecase->execute($cardId);

            $response->getBody()->write(
                json_encode([
                    'message' => 'Card was deleted successfully'
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
