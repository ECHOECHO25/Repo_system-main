<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MergeFacultyMasterlistIntoFaculty extends Migration
{
    public function up()
    {
        $table = 'faculty';

        if (!$this->db->tableExists($table)) {
            return;
        }

        $columnsToAdd = [
            'campus' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'email',
            ],
            'position' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'campus',
            ],
            'college_division' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'position',
            ],
            'department_office_unit' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'college_division',
            ],
            'sex' => [
                'type' => 'CHAR',
                'constraint' => 1,
                'null' => true,
                'after' => 'department_office_unit',
            ],
            'teaching_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'sex',
            ],
        ];

        foreach ($columnsToAdd as $column => $definition) {
            if (!$this->db->fieldExists($column, $table)) {
                $this->forge->addColumn($table, [$column => $definition]);
            }
        }

        if (!$this->db->tableExists('faculty_masterlist')) {
            return;
        }

        $masterlistRows = $this->db->table('faculty_masterlist')->get()->getResultArray();

        foreach ($masterlistRows as $row) {
            $name = trim((string)($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $normalized = strtolower(preg_replace('/\s+/', ' ', $name));
            $existing = $this->db->table('faculty')
                ->select('id')
                ->where('LOWER(TRIM(name)) = ' . $this->db->escape($normalized), null, false)
                ->get()
                ->getRowArray();

            $payload = [
                'name' => $name,
                'college_institute' => $row['college_division'] ?? null,
                'campus' => $row['campus'] ?? null,
                'position' => $row['position'] ?? null,
                'college_division' => $row['college_division'] ?? null,
                'department_office_unit' => $row['department_office_unit'] ?? null,
                'sex' => $row['sex'] ?? null,
                'teaching_status' => $row['teaching_status'] ?? null,
                'status' => 'active',
                'deleted_at' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->db->table('faculty')->where('id', (int)$existing['id'])->update($payload);
            } else {
                $payload['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('faculty')->insert($payload);
            }
        }
    }

    public function down()
    {
        // Intentionally keep merged data in faculty table.
    }
}
