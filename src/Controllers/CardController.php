<?php declare(strict_types=1);

// |============================================|
// | Controlador dos cartões                    |
// |============================================|

namespace App\Controllers;

use App\DTO\CreateCardDTO;
use App\DTO\UpdateCardDTO;
use App\Usecases\Board\FindBoardUsecase;
use App\Usecases\Card\CreateCardUsecase;
use App\Usecases\Card\FindCardUsecase;
use App\Usecases\Card\FindManyCardUsecase;
use App\Usecases\Card\UpdateCardUsecase;
use App\Usecases\User\AuthorizeUserUsecase;
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
    public function findOne(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $card = $this->findCardUsecase->execute($id);

            $cardResponse = [
                'id' => $card->getId(),
                'name' => $card->getName(),
                'hex_bgcolor' => $card->getHexBgColor(),
                'board' => $card->getBoard(),
                'created_at' => $card->getCreatedAt(),
                'updated_at' => $card->getUpdatedAt(),
            ];

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
    public function findMany(Request $request, Response $response): Response
    {
        try {
            $limit = (int) $request->getQueryParams()['limit'];
            $cards = $this->findManyCardUsecase->execute($limit);
            $cardsResponse = [];

            foreach ($cards as $card) {
                $cardsResponse[] = [
                    'id' => $card->getId(),
                    'name' => $card->getName(),
                    'hex_bgcolor' => $card->getHexBgColor(),
                    'board' => $card->getBoard(),
                    'created_at' => $card->getCreatedAt(),
                    'updated_at' => $card->getUpdatedAt(),
                ];
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
}
