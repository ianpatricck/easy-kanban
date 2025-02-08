<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCardTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('cards')) {
            $table = $this->table('cards');

            $table->addColumn('name', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('hex_bgcolor', 'string', ['limit' => 12]);
            $table
                ->addColumn('board', 'integer', ['null' => false])
                ->addForeignKey('board', 'boards', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
            $table->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->save();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('cards')) {
            $table = $this->table('cards')->drop();
            $table->save();
        }
    }
}
