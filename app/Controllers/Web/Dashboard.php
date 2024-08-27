<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\EventModel;
use App\Models\FacilityRumahGadangModel;
use App\Models\RumahGadangModel;

use App\Models\HomestayModel;
use App\Models\WorshipPlaceModel;
use App\Models\ServiceProviderModel;
use App\Models\SouvenirPlaceModel;
use App\Models\CulinaryPlaceModel;
use App\Models\AttractionModel;

class Dashboard extends BaseController
{
    protected $rumahGadangModel;
    protected $eventModel;
    protected $facilityModel;
    protected $accountModel;

    protected $homestayModel;
    protected $worshipPlaceModel;
    protected $serviceProviderModel;
    protected $souvenirPlaceModel;
    protected $culinaryPlaceModel;
    protected $attractionModel;

    protected $helpers = ['auth'];

    public function __construct()
    {
        $this->rumahGadangModel = new RumahGadangModel();
        $this->eventModel = new EventModel();
        $this->facilityModel = new FacilityRumahGadangModel();
        $this->accountModel = new AccountModel();

        $this->homestayModel = new HomestayModel();
        $this->worshipPlaceModel = new WorshipPlaceModel();
        $this->serviceProviderModel = new ServiceProviderModel();
        $this->souvenirPlaceModel = new SouvenirPlaceModel();
        $this->culinaryPlaceModel = new CulinaryPlaceModel();
        $this->attractionModel = new AttractionModel();
    }
    public function index()
    {
        if (in_groups("owner")) {
            return redirect()->to(base_url('/dashboard/rumahGadang'));
        } elseif (in_groups("admin")) {
            return redirect()->to(base_url('/dashboard/users'));
        }
        return redirect()->to(base_url('/web'));
    }

    public function rumahGadang()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->rumahGadangModel->get_list_rg_api()->getResultArray();
        } elseif (in_groups('owner')) {
            $contents = $this->rumahGadangModel->list_by_owner_api(user()->id)->getResultArray();
        }

        $data = [
            'title' => 'Manage Rumah Gadang',
            'category' => 'Rumah Gadang',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function homestay()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->homestayModel->get_list_hs_api()->getResultArray();
        } elseif (in_groups('owner')) {
            $check_owner_has_homestay = $this->homestayModel->check_owner_has_homestay(user()->id)->getResultArray();
            // dd($check_owner_has_homestay);

            if (empty($check_owner_has_homestay)) {
                return redirect()->to('/dashboard/homestay/new');
            }
            return redirect()->to('/dashboard/homestay/' . $check_owner_has_homestay[0]['id']);

            // $contents = $this->homestayModel->list_by_owner_api(user()->id)->getResultArray();
        }

        $data = [
            'title' => 'Manage Homestay',
            'category' => 'Homestay',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function worshipPlace()
    {
        $contents = $this->worshipPlaceModel->get_list_wp_api()->getResultArray();
        $data = [
            'title' => 'Manage Worship PLace',
            'category' => 'Worship Place',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function serviceProvider()
    {
        $contents = $this->serviceProviderModel->get_list_sv_api()->getResultArray();
        $data = [
            'title' => 'Manage Service Provider',
            'category' => 'Service Provider',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function souvenirPlace()
    {
        $contents = $this->souvenirPlaceModel->get_list_sp_api()->getResultArray();
        $data = [
            'title' => 'Manage Souvenir Place',
            'category' => 'Souvenir Place',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function culinaryPlace()
    {
        $contents = $this->culinaryPlaceModel->get_list_cp_api()->getResultArray();
        $data = [
            'title' => 'Manage Culinary Place',
            'category' => 'Culinary Place',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function attraction()
    {
        $contents = $this->attractionModel->get_list_at_api()->getResultArray();
        $data = [
            'title' => 'Manage Attraction',
            'category' => 'Attraction',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function event()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->eventModel->get_list_ev_api()->getResultArray();
        } elseif (in_groups('owner')) {
            $contents = $this->eventModel->list_by_owner_api(user()->id)->getResultArray();
        }

        $data = [
            'title' => 'Manage Event',
            'category' => 'Event',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function facility()
    {
        $contents = $this->facilityModel->get_list_fc_api()->getResultArray();
        $data = [
            'title' => 'Manage Facility',
            'category' => 'Facility',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    //daftar user
    public function users()
    {
        $contents = $this->accountModel->get_list_user_api()->getResultArray();
        $data = [
            'title' => 'Manage Users',
            'category' => 'Users',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function recommendation()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->rumahGadangModel->get_list_recommendation_api()->getResultArray();
        } elseif (in_groups('owner')) {
            $contents = $this->rumahGadangModel->recommendation_by_owner_api(user()->id)->getResultArray();
        }

        $recommendations = $this->rumahGadangModel->get_recommendation_data_api()->getResultArray();
        $data = [
            'title' => 'Manage Recommendation',
            'category' => 'Recommendation',
            'data' => $contents,
            'recommendations' => $recommendations,
        ];
        return view('dashboard/recommendation', $data);
    }
}
