<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use PhpParser\Node\Expr\AssignOp\Mul;

class ReservationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'reservation';
    protected $returnType       = 'array';
    protected $allowedFields    = ['id', 'customer_id', 'request_date', 'check_in', 'total_pepole', 'package_id', 'review', 'rating', 'total_price', 'deposit', 'status'];

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
    //Mendapatkan id reservasi baru
    public function get_new_id_api()
    {
        $lastId = $this->db->table($this->table)->select('id')->orderBy('id', 'ASC')->get()->getLastRow('array');
        if ($lastId == null) {
            $count = 0;
        } else {
            $count = (int)substr($lastId['id'], 1);
        }
        $id = sprintf('R%03d', $count + 1);
        return $id;
    }
    //Menmabhakan data reservasi baru
    public function add_reservation_api($reservation = null)
    {
        $reservation['request_date'] = Time::now('Asia/Jakarta', 'en_US');

        $insert = $this->db->table($this->table)
            ->insert($reservation);
        return $insert;
    }
    //Mendapatkan daftar reservasi
    public function get_list_reservation_by_cus_id($customer_id = null)
    {

        $query = $this->db->table($this->table)
            ->select("*")
            ->where('customer_id', $customer_id)
            ->orderBy('id', 'DESC')
            ->get();
        return $query;
    }
    //Mendapatkan detail reservas
    public function get_reservation_by_id($id = null)
    {

        $query = $this->db->table($this->table)
            ->select("*")
            ->where('id', $id)
            ->get();
        return $query;
    }
    public function get_reservation_by_pid($reservation_id = null, $homestay_id = null, $package_id = null)
    {

        $query = $this->db->table($this->table)
            ->select("*")
            ->where('id', $reservation_id)
            ->where('homestay_id', $homestay_id)
            ->where('package_id', $package_id)
            ->get();
        return $query;
    }
    public function get_reservation_by_cpid($homestay_id = null, $package_id = null)
    {

        $query = $this->db->table($this->table)
            ->select("*")
            ->where('homestay_id', $homestay_id)
            ->where('package_id', $package_id)
            ->get();
        return $query;
    }
    //Menambahkan paket wisata pada reservasi
    public function add_package_api($reservation = null, $reservation_id = null, $homestay_id = null, $package_id = null)
    {
        $reservation['homestay_id'] = $homestay_id;
        $reservation['package_id'] = $package_id;
        $query = $this->db->table($this->table)
            ->where('id', $reservation_id)
            ->update($reservation);
        return $query;
    }
    public function del_package_api($reservation_id = null)
    {
        $query = $this->db->table($this->table)
            ->set('homestay_id', null)
            ->set('package_id', null)
            ->where('id', $reservation_id)
            ->update();
        return $query;
    }
    //Finalisasi reservasi
    public function finish_reservation($reservation_id = null, $deposit = null, $total_price = null)
    {
        $reservation_finish_at = Time::now('Asia/Jakarta', 'en_US');
        $query = $this->db->table($this->table)
            ->set('status', '0')
            ->set('total_price', $total_price)
            ->set('deposit', $deposit)
            ->set('reservation_finish_at', $reservation_finish_at)
            ->where('id', $reservation_id)
            ->update();
        return $query;
    }
    //Konfirmasi reservasi
    public function confirm_reservation($reservation = null, $reservation_id = null)
    {
        $confirmed_at = Time::now('Asia/Jakarta', 'en_US');
        $reservation['status'] = '1';
        $reservation['confirmed_at'] = $confirmed_at;
        $query = $this->db->table($this->table)
            ->where('id', $reservation_id)
            ->update($reservation);
        return $query;
    }
    public function set_total_price($total_price = null, $reservation_id = null)
    {
        $reservation['total_price'] = $total_price;
        $query = $this->db->table($this->table)
            ->where('id', $reservation_id)
            ->update($reservation);
        return $query;
    }
    //Menambahkan bukti pembayaran deposit
    public function pay_deposit($reservation = null, $id = null)
    {
        $reservation['is_deposit_proof_correct'] = null;
        $reservation['deposit_at'] = Time::now('Asia/Jakarta', 'en_US');
        $query = $this->db->table($this->table)
            ->where('id', $id)
            ->update($reservation);
        return $query;
    }
    //Melakukan konfirmasi bukti pembayaran deposit
    public function confirm_deposit_reservation($reservation = null, $id = null)
    {
        if ($reservation['is_deposit_proof_correct'] == '1') {
            $reservation['deposit_confirmed_at'] = Time::now('Asia/Jakarta', 'en_US');
        }

        $query = $this->db->table($this->table)
            ->where('id', $id)
            ->update($reservation);
        return $query;
    }
    //Unggah bukti pembayaran penuh
    public function pay_full($reservation = null, $id = null)
    {
        $reservation['is_full_paid_proof_correct'] = null;
        $reservation['full_paid_at'] = Time::now('Asia/Jakarta', 'en_US');
        $query = $this->db->table($this->table)
            ->where('id', $id)
            ->update($reservation);
        return $query;
    }
    //Melakukan konfirmasi bukti pembayaran penuh
    public function confirm_full_pay_reservation($reservation = null, $id = null)
    {
        if ($reservation['is_full_paid_proof_correct'] == '1') {
            $reservation['full_paid_confirmed_at'] = Time::now('Asia/Jakarta', 'en_US');
        }

        $query = $this->db->table($this->table)
            ->where('id', $id)
            ->update($reservation);
        return $query;
    }
    public function confirm_package_price($reservation_id = null, $package_id = null)
    {
        $reservation['cust_package_price_confirmed_at'] = Time::now('Asia/Jakarta', 'en_US');
        $query = $this->db->table($this->table)
            ->where('id', $reservation_id)
            ->where('package_id', $package_id)
            ->update($reservation);
        return $query;
    }
    //Melakukan konfirmasi bukti pembayaran refund
    public function confirm_refund($reservation = null, $reservation_id = null)
    {
        if ($reservation['is_refund_proof_correct'] == '1') {
            $reservation['refund_paid_confirmed_at'] = Time::now('Asia/Jakarta', 'en_US');
        }
        $query = $this->db->table($this->table)
            ->where('id', $reservation_id)
            ->update($reservation);
        return $query;
    }
    //Membatalkan reservasi
    public function cancel_reservation($reservation = null, $reservation_id = null)
    {
        $query = $this->db->table($this->table)
            ->where('id', $reservation_id)
            ->update($reservation);
        return $query;
    }

    //Menambahkan bukti pembayaran refund
    public function refund_reservation($reservation = null, $id = null)
    {
        $reservation['is_refund_proof_correct'] = null;
        $reservation['refund_paid_at'] = Time::now('Asia/Jakarta', 'en_US');
        $query = $this->db->table($this->table)
            ->where('id', $id)
            ->update($reservation);
        return $query;
    }

    //menambahkan rating dan review
    public function add_rating($reservation = null, $reservation_id = null)
    {
        $query = $this->db->table($this->table)
            ->where('id', $reservation_id)
            ->update($reservation);
        return $query;
    }
    public function get_cust($user_id = null)
    {
        $query = $this->db->table('users')
            ->select('*')
            ->where('id', $user_id)
            ->get();
        return $query;
    }
}
