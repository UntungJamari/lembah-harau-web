<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
//$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/api/dbCheck', 'Home::dbCheck');
$routes->get('/', 'LandingPage::index');
$routes->get('/add/village', 'LandingPage::addVillage');
$routes->post('/add/village/new', 'web\TouristArea::addTouristArea');
$routes->get('/kab-kota', 'LandingPage::getKabKotaData');
$routes->get('/403', 'Home::error403');
$routes->get('/login', 'Web\Profile::login');
$routes->get('/register', 'Web\Profile::register');

// Upload files
$routes->group('upload', ['namespace' => 'App\Controllers\Web'], function ($routes) {
    $routes->post('photo', 'Upload::photo');
    $routes->post('video', 'Upload::video');
    $routes->post('avatar', 'Upload::avatar');
    $routes->delete('avatar', 'Upload::remove');
    $routes->delete('photo', 'Upload::remove');
    $routes->delete('video', 'Upload::remove');
});

// App
$routes->group('web', ['namespace' => 'App\Controllers\Web'], function ($routes) {
    $routes->get('homestay/maps', 'Homestay::maps');
    $routes->get('homestay/detail/(:segment)', 'Homestay::detail/$1');
    $routes->get('homestayUnit/detail/(:segment)/(:segment)/(:segment)', 'HomestayUnit::detail/$1/$2/$3');
    $routes->get('package/detail/(:segment)/(:segment)', 'TourismPackage::detail/$1/$2');
    $routes->get('additionalAmenities/detail/(:segment)/(:segment)', 'AdditionalAmenities::detail/$1/$2');
    $routes->get('attraction/maps', 'Attraction::maps');
    $routes->get('attraction/detail/(:segment)', 'Attraction::detail/$1');
    $routes->get('uniqueAttraction', 'Attraction::uniqueAttraction');
    $routes->presenter('attraction');
    $routes->presenter('homestay');
    $routes->get('homestayUnit/(:segment)/detail/(:segment)', 'HomestayUnit::detailUnit/$1/$2');
    $routes->get('homestayUnit/(:segment)', 'HomestayUnit::getListUnit/$1');
    $routes->get('homestayPackage/detail/(:segment)/(:segment)', 'tourismPackage::show/$1/$2');
    $routes->get('homestayPackage/(:segment)', 'TourismPackage::getListPackage/$1');
    $routes->get('homestayActivity/(:segment)', 'ExclusiveActivity::getListActivity/$1');
    $routes->get('homestayAdditionalAmenities/(:segment)', 'AdditionalAmenities::getListAdditionalAmenities/$1');
    $routes->get('getAdditionalAmenities/(:segment)/(:segment)', 'AdditionalAmenities::getAdditionalAmenities/$1/$2',  ['filter' => 'role:user']);
    $routes->delete('additionalAmenities/delete/(:segment)/(:segment)/(:segment)', 'AdditionalAmenities::deleteAdditionalAmenities/$1/$2/$3',  ['filter' => 'role:user']);
    $routes->presenter('souvenirPlace');
    $routes->presenter('culinaryPlace');
    $routes->presenter('worshipPlace');
    $routes->presenter('serviceProvider');
    $routes->presenter('rumahGadang');
    $routes->get('/', 'TouristArea::index');
    $routes->presenter('event');
    $routes->get('reservation', 'Reservation::listReservation', ['filter' => 'role:user']);
    $routes->get('reservation/cancel/(:segment)', 'Reservation::cancelReservation/$1', ['filter' => 'role:user']);
    $routes->post('reservation/refund/confirm/(:segment)', 'Reservation::confirmRefund/$1', ['filter' => 'role:user']);
    $routes->get('reservation/invoice/(:segment)', 'PdfController::generatePDF/$1', ['filter' => 'role:user']);
    $routes->get('reservation/(:segment)', 'Reservation::newReservation/$1', ['filter' => 'role:user']);
    $routes->get('reservation/detail/(:segment)', 'Reservation::detailReservation/$1', ['filter' => 'role:user']);
    $routes->delete('reservation/delete/(:segment)', 'Reservation::delete/$1', ['filter' => 'role:user']);
    $routes->post('reservation/addAmenities', 'Reservation::addAmenities', ['filter' => 'role:user']);
    $routes->post('reservation/addActivity', 'Reservation::addActivity', ['filter' => 'role:user']);
    $routes->post('reservation/review/(:segment)', 'Reservation::addReview/$1', ['filter' => 'role:user']);
    $routes->post('reservation/(:segment)', 'Reservation::createReservation/$1', ['filter' => 'role:user']);
    $routes->get('reservation/unit/(:segment)/(:segment)/(:segment)/(:segment)', 'Reservation::getUnit/$1/$2/$3/$4', ['filter' => 'role:user']);
    $routes->get('reservation/finish/(:segment)/(:segment)/(:segment)', 'Reservation::finishReservation/$1/$2/$3', ['filter' => 'role:user']);
    $routes->get('reservation/package/price/confirm/(:segment)/(:segment)', 'Reservation::confirmPackagePrice/$1/$2', ['filter' => 'role:user']);
    $routes->get('reservation/package/delete/(:segment)', 'Reservation::deletePackage/$1', ['filter' => 'role:user']);
    $routes->get('reservation/package/extendPackage/(:segment)/(:segment)/(:segment)', 'Reservation::customPackage/$1/$2/$3', ['filter' => 'role:user']);
    $routes->get('reservation/package/customPackage/(:segment)/(:segment)/(:segment)', 'Reservation::customPackage/$1/$2/$3', ['filter' => 'role:user']);
    $routes->get('reservation/package/(:segment)/(:segment)', 'Reservation::getPackage/$1/$2', ['filter' => 'role:user']);
    $routes->get('reservation/package/extend/(:segment)/(:segment)/(:segment)', 'Reservation::createExtendPackage/$1/$2/$3', ['filter' => 'role:user']);
    $routes->get('reservation/package/custom/(:segment)/(:segment)', 'Reservation::createCustomPackage/$1/$2', ['filter' => 'role:user']);
    $routes->post('reservation/package/buy/(:segment)/(:segment)/(:segment)', 'Reservation::buyPackage/$1/$2/$3', ['filter' => 'role:user']);
    $routes->post('reservation/payDeposit/(:segment)', 'Reservation::payDeposit/$1', ['filter' => 'role:user']);
    $routes->post('reservation/payFull/(:segment)', 'Reservation::payFull/$1', ['filter' => 'role:user']);
    $routes->post('reservation/bankAccount/create', 'Reservation::createBankAccount', ['filter' => 'role:user']);
    $routes->post('reservation/bankAccount/update/(:segment)', 'Reservation::updateBankAccount/$1', ['filter' => 'role:user']);
    $routes->get('visitHistory', 'VisitHistory::visitHistory', ['filter' => 'role:user']);
    $routes->get('visitHistory/add', 'VisitHistory::addVisitHistory', ['filter' => 'role:user']);
    $routes->post('visitHistory', 'VisitHistory::visitHistory', ['filter' => 'role:user']);
    $routes->post('review', 'Review::add', [['filter' => 'role:user']]);

    $routes->presenter('packageDay',  ['filter' => 'role:user']);
    $routes->delete('packageDay/delete/(:segment)/(:segment)/(:segment)', 'PackageDay::delete/$1/$2/$3',  ['filter' => 'role:user']);

    $routes->get('packageDetail/getObject/(:segment)/(:segment)/(:segment)/(:segment)', 'PackageDetail::getObject/$1/$2/$3/$4',  ['filter' => 'role:user']);
    $routes->presenter('packageDetail',  ['filter' => 'role:user']);
    $routes->delete('packageDetail/delete/(:segment)/(:segment)/(:segment)/(:segment)', 'PackageDetail::delete/$1/$2/$3/$4',  ['filter' => 'role:user']);

    $routes->get('packageService/(:segment)/(:segment)', 'PackageService::getService/$1/$2',  ['filter' => 'role:user']);
    $routes->presenter('packageService',  ['filter' => 'role:user']);
    $routes->delete('packageService/delete/(:segment)/(:segment)/(:segment)', 'PackageService::delete/$1/$2/$3',  ['filter' => 'role:user']);
    // Profile
    $routes->group('profile', function ($routes) {
        $routes->get('/', 'Profile::profile', ['filter' => 'login']);
        $routes->get('changePassword', 'Profile::changePassword', ['filter' => 'login']);
        $routes->post('changePassword', 'Profile::changePassword', ['filter' => 'login']);
        $routes->get('update', 'Profile::updateProfile', ['filter' => 'login']);
        $routes->post('update', 'Profile::update', ['filter' => 'login']);
    });
});

// Dashboard
$routes->group('dashboard', ['namespace' => 'App\Controllers\Web', 'filter' => 'role:admin,owner'], function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('rumahGadang', 'Dashboard::rumahGadang',  ['filter' => 'role:owner']);
    $routes->get('event', 'Dashboard::event',  ['filter' => 'role:admin']);
    $routes->get('facility', 'Dashboard::facility', ['filter' => 'role:admin']);
    $routes->get('recommendation', 'Dashboard::recommendation',  ['filter' => 'role:admin']);
    $routes->get('users', 'Dashboard::users', ['filter' => 'role:admin']);

    $routes->get('homestay', 'Dashboard::homestay',  ['filter' => 'role:owner']);
    $routes->get('homestay/manage', 'Dashboard::homestay',  ['filter' => 'role:admin']);
    $routes->get('worshipPlace', 'Dashboard::worshipPlace',  ['filter' => 'role:admin']);
    $routes->get('serviceProvider', 'Dashboard::serviceProvider',  ['filter' => 'role:admin']);
    $routes->get('souvenirPlace', 'Dashboard::souvenirPlace',  ['filter' => 'role:admin']);
    $routes->get('culinaryPlace', 'Dashboard::culinaryPlace',  ['filter' => 'role:admin']);
    $routes->get('attraction', 'Dashboard::attraction',  ['filter' => 'role:admin']);

    $routes->get('homestay/manage/new', 'Homestay::new',  ['filter' => 'role:admin']);
    $routes->post('homestay/manage', 'Homestay::create',  ['filter' => 'role:admin']);
    $routes->get('homestay/manage/(:segment)', 'Homestay::show/$1',  ['filter' => 'role:admin']);
    $routes->get('homestay/manage/edit/(:segment)', 'Homestay::edit/$1',  ['filter' => 'role:admin']);
    $routes->post('homestay/manage/update/(:segment)', 'Homestay::update/$1',  ['filter' => 'role:admin']);
    $routes->post('homestay/bankAccount', 'Homestay::bankAccount',  ['filter' => 'role:owner']);
    $routes->post('homestay/qris', 'Homestay::qris',  ['filter' => 'role:owner']);

    $routes->get('facilityHomestay', 'Homestay::facilityHomestay',  ['filter' => 'role:admin']);
    $routes->post('facilityHomestay', 'Homestay::addNewFacilityHomestay',  ['filter' => 'role:admin']);
    $routes->post('facilityHomestay/edit/(:segment)', 'Homestay::editFacilityHomestay/$1',  ['filter' => 'role:admin']);
    $routes->delete('facilityHomestay/delete/(:segment)', 'Homestay::deleteFacilityHomestay/$1',  ['filter' => 'role:admin']);

    $routes->get('facilityUnit', 'Homestay::facilityUnit',  ['filter' => 'role:admin']);
    $routes->post('facilityUnit', 'Homestay::addNewFacilityHomestayUnit',  ['filter' => 'role:admin']);
    $routes->post('facilityUnit/edit/(:segment)', 'Homestay::editFacilityUnit/$1',  ['filter' => 'role:admin']);
    $routes->delete('facilityUnit/delete/(:segment)', 'Homestay::deleteFacilityUnit/$1',  ['filter' => 'role:admin']);

    $routes->post('homestayUnit/facility/(:segment)/(:segment)/(:segment)', 'HomestayUnit::addNewFacility/$1/$2/$3',  ['filter' => 'role:owner']);
    $routes->post('homestayUnit/facility/edit/(:segment)/(:segment)/(:segment)/(:segment)', 'HomestayUnit::editFacility/$1/$2/$3/$4',  ['filter' => 'role:owner']);
    $routes->delete('homestayUnit/facility/delete/(:segment)/(:segment)/(:segment)/(:segment)', 'HomestayUnit::deleteFacility/$1/$2/$3/$4',  ['filter' => 'role:owner']);

    $routes->presenter('homestayUnit',  ['filter' => 'role:owner']);
    $routes->delete('homestayUnit/delete/(:segment)', 'HomestayUnit::delete/$1',  ['filter' => 'role:owner']);

    $routes->presenter('exclusiveActivity',  ['filter' => 'role:owner']);
    $routes->delete('exclusiveActivity/delete/(:segment)/(:segment)', 'ExclusiveActivity::delete/$1/$2',  ['filter' => 'role:owner']);

    $routes->presenter('additionalAmenities',  ['filter' => 'role:owner']);
    $routes->delete('additionalAmenities/delete/(:segment)/(:segment)', 'AdditionalAmenities::delete/$1/$2',  ['filter' => 'role:owner']);

    $routes->presenter('tourismPackage',  ['filter' => 'role:owner']);
    $routes->delete('tourismPackage/delete/(:segment)/(:segment)', 'TourismPackage::delete/$1/$2',  ['filter' => 'role:owner']);

    $routes->presenter('packageDay',  ['filter' => 'role:owner']);
    $routes->delete('packageDay/delete/(:segment)/(:segment)/(:segment)', 'PackageDay::delete/$1/$2/$3',  ['filter' => 'role:owner']);

    $routes->get('packageDetail/getObject/(:segment)/(:segment)/(:segment)', 'PackageDetail::getObject/$1/$2/$3',  ['filter' => 'role:owner']);
    $routes->presenter('packageDetail',  ['filter' => 'role:owner']);
    $routes->delete('packageDetail/delete/(:segment)/(:segment)/(:segment)/(:segment)', 'PackageDetail::delete/$1/$2/$3/$4',  ['filter' => 'role:owner']);

    $routes->get('packageService/(:segment)/(:segment)', 'PackageService::getService/$1/$2',  ['filter' => 'role:owner']);
    $routes->presenter('packageService',  ['filter' => 'role:owner']);
    $routes->delete('packageService/delete/(:segment)/(:segment)/(:segment)', 'PackageService::delete/$1/$2/$3',  ['filter' => 'role:owner']);

    $routes->post('event/date', 'Event::addDate',  ['filter' => 'role:admin']);
    $routes->delete('event/(:segment)/date/(:segment)', 'Event::deleteDate/$1/$2',  ['filter' => 'role:admin']);
    $routes->presenter('event',  ['filter' => 'role:admin']);

    $routes->post('reservation/refund/(:segment)', 'Reservation::refundReservation/$1', ['filter' => 'role:owner']);
    $routes->post('reservation/package/price/(:segment)/(:segment)', 'Reservation::setPackagePrice/$1/$2', ['filter' => 'role:owner']);
    $routes->post('reservation/fullPay/confirm/(:segment)', 'Reservation::confirmFullPayReservation/$1',  ['filter' => 'role:owner']);
    $routes->post('reservation/deposit/confirm/(:segment)', 'Reservation::confirmDepositReservation/$1',  ['filter' => 'role:owner']);
    $routes->post('reservation/confirm/(:segment)', 'Reservation::confirmReservation/$1',  ['filter' => 'role:owner']);
    $routes->delete('reservation/delete/(:segment)', 'Reservation::delete/$1',  ['filter' => 'role:owner']);
    $routes->presenter('reservation',  ['filter' => 'role:owner']);

    $routes->presenter('rumahGadang',  ['filter' => 'role:owner']);
    $routes->presenter('homestay',  ['filter' => 'role:owner']);
    $routes->presenter('worshipPlace',  ['filter' => 'role:admin']);

    $routes->presenter('serviceProvider',  ['filter' => 'role:admin']);
    $routes->post('serviceProvider/service', 'ServiceProvider::addService',  ['filter' => 'role:admin']);
    $routes->post('serviceProvider/service/edit/(:segment)', 'ServiceProvider::editService/$1');
    $routes->delete('serviceProvider/service/delete/(:segment)', 'ServiceProvider::deleteService/$1');

    $routes->get('souvenirPlace/product', 'souvenirPlace::listProduct',  ['filter' => 'role:admin']);
    $routes->presenter('souvenirPlace',  ['filter' => 'role:admin']);
    $routes->post('souvenirPlace/product', 'souvenirPlace::addNewProduct',  ['filter' => 'role:admin']);
    $routes->post('souvenirPlace/(:segment)/product', 'souvenirPlace::addProduct',  ['filter' => 'role:admin']);
    $routes->post('souvenirPlace/product/edit/(:segment)', 'souvenirPlace::editProduct/$1');
    $routes->post('souvenirPlace/(:segment)/product/(:segment)', 'souvenirPlace::editSouvenirProduct/$1/$2',  ['filter' => 'role:admin']);
    $routes->delete('souvenirPlace/product/delete/(:segment)', 'souvenirPlace::deleteProduct/$1');
    $routes->delete('souvenirPlace/(:segment)/product/(:segment)/delete', 'souvenirPlace::deleteSouvenirProduct/$1/$2');

    $routes->get('culinaryPlace/product', 'culinaryPlace::listProduct',  ['filter' => 'role:admin']);
    $routes->presenter('culinaryPlace',  ['filter' => 'role:admin']);
    $routes->post('culinaryPlace/product', 'culinaryPlace::addNewProduct',  ['filter' => 'role:admin']);
    $routes->post('culinaryPlace/(:segment)/product', 'culinaryPlace::addProduct',  ['filter' => 'role:admin']);
    $routes->post('culinaryPlace/product/edit/(:segment)', 'culinaryPlace::editProduct/$1');
    $routes->post('culinaryPlace/(:segment)/product/(:segment)', 'culinaryPlace::editCulinaryProduct/$1/$2',  ['filter' => 'role:admin']);
    $routes->delete('culinaryPlace/product/delete/(:segment)', 'culinaryPlace::deleteProduct/$1');
    $routes->delete('culinaryPlace/(:segment)/product/(:segment)/delete', 'culinaryPlace::deleteCulinaryProduct/$1/$2');

    $routes->get('attraction/facility', 'attraction::listFacility',  ['filter' => 'role:admin']);
    $routes->presenter('attraction',  ['filter' => 'role:admin']);
    $routes->post('attraction/ticket', 'attraction::addNewTicket',  ['filter' => 'role:admin']);
    $routes->post('attraction/ticket/edit/(:segment)', 'attraction::editTicket/$1',  ['filter' => 'role:admin']);
    $routes->delete('attraction/ticket/delete/(:segment)', 'attraction::deleteTicket/$1',  ['filter' => 'role:admin']);
    $routes->post('attraction/facility', 'attraction::addNewFacility',  ['filter' => 'role:admin']);
    $routes->post('attraction/facility/edit/(:segment)', 'attraction::editFacility/$1');
    $routes->delete('attraction/facility/delete/(:segment)', 'attraction::deletefacility/$1');

    $routes->presenter('facility', ['filter' => 'role:admin']);
    $routes->presenter('users', ['filter' => 'role:admin']);
});

// API
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->resource('homestay');
    $routes->post('homestay/findByRadius', 'Homestay::findByRadius');
    $routes->post('homestay/findByName', 'Homestay::findByName');
    $routes->get('homestayFacility', 'Homestay::getFacility');
    $routes->post('homestay/findByFacility', 'Homestay::findByFacility');
    $routes->post('homestay/findByUnit', 'Homestay::findByUnit');
    $routes->resource('attraction');
    $routes->post('attraction/findByRadius', 'Attraction::findByRadius');
    $routes->post('attraction/findByName', 'Attraction::findByName');
    $routes->get('attractionFacility', 'Attraction::getFacility');
    $routes->post('attraction/findByFacility', 'Attraction::findByFacility');
    $routes->resource('rumahGadang');
    $routes->get('recommendation', 'RumahGadang::recommendation');
    $routes->post('recommendationOwner', 'RumahGadang::recommendationByOwner');
    $routes->get('recommendationList', 'RumahGadang::recommendationList');
    $routes->post('recommendation', 'RumahGadang::updateRecommendation');
    $routes->post('rumahGadangOwner', 'RumahGadang::listByOwner');
    $routes->post('rumahGadang/findByName', 'RumahGadang::findByName');
    $routes->post('rumahGadang/findByRadius', 'RumahGadang::findByRadius');
    $routes->post('rumahGadang/findByFacility', 'RumahGadang::findByFacility');
    $routes->post('rumahGadang/findByRating', 'RumahGadang::findByRating');
    $routes->post('rumahGadang/findByCategory', 'RumahGadang::findByCategory');
    $routes->get('rumahGadang/maps', 'RumahGadang::maps');
    $routes->get('event/category', 'Event::category');
    $routes->resource('event');
    $routes->post('eventOwner', 'Event::listByOwner');
    $routes->post('event/findByName', 'Event::findByName');
    $routes->post('event/findByRadius', 'Event::findByRadius');
    $routes->post('event/findByRating', 'Event::findByRating');
    $routes->post('event/findByCategory', 'Event::findByCategory');
    $routes->post('event/findByDate', 'Event::findByDate');
    $routes->resource('culinaryPlace');
    $routes->post('culinaryPlaceOwner', 'CulinaryPlace::listByOwner');
    $routes->post('culinaryPlace/findByRadius', 'CulinaryPlace::findByRadius');
    $routes->resource('worshipPlace');
    $routes->post('worshipPlaceOwner', 'WorshipPlace::listByOwner');
    $routes->post('worshipPlace/findByRadius', 'WorshipPlace::findByRadius');
    $routes->resource('souvenirPlace');
    $routes->post('souvenirPlaceOwner', 'SouvenirPlace::listByOwner');
    $routes->post('souvenirPlace/findByRadius', 'SouvenirPlace::findByRadius');
    $routes->resource('serviceProvider');
    $routes->post('serviceProvider/findByRadius', 'ServiceProvider::findByRadius');
    $routes->resource('account');
    $routes->post('account/profile', 'Account::profile');
    $routes->post('account/changePassword', 'Account::changePassword');
    $routes->post('account/visitHistory', 'Account::visitHistory');
    $routes->post('account/newVisitHistory', 'Account::newVisitHistory');
    $routes->post('account/(:num)', 'Account::update/$1');
    $routes->resource('review');
    $routes->resource('user');
    $routes->get('owner', 'User::owner');
    $routes->resource('facility');
    $routes->post('village', 'Village::getData');
    $routes->get('touristArea', 'Village::getTouristAreaData');
    $routes->get('uniqueAttraction', 'Village::getUniqueAttData');
    $routes->get('villages', 'Village::getVillagesData');
    $routes->get('subdistricts', 'Village::getSubdistrictsData');
    $routes->get('cities', 'Village::getCitiesData');
    $routes->get('provinces', 'Village::getProvincesData');
    $routes->get('countries', 'Village::getCountriesData');
    $routes->post('login', 'Profile::attemptLogin');
    $routes->post('profile', 'Profile::profile');
    $routes->get('logout', 'Profile::logout');

    $routes->get('wPCat', 'WorshipPlace::getWPCat');
    $routes->get('proList/(:segment)', 'souvenirPlace::proList/$1');
    $routes->get('culList/(:segment)', 'culinaryPlace::culList/$1');
    $routes->get('homestayUnitFac/(:segment)/(:segment)/(:segment)', 'Homestay::homestayUnitFac/$1/$2/$3');
    $routes->get('getHomestayNameByUser/(:segment)', 'Homestay::getNameByUser/$1');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
