<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicationEncoderRoleToUsers extends Migration
{
    public function up()
    {
        $this->db->query("
            UPDATE users
            SET role = 'researcher'
            WHERE role = 'publication_encoder'
        ");

        $this->db->query("
            ALTER TABLE users
            MODIFY role ENUM('admin', 'editor', 'viewer', 'researcher')
            NOT NULL DEFAULT 'viewer'
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE users
            MODIFY role ENUM('admin', 'editor', 'viewer')
            NOT NULL DEFAULT 'viewer'
        ");
    }
}
