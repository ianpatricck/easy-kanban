<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserTableMigration extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('users')) {
            $table = $this->table('users');

            $table->addColumn('username', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('name', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('email', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('password', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('bio', 'string', ['limit' => 200]);
            $table->addColumn('avatar', 'string', ['limit' => 100]);
            $table->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['username', 'email'], ['unique' => true]);

            $table->save();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('users')) {
            $table = $this->table('users')->drop();
            $table->save();
        }
    }
}
