<?php

namespace App\Controllers\Api;

use App\Models\DetailFacilityRumahGadangModel;
use App\Models\GalleryRumahGadangModel;
use App\Models\ReviewModel;
use App\Models\RumahGadangModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

use App\Models\HomestayModel;
use App\Models\HomestayGalleryModel;
use App\Models\HomestayFacilityDetailModel;
use App\Models\HomestayUnitFacilityModel;
use App\Models\HomestayUnitFacilityDetailModel;

class Homestay extends ResourceController
{
    use ResponseTrait;

    protected $rumahGadangModel;
    protected $galleryRumahGadangModel;
    protected $detailFacilityRumahGadangModel;
    protected $reviewModel;

    protected $homestayModel;
    protected $homestayGalleryModel;
    protected $homestayFacilityDetailModel;
    protected $homestayUnitFacilityModel;
    protected $homestayUnitFacilityDetailModel;

    public function __construct()
    {
        $this->rumahGadangModel = new RumahGadangModel();
        $this->galleryRumahGadangModel = new GalleryRumahGadangModel();
        $this->detailFacilityRumahGadangModel = new DetailFacilityRumahGadangModel();
        $this->reviewModel = new ReviewModel();

        $this->homestayModel = new HomestayModel();
        $this->homestayGalleryModel = new HomestayGalleryModel();
        $this->homestayFacilityDetailModel = new HomestayFacilityDetailModel();
        $this->homestayUnitFacilityModel = new HomestayUnitFacilityModel();
        $this->homestayUnitFacilityDetailModel = new HomestayUnitFacilityDetailModel();
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    //Fungsi mendapatkan daftar homestay
    public function index()
    {
        $homestay = array();
        //Mendapatkan daftar homestay
        $contents = $this->homestayModel->get_list_hs_api()->getResult();
        foreach ($contents as $content) {
            $list_gallery = $this->homestayGalleryModel->get_gallery_api($content->id)->getResultArray();
            $galleries = array();
            foreach ($list_gallery as $gallery) {
                $galleries[] = $gallery['url'];
            }
            if (empty($galleries)) {
                $content->gallery = null;
            } else {
                $content->gallery = $galleries[0];
            }
            $homestay[] = $content;
        }
        $response = [
            'data' => $homestay,
            'status' => 200,
            'message' => [
                "Success get list of Homestay"
            ]
        ];
        return $this->respond($response);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $homestay = $this->homestayModel->get_hs_by_id_api($id)->getRowArray();

        $list_gallery = $this->homestayGalleryModel->get_gallery_api($id)->getResultArray();
        $galleries = array();
        foreach ($list_gallery as $gallery) {
            $galleries[] = $gallery['url'];
        }

        $list_facility = $this->homestayFacilityDetailModel->get_facility_by_hs_api($id)->getResultArray();
        $facilities = array();
        foreach ($list_facility as $facility) {
            $facilities[] = $facility['name'];
        }

        // $list_review = $this->reviewModel->get_review_object_api('rumah_gadang_id', $id)->getResultArray();
        // $avg_rating = $this->reviewModel->get_rating('rumah_gadang_id', $id)->getRowArray()['avg_rating'];

        $homestay['facilities'] = $facilities;
        $homestay['gallery'] = $galleries;
        // $rumahGadang['avg_rating'] = $avg_rating;
        // $rumahGadang['reviews'] = $list_review;

        $response = [
            'data' => $homestay,
            'status' => 200,
            'message' => [
                "Success display detail information of Homestay"
            ]
        ];
        return $this->respond($response);
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        $request = $this->request->getJSON(true);
        $id = $this->rumahGadangModel->get_new_id_api();
        $requestData = [
            'id' => $id,
            'name' => $request['name'],
            'address' => $request['address'],
            'open' => $request['open'],
            'close' => $request['close'],
            'ticket_price' => $request['ticket_price'],
            'contact_person' => $request['contact_person'],
            'status' => $request['status'],
            'recom' => $request['recom'],
            'owner' => $request['owner'],
            'description' => $request['description'],
            'video_url' => $request['video_url'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }
        $geojson = $request['geojson'];
        $addRG = $this->rumahGadangModel->add_rg_api($requestData, $geojson);
        $facilities = $request['facilities'];
        $addFacilities = $this->detailFacilityRumahGadangModel->add_facility_api($id, $facilities);
        $gallery = $request['gallery'];
        $addGallery = $this->galleryRumahGadangModel->add_gallery_api($id, $gallery);
        if ($addRG && $addFacilities && $addGallery) {
            $response = [
                'status' => 201,
                'message' => [
                    "Success create new Rumah Gadang"
                ]
            ];
            return $this->respondCreated($response);
        } else {
            $response = [
                'status' => 400,
                'message' => [
                    "Fail create new Rumah Gadang",
                    "Add Rumah Gadang: {$addRG}",
                    "Add Facilities: {$addFacilities}",
                    "Add Gallery: {$addGallery}",
                ]
            ];
            return $this->respond($response, 400);
        }
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $request = $this->request->getJSON(true);
        $requestData = [
            'name' => $request['name'],
            'address' => $request['address'],
            'open' => $request['open'],
            'close' => $request['close'],
            'ticket_price' => $request['ticket_price'],
            'contact_person' => $request['contact_person'],
            'status' => $request['status'],
            'recom' => $request['recom'],
            'owner' => $request['owner'],
            'description' => $request['description'],
            'video_url' => $request['video_url'],
        ];
        $geojson = $request['geojson'];
        $updateRG = $this->rumahGadangModel->update_rg_api($id, $requestData, $geojson);
        $facilities = $request['facilities'];
        $updateFacilities = $this->detailFacilityRumahGadangModel->update_facility_api($id, $facilities);
        $gallery = $request['gallery'];
        $updateGallery = $this->galleryRumahGadangModel->update_gallery_api($id, $gallery);
        if ($updateRG && $updateFacilities && $updateGallery) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success update Rumah Gadang"
                ]
            ];
            return $this->respondCreated($response);
        } else {
            $response = [
                'status' => 400,
                'message' => [
                    "Fail update Rumah Gadang",
                    "Update Rumah Gadang: {$updateRG}",
                    "Update Facilities: {$updateFacilities}",
                    "Update Gallery: {$updateGallery}",
                ]
            ];
            return $this->respond($response, 400);
        }
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    //menghapus data homestay
    public function delete($id = null)
    {
        $deleteHS = $this->homestayModel->delete(['id' => $id]);
        if ($deleteHS) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success delete Homestay"
                ]
            ];
            return $this->respondDeleted($response);
        } else {
            $response = [
                'status' => 404,
                'message' => [
                    "Homestay not found"
                ]
            ];
            return $this->failNotFound($response);
        }
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

        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success get list of recommended Rumah Gadang"
            ]
        ];
        return $this->respond($response);
    }

    public function recommendationByOwner()
    {
        $request = $this->request->getPost();
        $contents = $this->rumahGadangModel->recommendation_by_owner_api($request['id'])->getResultArray();
        for ($index = 0; $index < count($contents); $index++) {
            $list_gallery = $this->galleryRumahGadangModel->get_gallery_api($contents[$index]['id'])->getResultArray();
            $galleries = array();
            foreach ($list_gallery as $gallery) {
                $galleries[] = $gallery['url'];
            }
            $contents[$index]['gallery'] = $galleries;
        }

        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success get list of recommended Rumah Gadang"
            ]
        ];
        return $this->respond($response);
    }

    public function recommendationList()
    {
        $contents = $this->rumahGadangModel->get_recommendation_data_api()->getResultArray();

        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success get list of recommendation"
            ]
        ];
        return $this->respond($response);
    }

    public function updateRecommendation()
    {
        $request = $this->request->getPost();
        $requestData = [
            'id' => $request['id'],
            'recom' => $request['recom']
        ];
        $updateRecom = $this->rumahGadangModel->update_recom_api($requestData);
        if ($updateRecom) {
            $response = [
                'status' => 201,
                'message' => [
                    "Success update Rumah Gadang Recommendation"
                ]
            ];
            return $this->respondCreated($response);
        } else {
            $response = [
                'status' => 400,
                'message' => [
                    "Fail update Rumah Gadang Recommendation",
                    "Update Rumah Gadang Recommendation: {$updateRecom}",
                ]
            ];
            return $this->respond($response, 400);
        }
    }

    public function findByName()
    {
        $request = $this->request->getPost();
        $name = $request['name'];
        $contents = $this->homestayModel->get_hs_by_name_api($name)->getResult();
        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success find Homestay by name"
            ]
        ];
        return $this->respond($response);
    }

    public function findByRadius()
    {
        $request = $this->request->getPost();
        $contents = $this->homestayModel->get_hs_by_radius_api($request)->getResult();
        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success find Homestay by radius"
            ]
        ];
        return $this->respond($response);
    }

    public function findByFacility()
    {
        $request = $this->request->getPost();
        $facility = $request['facility'];
        $list_facility = $this->homestayFacilityDetailModel->get_facility_by_fc_api($facility)->getResultArray();
        $homestay_id = array();
        foreach ($list_facility as $facil) {
            $homestay_id[] = $facil['homestay_id'];
        }
        $contents = $this->homestayModel->get_hs_in_id_api($homestay_id)->getResult();
        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success find Homestay by facility"
            ]
        ];
        return $this->respond($response);
    }

    public function findByRating()
    {
        $request = $this->request->getPost();
        $rating = $request['rating'];
        $list_rating = $this->reviewModel->get_object_by_rating_api('rumah_gadang_id', $rating)->getResultArray();
        $rumah_gadang_id = array();
        foreach ($list_rating as $rat) {
            $rumah_gadang_id[] = $rat['rumah_gadang_id'];
        }
        if (count($rumah_gadang_id) > 0) {
            $contents = $this->rumahGadangModel->get_rg_in_id_api($rumah_gadang_id)->getResult();
        } else {
            $contents = [];
        }

        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success find Rumah Gadang by rating"
            ]
        ];
        return $this->respond($response);
    }

    public function findByUnit()
    {
        $request = $this->request->getPost();
        $unit_type = $request['category'];
        $id = $this->homestayUnitFacilityDetailModel->get_hs_id_by_unit($unit_type)->getResultArray();
        if (empty($id)) {
            $id_obj_included = ['AAA'];
        } else {
            $newData = array();
            foreach ($id as $row) {
                $newData[] = $row['homestay_id'];
            }
            $id_obj_included = $newData;
        }
        $contents = $this->homestayModel->get_hs_in_id_api($id_obj_included)->getResult();
        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success find Homestay by Unit"
            ]
        ];
        return $this->respond($response);
    }

    public function listByOwner()
    {
        $request = $this->request->getPost();
        $contents = $this->rumahGadangModel->list_by_owner_api($request['id'])->getResult();
        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success get list of Rumah Gadang"
            ]
        ];
        return $this->respond($response);
    }
    public function homestayUnitFac($homestay_id = null, $unit_type = null, $unit_number = null)
    {
        $id_fac_owned = $this->homestayUnitFacilityDetailModel->get_facility_id_by_hu_api($homestay_id, $unit_type, $unit_number)->getResultArray();
        $newData = array();
        foreach ($id_fac_owned as $row) {
            $newData[] = $row['facility_id'];
        }
        $id_fac_owned = $newData;
        if (empty($id_fac_owned)) {
            $contents = $this->homestayUnitFacilityModel->get_list_fc_api()->getResult();
        } else {
            $contents = $this->homestayUnitFacilityModel->get_list_huf_not_owned_api($id_fac_owned)->getResult();
        }

        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success get list of Facility"
            ]
        ];
        return $this->respond($response);
    }
    public function getNameByUser($id = null)
    {
        $homestay = $this->homestayModel->getNameByUser($id)->getRowArray();
        // dd($homestay['name']);
        $response = [
            'data' => $homestay['name'],
        ];
        return $this->respond($response);
    }
    public function getFacility()
    {
        $contents = $this->homestayModel->get_list_fc_api()->getResult();
        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success get list of facility"
            ]
        ];
        return $this->respond($response);
    }
}
