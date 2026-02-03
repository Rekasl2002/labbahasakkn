<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaterials extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 160],
            'type' => ['type' => 'VARCHAR', 'constraint' => 10],
            'text_content' => ['type' => 'MEDIUMTEXT', 'null' => true],
            'created_by_admin_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['type', 'id']);
        $this->forge->createTable('materials', true);
    }

    public function down()
    {
        $this->forge->dropTable('materials', true);
    }
}
