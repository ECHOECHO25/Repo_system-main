<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePublicationNonFacultyAuthorsTable extends Migration
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
            'author_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'author_type' => [
                'type'       => 'ENUM',
                'constraint' => ['internal', 'external', 'international'],
                'default'    => 'external',
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
        $this->forge->addKey('author_type');
        $this->forge->addForeignKey('publication_id', 'publications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('publication_non_faculty_authors', true);
    }

    public function down()
    {
        $this->forge->dropTable('publication_non_faculty_authors', true);
    }
}

