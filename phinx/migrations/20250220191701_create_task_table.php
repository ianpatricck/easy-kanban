<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTaskTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('tasks')) {
            $table = $this->table('tasks');

            $table->addColumn('title', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('body', 'string', ['limit' => 500]);
            $table->addColumn('hex_bgcolor', 'string', ['limit' => 12]);
            $table
                ->addColumn('owner', 'integer', ['null' => false])
                ->addForeignKey('owner', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
            $table
                ->addColumn('attributed_to', 'integer', ['null' => false])
                ->addForeignKey('attributed_to', 'users', 'id');
            $table
                ->addColumn('card', 'integer', ['null' => false])
                ->addForeignKey('card', 'cards', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
            $table->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->save();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('tasks')) {
            $table = $this->table('tasks')->drop();
            $table->save();
        }
    }
}
