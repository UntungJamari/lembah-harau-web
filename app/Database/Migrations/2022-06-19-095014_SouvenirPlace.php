<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SouvenirPlace extends Migration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 2,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'employee_name' => [
                'type' => 'VARCHAR',
                'constraint' => 25,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 13,
            ],
            'open' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'close' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'geom' => [
                'type' => 'GEOMETRY',
                'null' => true,
            ],
            'lat' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
            ],
            'lng' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ];

        $this->db->disableForeignKeyChecks();
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('souvenir_place');
        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->forge->dropTable('souvenir_place');
    }
}
