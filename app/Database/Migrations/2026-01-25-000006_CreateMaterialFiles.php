<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaterialFiles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'material_id' => ['type' => 'INT', 'unsigned' => true],
            'filename' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'size' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'url_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['material_id', 'id']);
        $this->forge->createTable('material_files', true);
    }

    public function down()
    {
        $this->forge->dropTable('material_files', true);
    }
}
