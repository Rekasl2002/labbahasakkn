<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVoiceLocksToSessionState extends Migration
{
    public function up()
    {
        $this->forge->addColumn('session_state', [
            'allow_student_mic' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'allow_student_speaker' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('session_state', ['allow_student_mic', 'allow_student_speaker']);
    }
}
