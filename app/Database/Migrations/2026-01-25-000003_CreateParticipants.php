<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParticipants extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'INT', 'unsigned' => true],
            'student_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'class_name' => ['type' => 'VARCHAR', 'constraint' => 60],
            'device_label' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'mic_on' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'speaker_on' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'joined_at' => ['type' => 'DATETIME', 'null' => true],
            'last_seen_at' => ['type' => 'DATETIME', 'null' => true],
            'left_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['session_id', 'id']);
        $this->forge->addKey(['session_id', 'last_seen_at']);
        $this->forge->createTable('participants', true);
    }

    public function down()
    {
        $this->forge->dropTable('participants', true);
    }
}
