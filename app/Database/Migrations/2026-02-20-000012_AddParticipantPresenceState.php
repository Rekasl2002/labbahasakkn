<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParticipantPresenceState extends Migration
{
    public function up()
    {
        $table = 'participants';

        if (!$this->db->fieldExists('presence_state', $table)) {
            $this->forge->addColumn($table, [
                'presence_state' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'offline',
                    'after' => 'left_at',
                ],
            ]);
        }

        if (!$this->db->fieldExists('presence_page', $table)) {
            $this->forge->addColumn($table, [
                'presence_page' => [
                    'type' => 'VARCHAR',
                    'constraint' => 24,
                    'null' => true,
                    'after' => 'presence_state',
                ],
            ]);
        }

        if (!$this->db->fieldExists('presence_reason', $table)) {
            $this->forge->addColumn($table, [
                'presence_reason' => [
                    'type' => 'VARCHAR',
                    'constraint' => 60,
                    'null' => true,
                    'after' => 'presence_page',
                ],
            ]);
        }

        if (!$this->db->fieldExists('presence_updated_at', $table)) {
            $this->forge->addColumn($table, [
                'presence_updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'presence_reason',
                ],
            ]);
        }

        try {
            $this->db->query("ALTER TABLE `participants` ADD KEY `session_presence_state` (`session_id`,`presence_state`)");
        } catch (\Throwable $e) {
            // ignore if index already exists
        }
    }

    public function down()
    {
        $table = 'participants';

        try {
            $this->db->query("ALTER TABLE `participants` DROP INDEX `session_presence_state`");
        } catch (\Throwable $e) {
            // ignore if index missing
        }

        $drops = [];
        foreach (['presence_updated_at', 'presence_reason', 'presence_page', 'presence_state'] as $col) {
            if ($this->db->fieldExists($col, $table)) {
                $drops[] = $col;
            }
        }
        if ($drops) {
            $this->forge->dropColumn($table, $drops);
        }
    }
}
