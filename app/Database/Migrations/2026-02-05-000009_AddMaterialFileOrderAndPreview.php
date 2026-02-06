<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaterialFileOrderAndPreview extends Migration
{
    public function up()
    {
        $table = 'material_files';

        if (!$this->db->fieldExists('sort_order', $table)) {
            $this->forge->addColumn($table, [
                'sort_order' => [
                    'type' => 'INT',
                    'null' => false,
                    'default' => 0,
                    'after' => 'material_id',
                ],
            ]);
        }

        if (!$this->db->fieldExists('preview_url_path', $table)) {
            $this->forge->addColumn($table, [
                'preview_url_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'url_path',
                ],
            ]);
        }
    }

    public function down()
    {
        $table = 'material_files';
        if ($this->db->fieldExists('preview_url_path', $table)) {
            $this->forge->dropColumn($table, 'preview_url_path');
        }
        if ($this->db->fieldExists('sort_order', $table)) {
            $this->forge->dropColumn($table, 'sort_order');
        }
    }
}
