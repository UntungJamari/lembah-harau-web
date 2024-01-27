<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class HomestayUnitFacilityModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'homestay_unit_facility';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['id', 'name'];

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

    public function get_new_id_api()
    {
        $lastId = $this->db->table($this->table)->select('id')->orderBy('id', 'ASC')->get()->getLastRow('array');
        if ($lastId == null) {
            $count = 0;
        } else {
            $count = (int)substr($lastId['id'], 0);
        }
        $id = sprintf('%02d', $count + 1);
        return $id;
    }
    public function get_list_fc_api()
    {
        $query = $this->db->table($this->table)
            ->select('id, name')
            ->get();
        return $query;
    }
    public function add_huf_api($product = null)
    {
        $new_id = $this->get_new_id_api();
        $product['id'] = $new_id;
        $product['created_at'] = Time::now();
        $product['updated_at'] = Time::now();
        $insert = $this->db->table($this->table)
            ->insert($product);
        return $insert;
    }
    public function edit_huf_api($product = null)
    {
        $product['updated_at'] = Time::now();
        $update = $this->db->table($this->table)
            ->where('id', $product['id'])
            ->update($product);

        return $update;
    }
    public function get_list_huf_not_owned_api($id = null)
    {
        $id = implode(", ", $id);
        $query = $this->db->table($this->table)
            ->select('id, name')
            ->where('id NOT IN (' . $id . ')')
            ->get();
        return $query;
    }
}
