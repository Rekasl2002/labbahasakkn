<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaterialFileCover extends Migration
{
    public function up()
    {
        $table = 'material_files';
        if (!$this->db->tableExists($table)) {
            return;
        }
        if (!$this->db->fieldExists('cover_url_path', $table)) {
            $this->forge->addColumn($table, [
                'cover_url_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'preview_url_path',
                ],
            ]);
        }
    }

    public function down()
    {
        $table = 'material_files';
        if ($this->db->tableExists($table) && $this->db->fieldExists('cover_url_path', $table)) {
            $this->forge->dropColumn($table, 'cover_url_path');
        }
    }
}
