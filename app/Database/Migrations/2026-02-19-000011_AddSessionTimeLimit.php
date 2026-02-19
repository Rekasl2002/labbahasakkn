<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSessionTimeLimit extends Migration
{
    public function up()
    {
        $fieldsToAdd = [];

        if (!$this->db->fieldExists('duration_limit_minutes', 'sessions')) {
            $fieldsToAdd['duration_limit_minutes'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ];
        }

        if (!$this->db->fieldExists('deadline_at', 'sessions')) {
            $fieldsToAdd['deadline_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (!$this->db->fieldExists('extension_minutes', 'sessions')) {
            $fieldsToAdd['extension_minutes'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ];
        }

        if ($fieldsToAdd !== []) {
            $this->forge->addColumn('sessions', $fieldsToAdd);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('extension_minutes', 'sessions')) {
            $this->forge->dropColumn('sessions', 'extension_minutes');
        }

        if ($this->db->fieldExists('deadline_at', 'sessions')) {
            $this->forge->dropColumn('sessions', 'deadline_at');
        }

        if ($this->db->fieldExists('duration_limit_minutes', 'sessions')) {
            $this->forge->dropColumn('sessions', 'duration_limit_minutes');
        }
    }
}
