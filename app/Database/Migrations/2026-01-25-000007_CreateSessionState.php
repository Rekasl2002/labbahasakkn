<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSessionState extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'session_id' => ['type' => 'INT', 'unsigned' => true],
            'current_material_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'broadcast_text' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('session_id', true);
        $this->forge->createTable('session_state', true);
    }

    public function down()
    {
        $this->forge->dropTable('session_state', true);
    }
}
