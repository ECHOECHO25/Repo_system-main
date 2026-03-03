<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeachingStatusToFacultyMasterlist extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('teaching_status', 'faculty_masterlist')) {
            $this->forge->addColumn('faculty_masterlist', [
                'teaching_status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'after' => 'sex'
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('teaching_status', 'faculty_masterlist')) {
            $this->forge->dropColumn('faculty_masterlist', 'teaching_status');
        }
    }
}
