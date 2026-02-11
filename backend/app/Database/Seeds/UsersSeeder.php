<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $password = password_hash('admin123', PASSWORD_DEFAULT);

        $this->db->table('users')->insert([
            'username' => 'admin',
            'password_hash' => $password,
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
