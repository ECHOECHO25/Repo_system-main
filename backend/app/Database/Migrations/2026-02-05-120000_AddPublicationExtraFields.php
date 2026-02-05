<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicationExtraFields extends Migration
{
    public function up()
    {
        $fields = [
            'claim_for_incentive' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'entry_by',
            ],
            'ors_registration' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'claim_for_incentive',
            ],
        ];

        $this->forge->addColumn('publications', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('publications', 'claim_for_incentive');
        $this->forge->dropColumn('publications', 'ors_registration');
    }
}
