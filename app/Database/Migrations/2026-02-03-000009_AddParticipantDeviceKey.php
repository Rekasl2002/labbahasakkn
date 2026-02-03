<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParticipantDeviceKey extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('device_key', 'participants')) {
            $this->forge->addColumn('participants', [
                'device_key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 64,
                    'null' => true,
                    'after' => 'device_label',
                ],
            ]);
        }

        try {
            $this->db->query("ALTER TABLE `participants` ADD KEY `session_identity` (`session_id`,`student_name`,`class_name`,`device_key`)");
        } catch (\Throwable $e) {
            // ignore if index already exists
        }
    }

    public function down()
    {
        try {
            $this->db->query("ALTER TABLE `participants` DROP INDEX `session_identity`");
        } catch (\Throwable $e) {
            // ignore if index missing
        }

        if ($this->db->fieldExists('device_key', 'participants')) {
            $this->forge->dropColumn('participants', 'device_key');
        }
    }
}
