<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFacultyMasterlistTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'campus' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'position' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'college_division' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'department_office_unit' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'sex' => [
                'type' => 'CHAR',
                'constraint' => 1,
                'null' => true,
            ],
            'teaching_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('campus');
        $this->forge->addKey('name');
        $this->forge->createTable('faculty_masterlist', true);
    }

    public function down()
    {
        $this->forge->dropTable('faculty_masterlist', true);
    }
}
