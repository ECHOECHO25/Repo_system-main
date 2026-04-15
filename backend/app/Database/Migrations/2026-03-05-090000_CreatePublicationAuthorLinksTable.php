<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePublicationAuthorLinksTable extends Migration
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
            'publication_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'faculty_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'author_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'match_type' => [
                'type'       => 'ENUM',
                'constraint' => ['auto', 'manual'],
                'default'    => 'auto',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'rejected'],
                'default'    => 'pending',
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
        $this->forge->addKey('publication_id');
        $this->forge->addKey('faculty_id');
        $this->forge->addKey('status');
        $this->forge->createTable('publication_author_links');
    }

    public function down()
    {
        $this->forge->dropTable('publication_author_links');
    }
}
