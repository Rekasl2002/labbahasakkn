<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $username = 'admin';
        $password = 'admin123';

        $exists = $this->db->table('admins')->where('username', $username)->get()->getRowArray();
        if ($exists) return;

        $this->db->table('admins')->insert([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
