<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCommentTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('comments')) {
            $table = $this->table('comments');

            $table->addColumn('body', 'string', ['limit' => 300]);
            $table
                ->addColumn('owner', 'integer', ['null' => false])
                ->addForeignKey('owner', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
            $table
                ->addColumn('task', 'integer', ['null' => false])
                ->addForeignKey('task', 'tasks', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE']);
            $table->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->save();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('comments')) {
            $table = $this->table('comments')->drop();
            $table->save();
        }
    }
}
