<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Database\Migrations\WorshipPlaceCategory;
use App\Models\GalleryWorshipPlaceModel;
use App\Models\ReviewModel;
use App\Models\WorshipPlaceModel;
use App\Models\WorshipPlaceGalleryModel;
use App\Models\WorshipPlaceCategoryModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Files\File;
use CodeIgniter\RESTful\ResourceController;

use App\Models\ServiceModel;
use App\Models\ServiceProviderModel;
use App\Models\ServiceProviderGalleryModel;

use App\Models\SouvenirPlaceModel;
use App\Models\SouvenirPlaceGalleryModel;
use App\Models\SouvenirProductModel;
use App\Models\SouvenirProductDetailModel;

use App\Models\CulinaryPlaceModel;
use App\Models\CulinaryPlaceGalleryModel;
use App\Models\CulinaryProductModel;
use App\Models\CulinaryProductDetailModel;

class CulinaryPlace extends BaseController
{
    use ResponseTrait;

    protected $worshipPlaceModel;
    protected $worshipPlaceGalleryModel;
    protected $worshipPlaceCategoryModel;
    protected $galleryWorshipPlaceModel;
    protected $reviewModel;

    protected $serviceModel;
    protected $serviceProviderModel;
    protected $serviceProviderGalleryModel;

    protected $souvenirPlaceModel;
    protected $souvenirPlaceGalleryModel;
    protected $souvenirProductModel;
    protected $souvenirProductDetailModel;

    protected $culinaryPlaceModel;
    protected $culinaryPlaceGalleryModel;
    protected $culinaryProductModel;
    protected $culinaryProductDetailModel;

    public function __construct()
    {
        $this->worshipPlaceModel = new WorshipPlaceModel();
        $this->worshipPlaceCategoryModel = new WorshipPlaceCategoryModel();
        $this->worshipPlaceGalleryModel = new WorshipPlaceGalleryModel();
        $this->galleryWorshipPlaceModel = new GalleryWorshipPlaceModel();
        $this->reviewModel = new ReviewModel();

        $this->serviceModel = new ServiceModel();
        $this->serviceProviderModel = new ServiceProviderModel();
        $this->serviceProviderGalleryModel = new ServiceProviderGalleryModel();

        $this->souvenirPlaceModel = new SouvenirPlaceModel();
        $this->souvenirPlaceGalleryModel = new SouvenirPlaceGalleryModel();
        $this->souvenirProductModel = new SouvenirProductModel();
        $this->souvenirProductDetailModel = new SouvenirProductDetailModel();

        $this->culinaryPlaceModel = new CulinaryPlaceModel();
        $this->culinaryPlaceGalleryModel = new CulinaryPlaceGalleryModel();
        $this->culinaryProductModel = new CulinaryProductModel();
        $this->culinaryProductDetailModel = new CulinaryProductDetailModel();
    }
    public function index()
    {
        $contents = $this->serviceProviderModel->get_list_sv_api()->getResult();
        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success get list of Service Privider"
            ]
        ];
        return $this->respond($response);
    }
    public function show($id = null)
    {
        $culinaryPlace = $this->culinaryPlaceModel->get_cp_by_id_api($id)->getRowArray();
        if (empty($culinaryPlace)) {
            return redirect()->to(substr(current_url(), 0, -strlen($id)));
        }

        $list_product = $this->culinaryProductDetailModel->get_product_by_sp_api($id)->getResultArray();

        $list_gallery = $this->culinaryPlaceGalleryModel->get_gallery_api($id)->getResultArray();
        $galleries = array();
        foreach ($list_gallery as $gallery) {
            $galleries[] = $gallery['url'];
        }

        $culinaryPlace['gallery'] = $galleries;
        $data = [
            'title' => $culinaryPlace['name'],
            'data' => $culinaryPlace,
            'products' => $list_product,
        ];

        $data['data']['geoJson'] = [
            'type' => 'Feature',
            'geometry' => json_decode($data['data']['geoJson']),
            'properties' => []
        ];

        if (url_is('*dashboard*')) {
            return view('dashboard/culinary_place_detail', $data);
        }
        return view('web/culinary_place_detail', $data);
    }
    public function new()
    {
        $data = [
            'title' => 'New Culinary Place',
        ];
        return view('dashboard/culinary_place_form', $data);
    }
    public function create()
    {
        $request = $this->request->getPost();
        $id = $this->culinaryPlaceModel->get_new_id_api();
        $requestData = [
            'id' => $id,
            'name' => $request['name'],
            'address' => $request['address'],
            'open' => $request['open'],
            'close' => $request['close'],
            'employee_name' => $request['employee_name'],
            'phone' => $request['phone'],
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

        $addSP = $this->culinaryPlaceModel->add_cp_api($requestData, $geojson);

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
            $this->culinaryPlaceGalleryModel->add_gallery_api($id, $gallery);
        }

        if ($addSP) {
            return redirect()->to(base_url('dashboard/culinaryPlace') . '/' . $id);
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function edit($id = null)
    {
        $culinaryPlace = $this->culinaryPlaceModel->get_cp_by_id_api($id)->getRowArray();
        if (empty($culinaryPlace)) {
            return redirect()->to('dashboard/culinaryPlace');
        }

        $list_gallery = $this->culinaryPlaceGalleryModel->get_gallery_api($id)->getResultArray();
        $galleries = array();
        foreach ($list_gallery as $gallery) {
            $galleries[] = $gallery['url'];
        }
        $culinaryPlace['gallery'] = $galleries;
        $data = [
            'title' => 'Edit Culinary Place',
            'data' => $culinaryPlace,
        ];
        return view('dashboard/culinary_place_form', $data);
    }
    public function update($id = null)
    {
        $request = $this->request->getPost();
        $requestData = [
            'id' => $id,
            'name' => $request['name'],
            'address' => $request['address'],
            'open' => $request['open'],
            'close' => $request['close'],
            'employee_name' => $request['employee_name'],
            'phone' => $request['phone'],
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
        $updateSV = $this->culinaryPlaceModel->update_cp_api($id, $requestData, $geojson);
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
            $this->culinaryPlaceGalleryModel->update_gallery_api($id, $gallery);
        } else {
            $this->culinaryPlaceGalleryModel->delete_gallery_api($id);
        }

        if ($updateSV) {
            return redirect()->to(base_url('dashboard/culinaryPlace') . '/' . $id);
        } else {
            return redirect()->back()->withInput();
        }
    }

    public function listProduct()
    {
        $contents = $this->culinaryProductModel->get_list_cpr_api()->getResultArray();
        $data = [
            'title' => 'Manage Culinary Product',
            'category' => 'Culinary Product',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function addNewProduct()
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

        $addSV = $this->culinaryProductModel->add_cpr_api($requestData);

        if ($addSV) {
            return redirect()->to(base_url('dashboard/culinaryPlace/product'));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function editProduct($id = null)
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

        $editSV = $this->culinaryProductModel->edit_cpr_api($requestData);

        if ($editSV) {
            return redirect()->to(base_url('dashboard/culinaryPlace/product'));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function deleteProduct($id = null)
    {
        $deleteS = $this->culinaryProductModel->delete(['id' => $id]);
        if ($deleteS) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success delete Product"
                ]
            ];
            return $this->respondDeleted($response);
        } else {
            $response = [
                'status' => 404,
                'message' => [
                    "Product not found"
                ]
            ];
            return $this->failNotFound($response);
        }
    }
    public function addProduct()
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
            'culinary_place_id' => $request['culinary_place_id'],
            'culinary_product_id' => $request['culinary_product_id'],
            'price' => $request['price'],
            'image_url' => $gallery[0],
            'description' => $request['description'],
        ];

        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        $addSV = $this->culinaryProductDetailModel->add_cpr_api($requestData);

        if ($addSV) {
            return redirect()->to(base_url('dashboard/culinaryPlace/' . $requestData['culinary_place_id']));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function editCulinaryProduct($culinaryPlaceId = null, $culinaryProductId = null)
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
            'price' => $request['price'],
            'image_url' => $gallery[0],
            'description' => $request['description'],
        ];

        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }

        $addSV = $this->culinaryProductDetailModel->update_cpr_api($requestData, $culinaryPlaceId, $culinaryProductId);

        if ($addSV) {
            return redirect()->to(base_url('dashboard/culinaryPlace/' . $culinaryPlaceId));
        } else {
            return redirect()->back()->withInput();
        }
    }
    public function deleteCulinaryProduct($culinaryPlaceId = null, $culinaryProductId = null)
    {
        $deleteS = $this->culinaryProductDetailModel->delete_cpr_by_id($culinaryPlaceId, $culinaryProductId);
        if ($deleteS) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success delete Product"
                ]
            ];
            return $this->respondDeleted($response);
        } else {
            $response = [
                'status' => 404,
                'message' => [
                    "Product not found"
                ]
            ];
            return $this->failNotFound($response);
        }
    }
}
