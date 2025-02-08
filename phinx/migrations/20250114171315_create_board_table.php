<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBoardTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('boards')) {
            $table = $this->table('boards');

            $table->addColumn('name', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('description', 'string', ['limit' => 500]);
            $table->addColumn('active_users', 'integer');
            $table
                ->addColumn('owner', 'integer', ['null' => false])
                ->addForeignKey('owner', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
            $table->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->save();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('boards')) {
            $table = $this->table('boards')->drop();
            $table->save();
        }
    }
}
