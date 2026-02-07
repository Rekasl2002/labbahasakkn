<?php

namespace Config;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();

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

/**
 * Auto Routing (Legacy)
 * - Jika proyek kamu tidak memakai auto route, lebih aman OFF.
 * - Kalau sebelumnya kamu bergantung ke auto route, biarkan true.
 */
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Basic pages
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');

// Error preview (non-production only, guarded in controller)
$routes->group('errors', static function ($routes) {
    $routes->get('preview', 'ErrorsPreview::index');
    $routes->get('preview/(:any)', 'ErrorsPreview::show/$1');
});

// Auth
$routes->get('login', 'AuthController::chooseRole');
$routes->post('login/admin', 'AuthController::adminLogin');
$routes->post('login/student', 'AuthController::studentLogin');
$routes->get('logout', 'AuthController::logout');

// Admin area
$routes->group('admin', ['filter' => 'adminauth'], static function($routes) {
    $routes->get('/', 'AdminController::dashboard');
    $routes->post('session/start', 'AdminController::startSession');
    $routes->post('session/end', 'AdminController::endSession');
    $routes->get('settings', 'AdminController::settings');
    $routes->post('settings', 'AdminController::saveSettings');
    $routes->post('settings/password', 'AdminController::updatePassword');

    // Materials management
    $routes->get('materials', 'MaterialController::index');
    $routes->get('materials/create', 'MaterialController::create');
    $routes->post('materials/store', 'MaterialController::store');
    $routes->get('materials/edit/(:num)', 'MaterialController::edit/$1');
    $routes->post('materials/update/(:num)', 'MaterialController::update/$1');
    $routes->post('materials/delete/(:num)', 'MaterialController::delete/$1');
    $routes->post('materials/broadcast/(:num)', 'MaterialController::broadcast/$1');
});

// Student area
$routes->group('student', ['filter' => 'studentauth'], static function($routes) {
    $routes->get('/', 'StudentController::dashboard');
    $routes->get('settings', 'StudentController::settings');
});

// API
$routes->group('api', static function($routes) {
    // Session
    $routes->get('session/active', 'Api\SessionApi::active');
    $routes->post('session/heartbeat', 'Api\SessionApi::heartbeat', ['filter' => 'studentauth']);

    // Events polling (auth ditangani di controller admin/student)
    $routes->get('events/poll', 'Api\EventApi::poll');

    // Chat
    $routes->post('chat/send', 'Api\ChatApi::send');

    // Controls
    $routes->post('control/mic/toggle', 'Api\ControlApi::toggleMic', ['filter' => 'studentauth']);
    $routes->post('control/speaker/toggle', 'Api\ControlApi::toggleSpeaker', ['filter' => 'studentauth']);
    $routes->post('control/admin/mic', 'Api\ControlApi::adminSetMic', ['filter' => 'adminauth']);
    $routes->post('control/admin/speaker', 'Api\ControlApi::adminSetSpeaker', ['filter' => 'adminauth']);
    $routes->post('control/admin/all', 'Api\ControlApi::adminSetAll', ['filter' => 'adminauth']);
    $routes->post('control/admin/voice-lock', 'Api\ControlApi::adminSetVoiceLock', ['filter' => 'adminauth']);
    $routes->post('control/admin/broadcast-text', 'Api\ControlApi::adminSetBroadcastText', ['filter' => 'adminauth']);

    // WebRTC signaling (auth ditangani di controller admin/student)
    $routes->post('rtc/signal', 'Api\RtcApi::signal');

    // Material
    $routes->get('material/current', 'Api\MaterialApi::current');
    $routes->post('material/select', 'Api\MaterialApi::selectItem', ['filter' => 'adminauth']);
    $routes->post('material/media-control', 'Api\MaterialApi::mediaControl', ['filter' => 'adminauth']);
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 * Include the system's routes first, so that the app's routes can override them.
 */
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Environment Based Routes
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
