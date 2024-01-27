<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class AttractionFacilityDetailModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'attraction_facility_detail';
    protected $returnType       = 'array';
    protected $allowedFields    = ['attraction_id', 'attraction_facility_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // API
    public function get_facility_by_at_api($attraction_id = null)
    {
        $query = $this->db->table($this->table)
            ->select('attraction_facility.name')
            ->where('attraction_id', $attraction_id)
            ->join('attraction_facility', 'attraction_facility_detail.attraction_facility_id = attraction_facility.id')
            ->get();
        return $query;
    }

    public function get_facility_by_fc_api($facility_id = null)
    {
        $query = $this->db->table($this->table)
            ->select('*')
            ->where('attraction_facility_id', $facility_id)
            ->get();
        return $query;
    }


    public function get_new_id_api()
    {
        $lastId = $this->db->table($this->table)->select('id')->orderBy('id', 'ASC')->get()->getLastRow('array');
        $count = (int)substr($lastId['id'], 0);
        $id = sprintf('%03d', $count + 1);
        return $id;
    }

    public function add_facility_api($id = null, $data = null)
    {
        $query = false;
        foreach ($data as $facility) {
            $content = [
                'attraction_id' => $id,
                'attraction_facility_id' => $facility,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ];
            $query = $this->db->table($this->table)->insert($content);
        }
        return $query;
    }

    public function update_facility_api($id = null, $data = null)
    {
        $queryDel = $this->db->table($this->table)->delete(['attraction_id' => $id]);
        $queryIns = $this->add_facility_api($id, $data);
        return $queryDel && $queryIns;
    }
}
