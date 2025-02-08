<?php declare(strict_types=1);

namespace App\Data\Adapter;

interface BaseAdapter
{
    public function startConnection(): void;
    public function closeConnection(): void;
}
