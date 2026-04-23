<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicationReviewFields extends Migration
{
    public function up()
    {
        $table = 'publications';
        if (!$this->db->tableExists($table)) {
            return;
        }

        if (!$this->db->fieldExists('review_status', $table)) {
            $this->forge->addColumn($table, [
                'review_status' => [
                    'type' => 'ENUM',
                    'constraint' => ['pending_review', 'approved', 'rejected'],
                    'default' => 'approved',
                    'after' => 'entry_by',
                ],
            ]);
        }

        if (!$this->db->fieldExists('submitted_by_user_id', $table)) {
            $this->forge->addColumn($table, [
                'submitted_by_user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'review_status',
                ],
            ]);
        }

        if (!$this->db->fieldExists('reviewed_by_user_id', $table)) {
            $this->forge->addColumn($table, [
                'reviewed_by_user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'submitted_by_user_id',
                ],
            ]);
        }

        if (!$this->db->fieldExists('reviewed_at', $table)) {
            $this->forge->addColumn($table, [
                'reviewed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'reviewed_by_user_id',
                ],
            ]);
        }

        if (!$this->db->fieldExists('review_remarks', $table)) {
            $this->forge->addColumn($table, [
                'review_remarks' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'reviewed_at',
                ],
            ]);
        }
    }

    public function down()
    {
        $table = 'publications';
        if (!$this->db->tableExists($table)) {
            return;
        }

        foreach (['review_remarks', 'reviewed_at', 'reviewed_by_user_id', 'submitted_by_user_id', 'review_status'] as $column) {
            if ($this->db->fieldExists($column, $table)) {
                $this->forge->dropColumn($table, $column);
            }
        }
    }
}

