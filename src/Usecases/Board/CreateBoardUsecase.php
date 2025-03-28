<?php declare(strict_types=1);

// |=======================================|
// | Caso de uso para criação de um quadro |
// |=======================================|

namespace App\Usecases\Board;

use App\Data\Repositories\BoardRepository;
use App\DTO\CreateBoardDTO;
use Exception;

class CreateBoardUsecase
{
    public function __construct(
        private BoardRepository $boardRepository
    ) {}

    public function execute(CreateBoardDTO $createBoardDTO)
    {
        $name = $createBoardDTO->name;

        if (!isset($name) || !(strlen(trim($name)) > 0)) {
            throw new Exception("The board's name was not provided", 400);
        }

        $this->boardRepository->create($createBoardDTO);
    }
}
