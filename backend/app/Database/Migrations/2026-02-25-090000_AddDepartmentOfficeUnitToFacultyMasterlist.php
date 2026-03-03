<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDepartmentOfficeUnitToFacultyMasterlist extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('department_office_unit', 'faculty_masterlist')) {
            $this->forge->addColumn('faculty_masterlist', [
                'department_office_unit' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'college_division'
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('department_office_unit', 'faculty_masterlist')) {
            $this->forge->dropColumn('faculty_masterlist', 'department_office_unit');
        }
    }
}
