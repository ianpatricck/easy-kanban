<?php declare(strict_types=1);

// |=======================================|
// | Classe para conexão com o SQLite      |
// |=======================================|

namespace App\Data\Adapter;

use App\Data\Adapter\BaseAdapter;
use PDO;

class DatabaseInMemory implements BaseAdapter
{
    protected $connection = null;

    /**
     * Start the SQLite PDO connection
     *
     * @return void
     */
    public function startConnection(): void
    {
        try {
            $configPDO = 'sqlite:' . __DIR__ . '/../../../development.db';
            $this->connection = new PDO($configPDO);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * Close the SQLite PDO connection
     *
     * @return void
     */
    public function closeConnection(): void
    {
        $this->connection = null;
    }
}
