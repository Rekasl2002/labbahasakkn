<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessages extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'INT', 'unsigned' => true],
            'sender_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'sender_admin_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sender_participant_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'target_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'target_participant_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'body' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['session_id', 'id']);
        $this->forge->addKey(['session_id', 'target_type']);
        $this->forge->addKey(['target_participant_id', 'id']);
        $this->forge->createTable('messages', true);
    }

    public function down()
    {
        $this->forge->dropTable('messages', true);
    }
}
