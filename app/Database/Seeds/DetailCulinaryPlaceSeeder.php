<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class DetailCulinaryPlaceSeeder extends Seeder
{
    public function run()
    {
        $rows = array_map('str_getcsv', file(WRITEPATH.'seeds/'. 'detail_culinary_place.csv'));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = [
                'culinary_place_id' => $row[0],
                'lat' => $row[1],
                'long' => $row[2],
                'description' => $row[3],
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ];

            $this->db->table('detail_culinary_place')->insert($data);
        }
    }
}
