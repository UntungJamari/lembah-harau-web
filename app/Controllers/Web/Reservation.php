<?php

namespace App\Controllers\Web;

use CodeIgniter\Files\File;
use CodeIgniter\RESTful\ResourcePresenter;
use CodeIgniter\API\ResponseTrait;

use App\Models\PackageModel;
use App\Models\PackageDayModel;
use App\Models\PackageDetailModel;
use App\Models\PackageServiceDetailModel;
use App\Models\HomestayModel;
use App\Models\HomestayUnitModel;
use App\Models\HomestayUnitGalleryModel;
use App\Models\HomestayExclusiveActivityModel;
use App\Models\HomestayAdditionalAmenitiesModel;
use App\Models\ReservationModel;
use App\Models\ReservationHomestayUnitDetailModel;
use App\Models\ReservationHomestayUnitDetailBackUpModel;
use App\Models\ReservationHomestayActivityDetailModel;
use App\Models\ReservationHomestayAdditionalAmenitiesDetailModel;
use App\Models\AttractionModel;
use App\Models\CulinaryPlaceModel;
use App\Models\SouvenirPlaceModel;
use App\Models\ServiceProviderModel;
use App\Models\WorshipPlaceModel;
use App\Models\EventModel;
use App\Models\UserBankAccountModel;

class Reservation extends ResourcePresenter
{
    use ResponseTrait;

    protected $packageModel;
    protected $packageDayModel;
    protected $packageDetailModel;
    protected $packageServiceDetailModel;
    protected $homestayModel;
    protected $homestayUnitModel;
    protected $homestayUnitGalleryModel;
    protected $homestayExclusiveActivityModel;
    protected $homestayAdditionalAmenitiesModel;
    protected $reservationModel;
    protected $reservationHomestayUnitDetailModel;
    protected $reservationHomestayUnitDetailBackUpModel;
    protected $reservationHomestayActivityDetailModel;
    protected $reservationHomestayAdditionalAmenitiesDetailModel;

    protected $attractionModel;
    protected $culinaryPlaceModel;
    protected $souvenirPlaceModel;
    protected $serviceProviderModel;
    protected $worshipPlaceModel;
    protected $eventModel;
    protected $userBankAccountModel;

    protected $helpers = ['auth', 'url', 'filesystem'];

    public function __construct()
    {
        $this->packageModel = new PackageModel();
        $this->packageDayModel = new PackageDayModel();
        $this->packageDetailModel = new PackageDetailModel();
        $this->packageServiceDetailModel = new PackageServiceDetailModel();
        $this->homestayModel = new HomestayModel();
        $this->homestayUnitModel = new HomestayUnitModel();
        $this->homestayUnitGalleryModel = new HomestayUnitGalleryModel();
        $this->homestayExclusiveActivityModel = new HomestayExclusiveActivityModel();
        $this->homestayAdditionalAmenitiesModel = new HomestayAdditionalAmenitiesModel();
        $this->reservationModel = new ReservationModel();
        $this->reservationHomestayUnitDetailModel = new ReservationHomestayUnitDetailModel();
        $this->reservationHomestayUnitDetailBackUpModel = new ReservationHomestayUnitDetailBackUpModel();
        $this->reservationHomestayActivityDetailModel = new ReservationHomestayActivityDetailModel();
        $this->reservationHomestayAdditionalAmenitiesDetailModel = new ReservationHomestayAdditionalAmenitiesDetailModel();

        $this->attractionModel = new AttractionModel();
        $this->culinaryPlaceModel = new CulinaryPlaceModel();
        $this->souvenirPlaceModel = new SouvenirPlaceModel();
        $this->serviceProviderModel = new ServiceProviderModel();
        $this->worshipPlaceModel = new WorshipPlaceModel();
        $this->eventModel = new EventModel();
        $this->userBankAccountModel = new UserBankAccountModel();
    }
    //Fungsi mendapatkan daftar reservasi
    public function listReservation()
    {
        //Mendapatkan daftar reservasi
        $reservations = $this->reservationModel->get_list_reservation_by_cus_id(user()->id)->getResultArray();

        foreach ($reservations as $reservation) {
            if ($reservation['canceled_at'] == null) {
                //Melakukan pengecekan apakah ada reservasi yang telah batal
                $checkIsReservationCancel = $this->checkIsReservationCancel($reservation);
            }
        }
        //Mendapatkan daftar reservasi
        $reservations = $this->reservationModel->get_list_reservation_by_cus_id(user()->id)->getResultArray();

        for ($i = 0; $i < count($reservations); $i++) {
            if ($reservations[$i]['package_id'] != null) {
                $package = $this->packageModel->get_package_by_id_api($reservations[$i]['homestay_id'], $reservations[$i]['package_id'])->getRowArray();

                $reservations[$i]['price'] = $package['price'];
                $reservations[$i]['is_custom'] = $package['is_custom'];
            } else {
                $reservations[$i]['price'] = null;
                $reservations[$i]['is_custom'] = null;
            }
        }
        $data = [
            'title' => 'Reservation',
            'data' => $reservations,
        ];

        return view('web/reservation_list', $data);
    }

    public function newReservation($homestay_id = null)
    {
        $homestay = $this->homestayModel->get_hs_by_id_api($homestay_id)->getRowArray();

        $data = [
            'title' =>  $homestay['name'] . ' Reservation',
            'homestay_id' => $homestay_id,
        ];

        return view('web/reservation_form', $data);
    }

    public function getUnit($homestay_id = null, $unit_type = null, $check_in = null, $day_of_stay = null)
    {
        $number_not_available = array();
        for ($i = 0; $i < $day_of_stay; $i++) {
            $date = date('Y-m-d', strtotime($check_in));
            $unit_number_not_available = $this->reservationHomestayUnitDetailModel->get_unit_number_not_available($homestay_id, $unit_type, $date)->getResultArray();
            $check_in = date("Y-m-d", strtotime($date . ' + 1 days'));
            foreach ($unit_number_not_available as $row) {
                $number_not_available[] = $row['unit_number'];
            }
        }
        $number_not_available = array_unique($number_not_available);

        if (empty($number_not_available)) {
            $number_not_available = ['AAA'];
        }
        $homestay_unit = $this->homestayUnitModel->get_hu_in_number($homestay_id, $unit_type, $number_not_available)->getResultArray();

        for ($i = 0; $i < count($homestay_unit); $i++) {
            $getRID = $this->reservationHomestayUnitDetailModel->get_reservation_by_huid($homestay_id, $unit_type, $homestay_unit[$i]['unit_number'])->getResultArray();
            $rating = 0;
            $rating_divider = 0;
            foreach ($getRID as $rid) {
                $reservation = $this->reservationModel->get_reservation_by_id($rid['reservation_id'])->getRowArray();
                if ($reservation['rating'] != null) {
                    $rating = $rating + $reservation['rating'];
                    $rating_divider++;
                }
            }
            if ($rating != 0) {
                $avg_rating = $rating / $rating_divider;
            } else {
                $avg_rating = 0;
            }
            $homestay_unit[$i]['avg_rating'] = $avg_rating;
            $unit_gallery = $this->homestayUnitGalleryModel->get_gallery_api($homestay_id, $unit_type, $homestay_unit[$i]['unit_number'])->getRowArray();
            $homestay_unit[$i]['url'] = $unit_gallery['url'];
        }

        if (empty($homestay_unit)) {
            $homestay_unit = 'Empty';
        }

        $response = [
            'data' => $homestay_unit,
            'status' => 200,
            'message' => [
                "Success get Homestay Unit Available"
            ]
        ];
        return $this->respond($response);
    }
    //Fungsi menambahkan data reservasi
    public function createReservation($homestay_id = null)
    {
        $request = $this->request->getPost();

        //Mendapatkan id reservasi baru
        $new_id = $this->reservationModel->get_new_id_api();

        $total_price = 0;

        $homestay_units = array();
        for ($i = 0; $i < count($request['unit_number']); $i++) {
            $homestay_unit = $this->homestayUnitModel->get_hu_by_id_api($homestay_id, $request['unit_type'], $request['unit_number'][$i])->getRowArray();
            $homestay_units[] = $homestay_unit;
            $total_price = $total_price + $homestay_unit['price'];
        }

        if ($request['unit_type'] == '3') {
            $requestData = [
                'id' => $new_id,
                'customer_id' => user()->id,
                'check_in' => $request['check_in'] . ' 06:00',
                'total_people' => $request['total_people']
            ];
        } else {
            $requestData = [
                'id' => $new_id,
                'customer_id' => user()->id,
                'check_in' => $request['check_in'] . ' 14:00',
                'total_people' => $request['total_people']
            ];
        }


        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        //Menambahkan data reservasi
        $addReservation = $this->reservationModel->add_reservation_api($requestData);

        $date = $request['check_in'];
        $date_array = array();
        for ($i = 0; $i < (int) $request['day_of_stay']; $i++) {
            $date = date('Y-m-d', strtotime($date));
            $date_array[] = $date;
            for ($j = 0; $j < count($homestay_units); $j++) {
                //Menambahkan data unit homestay yang direservasi
                $addReservationDetail = $this->reservationHomestayUnitDetailModel->add_reservation_detail_api($homestay_id, $request['unit_type'], $homestay_units[$j]['unit_number'], $date, $new_id);
            }
            $date = date("Y-m-d", strtotime($date . ' + 1 days'));
        }

        if ($addReservation) {
            return redirect()->to(base_url('web/reservation/detail/' . $new_id));
        } else {
            return redirect()->back()->withInput();
        }
    }

    //Fungsi melihat detail reservasi
    public function detailReservation($id = null)
    {
        //Mendapatkan detail reservasi
        $reservation = $this->reservationModel->get_reservation_by_id($id)->getRowArray();

        if (empty($reservation)) {
            return redirect()->to(base_url('web/reservation'));
        }

        if ($reservation['canceled_at'] == null) {
            $checkIsReservationCancel = $this->checkIsReservationCancel($reservation);
        }
        $reservation = $this->reservationModel->get_reservation_by_id($id)->getRowArray();

        if ($reservation['canceled_at'] != null) {
            //Mendapatkan data unit homestay
            $reservation_detail = $this->reservationHomestayUnitDetailBackUpModel->get_reservation_by_id($id)->getResultArray();
        } else {
            //Mendapatkan data unit homestay
            $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($id)->getResultArray();
        }

        $homestay_id = array();
        $unit_type = array();
        $day_of_stay = array();
        $unit_number = array();
        for ($i = 0; $i < count($reservation_detail); $i++) {
            $day_of_stay[] = $reservation_detail[$i]['date'];
            $unit_number[] = $reservation_detail[$i]['unit_number'];
            $homestay_id[] = $reservation_detail[$i]['homestay_id'];
            $unit_type[] = $reservation_detail[$i]['unit_type'];
        }
        $day_of_stay = array_unique($day_of_stay);
        $unit_number = array_values(array_unique($unit_number));
        $homestay_id = array_unique($homestay_id);
        $unit_type = array_unique($unit_type);

        $homestay_units = array();
        for ($j = 0; $j < count($unit_number); $j++) {
            $homestay_unit = $this->homestayUnitModel->get_hu_by_id_api($homestay_id[0], $unit_type[0], $unit_number[$j])->getRowArray();
            $homestay_units[] = $homestay_unit;
        }

        for ($i = 0; $i < count($homestay_units); $i++) {
            $list_gallery = $this->homestayUnitGalleryModel->get_gallery_api($homestay_id, $homestay_units[$i]['unit_type'], $homestay_units[$i]['unit_number'])->getResultArray();
            $galleries = array();
            foreach ($list_gallery as $gallery) {
                $galleries[] = $gallery['url'];
            }
            $homestay_units[$i]['galleries'] = $galleries;
        }

        $check_out = date("Y-m-d 12:00:00", strtotime($reservation['check_in'] . ' + ' . count($day_of_stay) . ' days'));
        $reservation['check_out'] = $check_out;
        $homestay_data = $this->homestayModel->get_hs_by_id_api($homestay_id[0])->getRowArray();

        $homestay['id'] = $homestay_data['id'];
        $homestay['name'] = $homestay_data['name'];
        $homestay['phone'] = $homestay_data['phone'];
        if ($unit_type[0] == '1') {
            $homestay['unit_type'] = "Room";
        } elseif ($unit_type[0] == '2') {
            $homestay['unit_type'] = "Villa";
        } else {
            $homestay['unit_type'] = "Hall";
        }

        $reservation['day_of_stay'] = count($day_of_stay);

        //Medapatakan data amenities tambahan
        $reservation_additional_amenities = $this->reservationHomestayAdditionalAmenitiesDetailModel->get_haa_by_rid_api($homestay_id[0], $reservation['id'])->getResultArray();
        for ($i = 0; $i < count($reservation_additional_amenities); $i++) {
            $amenities = $this->homestayAdditionalAmenitiesModel->get_haa_by_id_api($reservation_additional_amenities[$i]['homestay_id'], $reservation_additional_amenities[$i]['additional_amenities_id'])->getRowArray();
            $reservation_additional_amenities[$i]['name'] = $amenities['name'];
            $reservation_additional_amenities[$i]['category'] = $amenities['category'];
            $reservation_additional_amenities[$i]['price'] = $amenities['price'];
            $reservation_additional_amenities[$i]['is_order_count_per_day'] = $amenities['is_order_count_per_day'];
            $reservation_additional_amenities[$i]['is_order_count_per_person'] = $amenities['is_order_count_per_person'];
            $reservation_additional_amenities[$i]['is_order_count_per_room'] = $amenities['is_order_count_per_room'];
            $reservation_additional_amenities[$i]['description'] = $amenities['description'];
            $reservation_additional_amenities[$i]['image_url'] = $amenities['image_url'];
            $reservation_additional_amenities[$i]['id'] = $reservation_additional_amenities[$i]['additional_amenities_id'];
        }
        $homestay_owner_bank_account = $this->userBankAccountModel->get_user_bank_account($homestay_data['owner'])->getRowArray();

        $customer_bank_account = $this->userBankAccountModel->get_user_bank_account($reservation['customer_id'])->getRowArray();

        $data = [
            'title' => 'Reservation',
            'reservation' => $reservation,
            'homestay' => $homestay,
            'homestay_owner_bank_account' => $homestay_owner_bank_account,
            'customer_bank_account' => $customer_bank_account,
            'homestay_unit' => $homestay_units,
            'reservation_additional_amenities' => $reservation_additional_amenities,
        ];

        if (!empty($reservation['package_id'])) {
            //Mendapatkan data paket wisata
            $package = $this->packageModel->get_package_by_id_api($reservation['homestay_id'], $reservation['package_id'])->getRowArray();
            $data['package'] = $package;
            $data2 = $this->getPackageDetail($reservation['id'], $reservation['homestay_id'], $reservation['package_id']);

            $data = array_merge($data, $data2);
        }
        return view('web/reservation_detail', $data);
    }



    public function getPackage($homestay_id = null, $reservation_id = null)
    {
        $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($reservation_id)->getResultArray();
        $day_of_stay = array();
        for ($i = 0; $i < count($reservation_detail); $i++) {
            $day_of_stay[] = $reservation_detail[$i]['date'];
        }
        $day_of_stay = array_unique($day_of_stay);

        $package_detail = $this->packageDetailModel->get_pid_by_day($homestay_id, count($day_of_stay))->getResultArray();

        $package_id_available = array();
        for ($i = 0; $i < count($package_detail); $i++) {
            if ($package_detail[$i]['total_day'] <= count($day_of_stay)) {
                $package_id_available[] = $package_detail[$i]['package_id'];
            }
        }

        $packages = array();
        for ($i = 0; $i < count($package_id_available); $i++) {
            $package = $this->packageModel->get_package_by_id_api($homestay_id, $package_id_available[$i])->getRowArray();
            if (($package['homestay_id'] == $homestay_id) && ($package['is_custom'] == '0')) {
                foreach ($package_detail as $packageDetail) {
                    if ($packageDetail['package_id'] == $package['package_id']) {
                        $package['total_day'] = $packageDetail['total_day'];
                        $package['id'] = $package['package_id'];
                    }
                }
                $packages[] = $package;
            }
        }

        for ($i = 0; $i < count($packages); $i++) {
            $reservations = $this->reservationModel->get_reservation_by_cpid($homestay_id, $packages[$i]['package_id'])->getResultArray();
            $rating = 0;
            $rating_divider = 0;
            foreach ($reservations as $reservation) {
                if ($reservation['rating'] != null) {
                    $rating = $rating + $reservation['rating'];
                    $rating_divider++;
                }
            }
            if ($rating != 0) {
                $avg_rating = $rating / $rating_divider;
            } else {
                $avg_rating = 0;
            }
            $packages[$i]['avg_rating'] = $avg_rating;
        }


        $reservation = $this->reservationModel->get_reservation_by_id($reservation_id)->getRowArray();
        $data = [
            'title' => 'Reservation',
            'h_id' => $homestay_id,
            'reservation_id' => $reservation_id,
            'total_people' => $reservation['total_people'],
            'package' => $packages,
        ];

        return view('web/reservation_package', $data);
    }
    //menambahkan paket wisata custom pada reservasi
    public function createCustomPackage($homestay_id = null, $reservation_id = null)
    {
        $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($reservation_id)->getResultArray();
        $day_of_stay = array();
        for ($i = 0; $i < count($reservation_detail); $i++) {
            $day_of_stay[] = $reservation_detail[$i]['date'];
        }
        $day_of_stay = array_unique($day_of_stay);

        $package_new_id = $this->packageModel->get_new_id_api($homestay_id);

        date_default_timezone_set("Asia/Jakarta");
        $packageName = 'Custom by ' . user()->username . ' at ' . date("Y-m-d H:i");

        $requestData = [
            'package_id' => $package_new_id,
            'homestay_id' => $homestay_id,
            'name' => $packageName,
            'min_capacity' => 0,
            'price' => 0,
            'is_custom' => '1',
        ];

        $createPackage = $this->packageModel->add_package_api($requestData);

        $reservation = null;
        $addPackageonReservation = $this->reservationModel->add_package_api($reservation, $reservation_id, $homestay_id, $package_new_id);


        if ($createPackage) {
            return redirect()->to(base_url('web/reservation/package/customPackage/' . $reservation_id . "/"  . $homestay_id . "/" . $package_new_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //menambahkan paket wisata extend pada reservasi
    public function createExtendPackage($homestay_id = null, $reservation_id = null, $package_id = null)
    {
        $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($reservation_id)->getResultArray();
        $day_of_stay = array();
        for ($i = 0; $i < count($reservation_detail); $i++) {
            $day_of_stay[] = $reservation_detail[$i]['date'];
        }
        $day_of_stay = array_unique($day_of_stay);

        $package_new_id = $this->packageModel->get_new_id_api($homestay_id);

        $package = $this->packageModel->get_package_by_id_api($homestay_id, $package_id)->getRowArray();

        $package_day = $this->packageDayModel->get_pd_by_pacakage_id_api($homestay_id, $package_id)->getResultArray();

        $package_detail = $this->packageDetailModel->get_pd_by_pacakage_id_api($homestay_id, $package_id)->getResultArray();

        $package_service = $this->packageServiceDetailModel->get_list_service_by_id($homestay_id, $package_id)->getResultArray();
        for ($i = 0; $i < count($package_service); $i++) {
            unset($package_service[$i]['name']);
        }

        date_default_timezone_set("Asia/Jakarta");
        $packageName = $package['name'] . ' extend by ' . user()->username . ' at ' . date("Y-m-d H:i");

        $requestData = [
            'package_id' => $package_new_id,
            'homestay_id' => $homestay_id,
            'name' => $packageName,
            'min_capacity' => $package['min_capacity'],
            'price' => $package['price'],
            'is_custom' => '1',
        ];

        $createPackage = $this->packageModel->add_package_api($requestData);

        foreach ($package_day as $packageDay) {
            $packageDay['package_id'] = $package_new_id;
            $packageDay['homestay_id'] = $homestay_id;
            $packageDay['is_base_for_extend'] = "1";
            $createPackageDay = $this->packageDayModel->add_epd_api($packageDay);
        }

        foreach ($package_detail as $packageDetail) {
            $packageDetail['package_id'] = $package_new_id;
            $packageDetail['homestay_id'] = $homestay_id;
            $packageDetail['is_base_for_extend'] = "1";
            $createPackageDetail = $this->packageDetailModel->add_pd_api($packageDetail);
        }

        foreach ($package_service as $packageService) {
            if ($packageService['status'] == "1") {
                $packageService['package_id'] = $package_new_id;
                $packageService['homestay_id'] = $homestay_id;
                $packageService['is_base_for_extend'] = "1";
                unset($packageService['price']);
                unset($packageService['category']);
                $createPackageService = $this->packageServiceDetailModel->add_package_service($packageService);
            }
        }

        $reservation = null;
        $addPackageonReservation = $this->reservationModel->add_package_api($reservation, $reservation_id, $homestay_id, $package_new_id);


        if ($createPackage) {
            return redirect()->to(base_url('web/reservation/package/extendPackage/' . $reservation_id . '/' . $homestay_id . '/' . $package_new_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function customPackage($reservation_id = null, $homestay_id = null, $package_id = null)
    {
        $data = $this->getPackageDetail($reservation_id, $homestay_id, $package_id);
        if (url_is('*customPackage*')) {
            $data['title'] = 'Custom Package';
        } else {
            $data['title'] = 'Extend Package';
        }
        return view('web/package_form', $data);
    }

    public function getPackageDetail($reservation_id = null, $homestay_id = null, $id = null)
    {
        $package = $this->packageModel->get_package_by_id_api($homestay_id, $id)->getRowArray();

        $package['gallery'] = [$package['brochure_url']];
        $package['id'] = $package['package_id'];

        $package_day = $this->packageDayModel->get_pd_by_pacakage_id_api($homestay_id, $id)->getResultArray();

        $list_activity = $this->packageDetailModel->get_pd_by_pacakage_id_api($homestay_id, $id)->getResultArray();
        for ($i = 0; $i < count($list_activity); $i++) {
            if (substr($list_activity[$i]['id_object'], 0, 1) === 'A') {
                $list_activity[$i]['type'] = "Attraction";
                $attraction = $this->attractionModel->get_at_by_id_api($list_activity[$i]['id_object'])->getRowArray();
                $list_activity[$i]['object_name'] = $attraction['name'];
                $list_activity[$i]['price_for_package'] = $attraction['price_for_package'];
            } elseif (substr($list_activity[$i]['id_object'], 0, 1) === 'C') {
                $list_activity[$i]['type'] = "Culinary";
                $culinaryPlace = $this->culinaryPlaceModel->get_cp_by_id_api($list_activity[$i]['id_object'])->getRowArray();
                $list_activity[$i]['object_name'] = $culinaryPlace['name'];
            } elseif (substr($list_activity[$i]['id_object'], 0, 1) === 'S') {
                $list_activity[$i]['type'] = "Souvenir";
                $souvenirPlace = $this->souvenirPlaceModel->get_sp_by_id_api($list_activity[$i]['id_object'])->getRowArray();
                $list_activity[$i]['object_name'] = $souvenirPlace['name'];
            } elseif (substr($list_activity[$i]['id_object'], 0, 1) === 'V') {
                $list_activity[$i]['type'] = "Service Provider";
                $serviceProvider = $this->serviceProviderModel->get_sv_by_id_api($list_activity[$i]['id_object'])->getRowArray();
                $list_activity[$i]['object_name'] = $serviceProvider['name'];
            } elseif (substr($list_activity[$i]['id_object'], 0, 1) === 'W') {
                $list_activity[$i]['type'] = "Worship";
                $worshipPlace = $this->worshipPlaceModel->get_wp_by_id_api($list_activity[$i]['id_object'])->getRowArray();
                $list_activity[$i]['object_name'] = $worshipPlace['name'];
            } elseif (substr($list_activity[$i]['id_object'], 0, 1) === 'E') {
                $list_activity[$i]['type'] = "Event";
                $event = $this->eventModel->get_ev_by_id_api($list_activity[$i]['id_object'])->getRowArray();
                $list_activity[$i]['object_name'] = $event['name'];
                $list_activity[$i]['price_for_package'] = $event['ticket_price'];
            } else {
                $list_activity[$i]['type'] = "Homestay Activity";
                $homestayActivity = $this->homestayExclusiveActivityModel->get_hea_by_id_api($list_activity[$i]['id_object'])->getRowArray();
                $list_activity[$i]['object_name'] = $homestayActivity['name'];
            }
        }

        $list_service = $this->packageServiceDetailModel->get_list_service_by_id($homestay_id, $id)->getResultArray();

        $reservation = $this->reservationModel->get_reservation_by_pid($reservation_id, $homestay_id, $id)->getRowArray();
        $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($reservation['id'])->getResultArray();
        $day_of_stay = array();
        for ($i = 0; $i < count($reservation_detail); $i++) {
            $day_of_stay[] = $reservation_detail[$i]['date'];
        }
        $day_of_stay = array_unique($day_of_stay);

        if (url_is('*customPackage*')) {
            $total_people = $reservation['total_people'];
        } else {
            $total_people = $package['min_capacity'];
        }

        $data = [
            'package' => $package,
            'list_day' => $package_day,
            'list_activity' => $list_activity,
            'list_service' => $list_service,
            'reservation_id' => $reservation['id'],
            'total_people' => $total_people,
            'check_in' => $reservation['check_in'],
            'day_of_stay' => count($day_of_stay)
        ];

        if (url_is('*extendPackage*')) {
            $data['res_total_people'] = $reservation['total_people'];
        }

        return $data;
    }
    //Fungsi menambahkan paket wisata pada reservasi
    public function buyPackage($homestay_id = null, $reservation_id = null, $package_id = null)
    {
        $request = $this->request->getPost();

        $requestData = [
            'total_people' => $request['total_people'],
        ];

        $check_package = $this->packageModel->check_package_api($homestay_id, $package_id)->getRowArray();
        if (empty($check_package)) {
            return redirect()->to(base_url('web/reservation'));
        }

        //Menambahkan paket wisata pada reservasi
        $addPackage = $this->reservationModel->add_package_api($requestData, $reservation_id, $homestay_id, $package_id);

        if ($addPackage) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function deletePackage($reservation_id = null)
    {
        $delPackage = $this->reservationModel->del_package_api($reservation_id);

        if ($delPackage) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Fungsi finalisasi reservasi
    public function finishReservation($reservation_id = null, $deposit = null, $total_price = null)
    {
        $finishPackage = $this->reservationModel->finish_reservation($reservation_id, $deposit, $total_price);

        if ($finishPackage) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function setPackagePrice($reservation_id = null, $package_id = null)
    {
        $request = $this->request->getPost();

        $setPrice = $this->packageModel->set_price($request['price'], $package_id);
        $setTotalPrice = $this->reservationModel->set_total_price($request['hs_res_price'] + $request['price'], $reservation_id);

        if ($setPrice) {
            return redirect()->to(base_url('dashboard/reservation/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function confirmPackagePrice($reservation_id = null, $package_id = null)
    {
        $confirm = $this->reservationModel->confirm_package_price($reservation_id, $package_id);

        if ($confirm) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Fungsi melakukan konfirmasi bukti pembayaran refund
    public function confirmRefund($reservation_id = null)
    {
        //Mendapatkan data dari form
        $request = $this->request->getPost();

        //Melakukan konfirmasi bukti pembayaran refund
        $confirm = $this->reservationModel->confirm_refund($request, $reservation_id);

        if ($confirm) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Fungsi unggah bukti pembayaran deposit
    public function payDeposit($id = null)
    {
        $request = $this->request->getPost();

        $folders = $request['gallery'];
        $gallery = array();
        foreach ($folders as $folder) {
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $fileImg = new File($filepath . '/' . $filenames[0]);
            $fileImg->move(FCPATH . 'media/photos');
            delete_files($filepath);
            rmdir($filepath);
            $gallery[] = $fileImg->getFilename();
        }

        $requestData = [
            'deposit_proof' => $gallery[0],
        ];

        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }
        //Menambahkan bukti pembayarab deposit
        $payDeposit = $this->reservationModel->pay_deposit($requestData, $id);

        if ($payDeposit) {
            return redirect()->to(base_url('web/reservation/detail/' . $id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Fungsi unggah bukti pembayaran penuh
    public function payFull($id = null)
    {
        //mendapatkan data dari form
        $request = $this->request->getPost();

        $folders = $request['gallery'];
        $gallery = array();
        foreach ($folders as $folder) {
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $fileImg = new File($filepath . '/' . $filenames[0]);
            $fileImg->move(FCPATH . 'media/photos');
            delete_files($filepath);
            rmdir($filepath);
            $gallery[] = $fileImg->getFilename();
        }

        $requestData = [
            'full_paid_proof' => $gallery[0],
        ];

        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        //Unggah bukti pembayaran penuh
        $payFull = $this->reservationModel->pay_full($requestData, $id);

        if ($payFull) {
            return redirect()->to(base_url('web/reservation/detail/' . $id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Fungsi Membatalkan reservasi
    public function cancelReservation($reservation_id = null)
    {
        date_default_timezone_set("Asia/Jakarta");
        $reservation['canceled_at'] = date("Y-m-d H:i");

        //Mendapatkan detail reservasi
        $reservation_data = $this->reservationModel->get_reservation_by_id($reservation_id)->getRowArray();
        $date_refund = date("Y-m-d H:i", strtotime($reservation_data['check_in'] . ' - 1 days'));

        //Menentukan apakah mendapatkan refund
        if (strtotime($reservation['canceled_at']) < strtotime($date_refund)) {
            $reservation['is_refund'] = '1';
        } else {
            $reservation['is_refund'] = '0';
        }
        $reservation['cancelation_reason'] = '1';

        //Membatalkan reservasi
        $cancel_reservation = $this->reservationModel->cancel_reservation($reservation, $reservation_id);

        $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($reservation_id)->getResultArray();

        foreach ($reservation_detail as $reservationDetail) {
            //Memindahkan detail reservasi ke backup
            $reservation_detail_backup = $this->reservationHomestayUnitDetailBackUpModel->add_reservation_detail_api($reservationDetail);
        }

        //Menghapus detail reservasi
        $delete_reservation_detail = $this->reservationHomestayUnitDetailModel->delete_reserv_det_by_reserv_id($reservation_id);

        if ($cancel_reservation) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //menambahkan data rekening bank customer
    public function createBankAccount()
    {
        $request = $this->request->getPost();

        $reservation_id = $request['reservation_id'];

        unset($request['reservation_id']);

        $add = $this->userBankAccountModel->add_user_bank_account($request);

        if ($add) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function updateBankAccount($id = null)
    {
        $request = $this->request->getPost();
        $reservation_id = $request['reservation_id'];

        unset($request['reservation_id']);

        $update = $this->userBankAccountModel->update_user_bank_account($request, $id);

        if ($update) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Menambahkan amenities tambahan pada reservasi
    public function addAmenities()
    {
        $request = $this->request->getPost();
        $request['additional_amenities_id'] = substr($request['additional_amenities_id'], 0, 2);

        $add = $this->reservationHomestayAdditionalAmenitiesDetailModel->add_detail_haa($request);

        return redirect()->to(base_url('web/reservation/detail/' . $request['reservation_id']));
    }
    public function addActivity()
    {
        $request = $this->request->getPost();

        $reservation_homestay_activity = $this->reservationHomestayActivityDetailModel->get_activity_by_rid_api($request['reservation_id'])->getResultArray();

        $delete = $this->reservationHomestayActivityDetailModel->del_activity_by_rid_api($request['reservation_id']);

        if (isset($request['activity_id'])) {
            foreach ($request['activity_id'] as $activity_id) {
                $add = $this->reservationHomestayActivityDetailModel->add_detail_res_act($request['homestay_id'], $request['reservation_id'], $activity_id);
            }
        }

        return redirect()->to(base_url('web/reservation/detail/' . $request['reservation_id']));
    }
    //Fungsi menambahkan rating dan review
    public function addReview($reservation_id = null)
    {
        $request = $this->request->getPost();
        //Menambahkan rating dan review
        $addRating = $this->reservationModel->add_rating($request, $reservation_id);

        if ($addRating) {
            return redirect()->to(base_url('web/reservation/detail/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //xxx
    public function index()
    {
        $homestay = $this->homestayModel->list_by_owner_api(user()->id)->getRowArray();
        $id_reservation1 = $this->reservationHomestayUnitDetailModel->get_reservation_by_hs_api($homestay['id'])->getResultArray();
        $id_reservation2 = $this->reservationHomestayUnitDetailBackUpModel->get_reservation_by_hs_api($homestay['id'])->getResultArray();

        $id_reservation = array_merge($id_reservation1, $id_reservation2);

        $id = array();

        for ($i = 0; $i < count($id_reservation); $i++) {
            $id[$i] = $id_reservation[$i]['reservation_id'];
        }

        rsort($id);

        $nid = array();

        foreach ($id as $key => $value) {
            $nid[$key] = $value;
        }

        $reservations = array();
        for ($i = 0; $i < count($nid); $i++) {
            $reservation = $this->reservationModel->get_reservation_by_id($nid[$i])->getRowArray();
            if ($reservation['canceled_at'] == null) {
                $checkIsReservationCancel = $this->checkIsReservationCancel($reservation);
            }
            $reservation = $this->reservationModel->get_reservation_by_id($nid[$i])->getRowArray();
            $reservations[] = $reservation;
        }

        for ($i = 0; $i < count($reservations); $i++) {
            if ($reservations[$i]['package_id'] != null) {
                $package = $this->packageModel->get_package_by_id_api($reservations[$i]['homestay_id'], $reservations[$i]['package_id'])->getRowArray();

                $reservations[$i]['price'] = $package['price'];
                $reservations[$i]['is_custom'] = $package['is_custom'];
            } else {
                $reservations[$i]['price'] = null;
                $reservations[$i]['is_custom'] = null;
            }
        }

        $data = [
            'title' => 'Reservation',
            'data' => $reservations,
        ];

        return view('dashboard/reservation_list', $data);
    }
    public function show($id = null)
    {
        $reservation = $this->reservationModel->get_reservation_by_id($id)->getRowArray();

        if (empty($reservation)) {
            return redirect()->to(base_url('dashboard/reservation'));
        }

        if ($reservation['canceled_at'] == null) {
            $checkIsReservationCancel = $this->checkIsReservationCancel($reservation);
        }
        $reservation = $this->reservationModel->get_reservation_by_id($id)->getRowArray();

        if ($reservation['canceled_at'] != null) {
            $reservation_detail = $this->reservationHomestayUnitDetailBackUpModel->get_reservation_by_id($id)->getResultArray();
        } else {
            $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($id)->getResultArray();
        }

        $homestay_id = array();
        $unit_type = array();
        $day_of_stay = array();
        $unit_number = array();
        for ($i = 0; $i < count($reservation_detail); $i++) {
            $day_of_stay[] = $reservation_detail[$i]['date'];
            $unit_number[] = $reservation_detail[$i]['unit_number'];
            $homestay_id[] = $reservation_detail[$i]['homestay_id'];
            $unit_type[] = $reservation_detail[$i]['unit_type'];
        }
        $day_of_stay = array_unique($day_of_stay);
        $unit_number = array_values(array_unique($unit_number));
        $homestay_id = array_unique($homestay_id);
        $unit_type = array_unique($unit_type);

        $homestay_units = array();
        for ($j = 0; $j < count($unit_number); $j++) {
            $homestay_unit = $this->homestayUnitModel->get_hu_by_id_api($homestay_id[0], $unit_type[0], $unit_number[$j])->getRowArray();
            $homestay_units[] = $homestay_unit;
        }

        for ($i = 0; $i < count($homestay_units); $i++) {
            $list_gallery = $this->homestayUnitGalleryModel->get_gallery_api($homestay_id, $homestay_units[$i]['unit_type'], $homestay_units[$i]['unit_number'])->getResultArray();
            $galleries = array();
            foreach ($list_gallery as $gallery) {
                $galleries[] = $gallery['url'];
            }
            $homestay_units[$i]['galleries'] = $galleries;
        }

        $check_out = date("Y-m-d 12:00:00", strtotime($reservation['check_in'] . ' + ' . count($day_of_stay) . ' days'));
        $reservation['check_out'] = $check_out;

        $homestay_data = $this->homestayModel->get_hs_by_id_api($homestay_id[0])->getRowArray();

        $homestay['id'] = $homestay_data['id'];
        $homestay['name'] = $homestay_data['name'];
        $homestay['phone'] = $homestay_data['phone'];
        if ($unit_type[0] == '1') {
            $homestay['unit_type'] = "Room";
        } elseif ($unit_type[0] == '2') {
            $homestay['unit_type'] = "Villa";
        } else {
            $homestay['unit_type'] = "Hall";
        }

        $reservation['day_of_stay'] = count($day_of_stay);

        $reservation_additional_amenities = $this->reservationHomestayAdditionalAmenitiesDetailModel->get_haa_by_rid_api($homestay_id[0], $reservation['id'])->getResultArray();
        for ($i = 0; $i < count($reservation_additional_amenities); $i++) {
            $amenities = $this->homestayAdditionalAmenitiesModel->get_haa_by_id_api($reservation_additional_amenities[$i]['homestay_id'], $reservation_additional_amenities[$i]['additional_amenities_id'])->getRowArray();
            $reservation_additional_amenities[$i]['name'] = $amenities['name'];
            $reservation_additional_amenities[$i]['category'] = $amenities['category'];
            $reservation_additional_amenities[$i]['price'] = $amenities['price'];
            $reservation_additional_amenities[$i]['is_order_count_per_day'] = $amenities['is_order_count_per_day'];
            $reservation_additional_amenities[$i]['is_order_count_per_person'] = $amenities['is_order_count_per_person'];
            $reservation_additional_amenities[$i]['is_order_count_per_room'] = $amenities['is_order_count_per_room'];
            $reservation_additional_amenities[$i]['description'] = $amenities['description'];
            $reservation_additional_amenities[$i]['image_url'] = $amenities['image_url'];
            $reservation_additional_amenities[$i]['id'] = $reservation_additional_amenities[$i]['additional_amenities_id'];
        }

        $homestay_owner_bank_account = $this->userBankAccountModel->get_user_bank_account($homestay_data['owner'])->getRowArray();

        $customer_bank_account = $this->userBankAccountModel->get_user_bank_account($reservation['customer_id'])->getRowArray();

        $data = [
            'title' => 'Reservation',
            'reservation' => $reservation,
            'homestay' => $homestay,
            'homestay_owner_bank_account' => $homestay_owner_bank_account,
            'customer_bank_account' => $customer_bank_account,
            'homestay_unit' => $homestay_units,
            'reservation_additional_amenities' => $reservation_additional_amenities,
        ];

        if (!empty($reservation['package_id'])) {
            $package = $this->packageModel->get_package_by_id_api($reservation['homestay_id'], $reservation['package_id'])->getRowArray();
            $data['package'] = $package;
            $data2 = $this->getPackageDetail($reservation['id'], $reservation['homestay_id'], $reservation['package_id']);

            $data = array_merge($data, $data2);
        }
        return view('dashboard/reservation_detail', $data);
    }

    //Fungsi melakukan konfirmasi reservasi
    public function confirmReservation($reservation_id = null)
    {
        $request = $this->request->getPost();

        //Mendapatkan detail reservasi
        $reservation = $this->reservationModel->get_reservation_by_id($reservation_id)->getRowArray();

        if (empty($reservation)) {
            return redirect()->to(base_url('dashboard/reservation'));
        }

        //Konfrimasi reservasi
        $confirm = $this->reservationModel->confirm_reservation($request, $reservation_id);

        if ($confirm) {
            return redirect()->to(base_url('dashboard/reservation/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Fungsi melakukan konfirmasi bukti pembayaran deposit
    public function confirmDepositReservation($id = null)
    {
        //Mendapatkan data dari form
        $request = $this->request->getPost();

        //Mendapatkan detail reservasi
        $reservation = $this->reservationModel->get_reservation_by_id($id)->getRowArray();

        if (empty($reservation)) {
            return redirect()->to(base_url('dashboard/reservation'));
        }

        //Melakukan konfirmasi bukti pembayaran deposit
        $confirm = $this->reservationModel->confirm_deposit_reservation($request, $id);

        if ($confirm) {
            return redirect()->to(base_url('dashboard/reservation/' . $id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Fungsi melakukan konfirmasi bukti pembayaran penuh
    public function confirmFullPayReservation($id = null)
    {
        //mendapatkan data dari form
        $request = $this->request->getPost();

        //Mendapatkan detail reservais
        $reservation = $this->reservationModel->get_reservation_by_id($id)->getRowArray();

        if (empty($reservation)) {
            return redirect()->to(base_url('dashboard/reservation'));
        }

        //Melakukan konfirmasi bukti pembayaran penuh
        $confirm = $this->reservationModel->confirm_full_pay_reservation($request, $id);

        if ($confirm) {
            return redirect()->to(base_url('dashboard/reservation/' . $id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //Fungsi unggah bukti pembayaran refund
    public function refundReservation($reservation_id = null)
    {
        $request = $this->request->getPost();

        $folders = $request['gallery'];
        $gallery = array();
        foreach ($folders as $folder) {
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $fileImg = new File($filepath . '/' . $filenames[0]);
            $fileImg->move(FCPATH . 'media/photos');
            delete_files($filepath);
            rmdir($filepath);
            $gallery[] = $fileImg->getFilename();
        }

        $requestData = [
            'refund_proof' => $gallery[0],
        ];

        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }
        //Menambahkan bukti pembayaran refund
        $payDeposit = $this->reservationModel->refund_reservation($requestData, $reservation_id);

        if ($payDeposit) {
            return redirect()->to(base_url('dashboard/reservation/' . $reservation_id));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function create()
    {
        $request = $this->request->getPost();
        $homestay = $this->homestayModel->list_by_owner_api(user()->id)->getRowArray();

        $folders = $request['gallery'];
        $gallery = array();
        foreach ($folders as $folder) {
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $fileImg = new File($filepath . '/' . $filenames[0]);
            $fileImg->move(FCPATH . 'media/photos');
            delete_files($filepath);
            rmdir($filepath);
            $gallery[] = $fileImg->getFilename();
        }

        $requestData = [
            'homestay_id' => $homestay['id'],
            'name' => $request['name'],
            'description' => $request['description'],
            'image_url' => $gallery[0],
        ];

        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        $addHS = $this->homestayExclusiveActivityModel->add_hea_api($requestData);


        if ($addHS) {
            return redirect()->to(base_url('dashboard/exclusiveActivity'));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function update($id = null)
    {
        $request = $this->request->getPost();
        $homestay = $this->homestayModel->list_by_owner_api(user()->id)->getRowArray();

        $folders = $request['gallery'];
        $gallery = array();
        foreach ($folders as $folder) {
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $fileImg = new File($filepath . '/' . $filenames[0]);
            $fileImg->move(FCPATH . 'media/photos');
            delete_files($filepath);
            rmdir($filepath);
            $gallery[] = $fileImg->getFilename();
        }

        $requestData = [
            'homestay_id' => $homestay['id'],
            'name' => $request['name'],
            'description' => $request['description'],
            'image_url' => $gallery[0],
        ];

        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        $updateHS = $this->homestayExclusiveActivityModel->update_hea_api($requestData, $id);

        if ($updateHS) {
            return redirect()->to(base_url('dashboard/exclusiveActivity'));
        } else {
            return redirect()->back()->withInput();
        }
    }
    //menghapus reservasi
    public function delete($id = null)
    {
        $deleteS = $this->reservationModel->delete(['id' => $id]);
        if ($deleteS) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success delete Reservation"
                ]
            ];
            return $this->respondDeleted($response);
        } else {
            $response = [
                'status' => 404,
                'message' => [
                    "Reservation not found"
                ]
            ];
            return $this->failNotFound($response);
        }
    }
    public function getListActivity($homestay_id = null)
    {
        $homestay = $this->homestayModel->get_hs_by_id_api($homestay_id)->getRowArray();
        $contents = $this->homestayExclusiveActivityModel->get_list_hea_api($homestay_id)->getResultArray();

        $data = [
            'title' => $homestay['name'],
            'category' => 'Homestay Exclusive Activity',
            'data' => $contents,
        ];

        $data['homestay_id'] = $homestay_id;

        return view('web/homestay_activity_list', $data);
    }
    // Melakukan pengecekan apakah reservasi telah batal
    public function checkIsReservationCancel($reservation = null)
    {
        date_default_timezone_set("Asia/Jakarta");
        $dateNow = date("Y-m-d H:i");

        $depositDeadline = date("d F Y, H:i", strtotime($reservation['check_in'] . ' - 2 days'));
        $fullPayDeadline = date("d F Y, 18:00", strtotime($reservation['check_in']));

        if ((strtotime($dateNow) > strtotime($depositDeadline)) && ($reservation['deposit_at'] == null) && ($reservation['canceled_at'] == null)) {
            $cancelReservation['canceled_at'] = $dateNow;
            $cancelReservation['cancelation_reason'] = '2';
            $cancelReservation['is_refund'] = '0';
            $cancel_reservation = $this->reservationModel->cancel_reservation($cancelReservation, $reservation['id']);
            $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($reservation['id'])->getResultArray();

            foreach ($reservation_detail as $reservationDetail) {
                $reservation_detail_backup = $this->reservationHomestayUnitDetailBackUpModel->add_reservation_detail_api($reservationDetail);
            }
            $delete_reservation_detail = $this->reservationHomestayUnitDetailModel->delete_reserv_det_by_reserv_id($reservation['id']);
        } else if ((strtotime($dateNow) > strtotime($fullPayDeadline)) && ($reservation['full_paid_at'] == null) && ($reservation['canceled_at'] == null)) {
            $cancelReservation['canceled_at'] = $dateNow;
            $cancelReservation['cancelation_reason'] = '3';
            $cancelReservation['is_refund'] = '0';
            $cancel_reservation = $this->reservationModel->cancel_reservation($cancelReservation, $reservation['id']);
            $reservation_detail = $this->reservationHomestayUnitDetailModel->get_reservation_by_id($reservation['id'])->getResultArray();

            foreach ($reservation_detail as $reservationDetail) {
                $reservation_detail_backup = $this->reservationHomestayUnitDetailBackUpModel->add_reservation_detail_api($reservationDetail);
            }
            $delete_reservation_detail = $this->reservationHomestayUnitDetailModel->delete_reserv_det_by_reserv_id($reservation['id']);
        }
    }
}
