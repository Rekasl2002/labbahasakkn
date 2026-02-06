<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaterialItemSelection extends Migration
{
    public function up()
    {
        $table = 'session_state';

        if (!$this->db->fieldExists('current_material_file_id', $table)) {
            $this->forge->addColumn($table, [
                'current_material_file_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'current_material_id',
                ],
            ]);
        }

        if (!$this->db->fieldExists('current_material_text_index', $table)) {
            $this->forge->addColumn($table, [
                'current_material_text_index' => [
                    'type' => 'INT',
                    'null' => true,
                    'after' => 'current_material_file_id',
                ],
            ]);
        }
    }

    public function down()
    {
        $table = 'session_state';
        if ($this->db->fieldExists('current_material_text_index', $table)) {
            $this->forge->dropColumn($table, 'current_material_text_index');
        }
        if ($this->db->fieldExists('current_material_file_id', $table)) {
            $this->forge->dropColumn($table, 'current_material_file_id');
        }
    }
}
