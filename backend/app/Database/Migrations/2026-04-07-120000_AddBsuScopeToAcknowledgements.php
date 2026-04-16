<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBsuScopeToAcknowledgements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('acknowledgements', [
            'bsu_scope' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'position',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('acknowledgements', 'bsu_scope');
    }
}
