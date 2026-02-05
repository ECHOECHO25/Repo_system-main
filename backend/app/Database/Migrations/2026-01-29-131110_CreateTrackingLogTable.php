<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrackingLogTable extends Migration
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
            'action_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'entity_type' => [
                'type'       => 'ENUM',
                'constraint' => ['publication', 'faculty', 'system'],
            ],
            'entity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'updated_by' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('tracking_log');
    }

    public function down()
    {
        $this->forge->dropTable('tracking_log');
    }
}