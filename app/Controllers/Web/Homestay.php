<?php

namespace App\Controllers\Web;

use App\Models\DetailFacilityRumahGadangModel;
use App\Models\FacilityRumahGadangModel;
use App\Models\GalleryRumahGadangModel;
use App\Models\ReviewModel;
use App\Models\RumahGadangModel;
use CodeIgniter\Files\File;
use CodeIgniter\RESTful\ResourcePresenter;
use CodeIgniter\API\ResponseTrait;

use App\Models\HomestayModel;
use App\Models\HomestayFacilityModel;
use App\Models\HomestayUnitModel;
use App\Models\HomestayUnitFacilityModel;
use App\Models\HomestayFacilityDetailModel;
use App\Models\HomestayGalleryModel;

use App\Models\PackageModel;

use App\Models\HomestayAdditionalAmenitiesModel;

use App\Models\ReservationModel;
use App\Models\ReservationHomestayUnitDetailModel;

class Homestay extends ResourcePresenter
{
    use ResponseTrait;

    protected $rumahGadangModel;
    protected $galleryRumahGadangModel;
    protected $detailFacilityRumahGadangModel;
    protected $reviewModel;
    protected $facilityRumahGadangModel;

    protected $homestayModel;
    protected $homestayFacilityModel;
    protected $homestayUnitModel;
    protected $homestayUnitFacilityModel;
    protected $homestayFacilityDetailModel;
    protected $homestayGalleryModel;

    protected $packageModel;

    protected $homestayAdditionalAmenitiesModelModel;

    protected $reservationModel;
    protected $reservationHomestayUnitDetailModel;

    protected $helpers = ['auth', 'url', 'filesystem'];

    public function __construct()
    {
        $this->rumahGadangModel = new RumahGadangModel();
        $this->galleryRumahGadangModel = new GalleryRumahGadangModel();
        $this->detailFacilityRumahGadangModel = new DetailFacilityRumahGadangModel();
        $this->reviewModel = new ReviewModel();
        $this->facilityRumahGadangModel = new FacilityRumahGadangModel();

        $this->homestayModel = new HomestayModel();
        $this->homestayFacilityModel = new HomestayFacilityModel();
        $this->homestayUnitModel = new HomestayUnitModel();
        $this->homestayUnitFacilityModel = new HomestayUnitFacilityModel();
        $this->homestayFacilityDetailModel = new HomestayFacilityDetailModel();
        $this->homestayGalleryModel = new HomestayGalleryModel();

        $this->packageModel = new PackageModel();

        $this->homestayAdditionalAmenitiesModelModel = new HomestayAdditionalAmenitiesModel();

        $this->reservationModel = new ReservationModel();
        $this->reservationHomestayUnitDetailModel = new ReservationHomestayUnitDetailModel();
    }

    /**
     * Present a view of resource objects
     *
     * @return mixed
     */
    public function index()
    {
        $contents = $this->homestayModel->get_list_hs_api()->getResultArray();
        $data = [
            'title' => 'Homestay',
            'data' => $contents,
        ];

        return view('web/list_homestay', $data);
    }

    /**
     * Present a view to present a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $homestay = $this->homestayModel->get_hs_by_id_api($id)->getRowArray();
        if (empty($homestay)) {
            return redirect()->to(substr(current_url(), 0, -strlen($id)));
        }

        $list_facility = $this->homestayFacilityDetailModel->get_facility_by_hs_api($id)->getResultArray();
        $facilities = array();
        foreach ($list_facility as $facility) {
            $facilities[] = $facility['name'];
        }

        $getRID = $this->reservationHomestayUnitDetailModel->get_reservation_by_hs_api($id)->getResultArray();

        $rating_review = array();
        $rating = 0;
        $rating_divider = 0;
        foreach ($getRID as $rid) {
            $reservation = $this->reservationModel->get_reservation_by_id($rid['reservation_id'])->getRowArray();
            if ($reservation['rating'] != null) {
                $user = $this->reservationModel->get_cust($reservation['customer_id'])->getRowArray();
                $rr['username'] = $user['username'];
                $rr['rating'] = $reservation['rating'];
                $rr['review'] = $reservation['review'];
                $rating_review[] = $rr;
                $rating = $rating + $reservation['rating'];
                $rating_divider++;
            }
        }
        if ($rating != 0) {
            $avg_rating = $rating / $rating_divider;
        } else {
            $avg_rating = 0;
        }

        $homestay['avg_rating'] = $avg_rating;
        $homestay['rating_review'] = $rating_review;

        // $list_review = $this->reviewModel->get_review_object_api('rumah_gadang_id', $id)->getResultArray();

        $list_gallery = $this->homestayGalleryModel->get_gallery_api($id)->getResultArray();
        $galleries = array();
        foreach ($list_gallery as $gallery) {
            $galleries[] = $gallery['url'];
        }


        $homestay['facilities'] = $facilities;
        // $rumahGadang['reviews'] = $list_review;
        $homestay['gallery'] = $galleries;

        $data = [
            'title' => $homestay['name'],
            'data' => $homestay,
        ];

        $data['data']['geoJson'] = [
            'type' => 'Feature',
            'geometry' => json_decode($data['data']['geoJson']),
            'properties' => []
        ];

        if (url_is('*dashboard*')) {
            return view('dashboard/homestay_detail', $data);
        }

        $data['homestay_id'] = $homestay['id'];

        return view('web/homestay_detail', $data);
    }

    /**
     * Present a view to present a new single resource object
     *
     * @return mixed
     */
    public function new()
    {
        $facilities = $this->homestayFacilityModel->get_list_fc_api()->getResultArray();
        $data = [
            'title' => 'New Homestay',
            'facilities' => $facilities,
        ];
        return view('dashboard/homestay_form', $data);
    }

    /**
     * Process the creation/insertion of a new resource object.
     * This should be a POST.
     *
     * @return mixed
     */
    public function create()
    {
        $request = $this->request->getPost();
        $id = $this->homestayModel->get_new_id_api();
        $requestData = [
            'id' => $id,
            'name' => $request['name'],
            'address' => $request['address'],
            'open' => $request['open'],
            'close' => $request['close'],
            'owner' => $request['owner'],
            'description' => $request['description'],
            'lat' => $request['lat'],
            'lng' => $request['lng'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }
        $geojson = $request['geo-json'];
        if (isset($request['video'])) {
            $folder = $request['video'];
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $vidFile = new File($filepath . '/' . $filenames[0]);
            $vidFile->move(FCPATH . 'media/videos');
            delete_files($filepath);
            rmdir($filepath);
            $requestData['video_url'] = $vidFile->getFilename();
        }
        $addHS = $this->homestayModel->add_hs_api($requestData, $geojson);

        $addFacilities = true;
        if (isset($request['facilities'])) {
            $facilities = $request['facilities'];
            $addFacilities = $this->homestayFacilityDetailModel->add_facility_api($id, $facilities);
        }

        if (isset($request['gallery'])) {
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
            $this->homestayGalleryModel->add_gallery_api($id, $gallery);
        }

        if ($addHS && $addFacilities) {
            if (in_groups('owner')) {
                return redirect()->to(base_url('dashboard/homestay') . '/' . $id);
            } else {
                return redirect()->to(base_url('dashboard/homestay/manage') . '/' . $id);
            }
        } else {
            return redirect()->back()->withInput();
        }
    }

    /**
     * Present a view to edit the properties of a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        $facilities = $this->homestayFacilityModel->get_list_fc_api()->getResultArray();
        $homestay = $this->homestayModel->get_hs_by_id_api($id)->getRowArray();
        if (empty($homestay)) {
            return redirect()->to('dashboard/homestay');
        }

        $owner = $this->homestayModel->get_hs_owner_by_id($homestay['owner'])->getRowArray();

        $homestay['owner_first_name'] = $owner['first_name'];
        $homestay['owner_last_name'] = $owner['last_name'];
        $homestay['owner_username'] = $owner['username'];

        $list_facility = $this->homestayFacilityDetailModel->get_facility_by_hs_api($id)->getResultArray();
        $selectedFac = array();
        foreach ($list_facility as $facility) {
            $selectedFac[] = $facility['name'];
        }

        $list_gallery = $this->homestayGalleryModel->get_gallery_api($id)->getResultArray();
        $galleries = array();
        foreach ($list_gallery as $gallery) {
            $galleries[] = $gallery['url'];
        }

        $homestay['facilities'] = $selectedFac;
        $homestay['gallery'] = $galleries;
        $data = [
            'title' => 'Edit Homestay',
            'data' => $homestay,
            'facilities' => $facilities,
        ];
        return view('dashboard/homestay_form', $data);
    }

    /**
     * Process the updating, full or partial, of a specific resource object.
     * This should be a POST.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $request = $this->request->getPost();
        $requestData = [
            'name' => $request['name'],
            'address' => $request['address'],
            'open' => $request['open'],
            'close' => $request['close'],
            'owner' => $request['owner'],
            'description' => $request['description'],
            'lat' => $request['lat'],
            'lng' => $request['lng'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }
        $geojson = $request['geo-json'];
        if (isset($request['video'])) {
            $folder = $request['video'];
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $vidFile = new File($filepath . '/' . $filenames[0]);
            $vidFile->move(FCPATH . 'media/videos');
            delete_files($filepath);
            rmdir($filepath);
            $requestData['video_url'] = $vidFile->getFilename();
        } else {
            $requestData['video_url'] = null;
        }
        $updateHS = $this->homestayModel->update_hs_api($id, $requestData, $geojson);

        $updateFacilities = true;
        if (isset($request['facilities'])) {
            $facilities = $request['facilities'];
            $updateFacilities = $this->homestayFacilityDetailModel->update_facility_api($id, $facilities);
        }

        if (isset($request['gallery'])) {
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
            $this->homestayGalleryModel->update_gallery_api($id, $gallery);
        } else {
            $this->homestayGalleryModel->delete_gallery_api($id);
        }

        if ($updateHS && $updateFacilities) {
            if (in_groups('owner')) {
                return redirect()->to(base_url('dashboard/homestay') . '/' . $id);
            } else {
                return redirect()->to(base_url('dashboard/homestay/manage') . '/' . $id);
            }
        } else {
            return redirect()->back()->withInput();
        }
    }

    /**
     * Present a view to confirm the deletion of a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function remove($id = null)
    {
        //
    }

    /**
     * Process the deletion of a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        //
    }

    public function recommendation()
    {
        $contents = $this->rumahGadangModel->get_recommendation_api()->getResultArray();
        for ($index = 0; $index < count($contents); $index++) {
            $list_gallery = $this->galleryRumahGadangModel->get_gallery_api($contents[$index]['id'])->getResultArray();
            $galleries = array();
            foreach ($list_gallery as $gallery) {
                $galleries[] = $gallery['url'];
            }
            $contents[$index]['gallery'] = $galleries;
        }
        $data = [
            'title' => 'Home',
            'data' => $contents,
        ];

        return view('web/recommendation', $data);
    }

    public function maps()
    {
        $contents = $this->homestayModel->get_list_hs_api()->getResultArray();

        $data = [
            'title' => 'Homestay',
            'data' => $contents,
        ];

        return view('maps/homestay', $data);
    }

    public function detail($id = null)
    {
        $homestay = $this->homestayModel->get_hs_by_id_api($id)->getRowArray();

        $list_facility = $this->homestayFacilityDetailModel->get_facility_by_hs_api($id)->getResultArray();
        $facilities = array();
        foreach ($list_facility as $facility) {
            $facilities[] = $facility['name'];
        }

        $getRID = $this->reservationHomestayUnitDetailModel->get_reservation_by_hs_api($id)->getResultArray();

        $rating_review = array();
        $rating = 0;
        $rating_divider = 0;
        foreach ($getRID as $rid) {
            $reservation = $this->reservationModel->get_reservation_by_id($rid['reservation_id'])->getRowArray();
            if ($reservation['rating'] != null) {
                $user = $this->reservationModel->get_cust($reservation['customer_id'])->getRowArray();
                $rr['username'] = $user['username'];
                $rr['rating'] = $reservation['rating'];
                $rr['review'] = $reservation['review'];
                $rating_review[] = $rr;
                $rating = $rating + $reservation['rating'];
                $rating_divider++;
            }
        }
        if ($rating != 0) {
            $avg_rating = $rating / $rating_divider;
        } else {
            $avg_rating = 0;
        }

        $homestay['avg_rating'] = $avg_rating;
        $homestay['rating_review'] = $rating_review;

        $list_gallery = $this->homestayGalleryModel->get_gallery_api($id)->getResultArray();
        $galleries = array();
        foreach ($list_gallery as $gallery) {
            $galleries[] = $gallery['url'];
        }

        $homestay_unit = $this->homestayUnitModel->get_list_hu_api($homestay['id'])->getResultArray();
        $homestay['unit'] = $homestay_unit;

        $package = $this->packageModel->list_by_homestay_api($homestay['id'])->getResultArray();
        $homestay['package'] = $package;

        $additional_amenities = $this->homestayAdditionalAmenitiesModelModel->get_list_haa_api($homestay['id'])->getResultArray();
        $homestay['additional_amenities'] = $additional_amenities;

        $homestay['facilities'] = $facilities;
        $homestay['gallery'] = $galleries;

        $data = [
            'title' => $homestay['name'],
            'data' => $homestay,
        ];

        $data['data']['geoJson'] = [
            'type' => 'Feature',
            'geometry' => json_decode($data['data']['geoJson']),
            'properties' => []
        ];

        if (url_is('*dashboard*')) {
            return view('dashboard/homestay_detail', $data);
        }

        $data['homestay_id'] = $homestay['id'];
        return view('maps/homestay_detail', $data);
    }

    public function facilityHomestay()
    {
        $contents = $this->homestayFacilityModel->get_list_fc_api()->getResultArray();
        $data = [
            'title' => 'Manage Homestay Facility',
            'category' => 'Homestay Facility',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function addNewFacilityHomestay()
    {
        $request = $this->request->getPost();

        $requestData = [
            'name' => $request['name'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        $addSV = $this->homestayFacilityModel->add_hf_api($requestData);

        if ($addSV) {
            return redirect()->to(base_url('dashboard/facilityHomestay'));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function editFacilityHomestay($id = null)
    {
        $request = $this->request->getPost();

        $requestData = [
            'id' => $id,
            'name' => $request['name'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        $editSV = $this->homestayFacilityModel->edit_hf_api($requestData);

        if ($editSV) {
            return redirect()->to(base_url('dashboard/facilityHomestay'));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function deleteFacilityHomestay($id = null)
    {
        $deleteS = $this->homestayFacilityModel->delete(['id' => $id]);
        if ($deleteS) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success delete Homestay Facility"
                ]
            ];
            return $this->respondDeleted($response);
        } else {
            $response = [
                'status' => 404,
                'message' => [
                    "Homestay Facility not found"
                ]
            ];
            return $this->failNotFound($response);
        }
    }
    public function facilityUnit()
    {
        $contents = $this->homestayUnitFacilityModel->get_list_fc_api()->getResultArray();
        $data = [
            'title' => 'Manage Homestay Unit Facility',
            'category' => 'Homestay Unit Facility',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function addNewFacilityHomestayUnit()
    {
        $request = $this->request->getPost();

        $requestData = [
            'name' => $request['name'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        $addSV = $this->homestayUnitFacilityModel->add_huf_api($requestData);

        if ($addSV) {
            return redirect()->to(base_url('dashboard/facilityUnit'));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function editFacilityUnit($id = null)
    {
        $request = $this->request->getPost();

        $requestData = [
            'id' => $id,
            'name' => $request['name'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        $editSV = $this->homestayUnitFacilityModel->edit_huf_api($requestData);

        if ($editSV) {
            return redirect()->to(base_url('dashboard/facilityUnit'));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function deleteFacilityUnit($id = null)
    {
        $deleteS = $this->homestayUnitFacilityModel->delete(['id' => $id]);
        if ($deleteS) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success delete Homestay Unit Facility"
                ]
            ];
            return $this->respondDeleted($response);
        } else {
            $response = [
                'status' => 404,
                'message' => [
                    "Homestay Unit Facility not found"
                ]
            ];
            return $this->failNotFound($response);
        }
    }
}
