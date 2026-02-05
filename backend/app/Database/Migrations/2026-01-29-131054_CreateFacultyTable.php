<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFacultyTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'google_scholar_citations' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'h_index' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'i10_index' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'google_scholar_account' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
            ],
            'college_institute' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('faculty');
    }

    public function down()
    {
        $this->forge->dropTable('faculty');
    }
}