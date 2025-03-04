<?php declare(strict_types=1);

// |===============================================|
// | Classe base para conexão com bancos de dados  |
// |===============================================|

namespace App\Data\Adapter;

interface BaseAdapter
{
    public function startConnection(): void;
    public function closeConnection(): void;
}
