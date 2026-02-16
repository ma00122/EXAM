<?php

use app\controllers\AdminController;
use app\controllers\AuthController;
use app\controllers\EchangeController;
use app\controllers\ObjetController;
use app\controllers\BesoinController;
use app\controllers\VilleController;
use app\controllers\DonController;
use app\controllers\SimulationController;
use app\controllers\DashboardController;
use app\controllers\ConfigurationController;
use app\controllers\AchatController;
use app\controllers\RecapController;
use app\controllers\DonArgentController;
use app\controllers\AttributionController;
use flight\Engine;
use flight\net\Router;

/**
 * @var Router $router 
 * @var Engine $app
 */

$router->get('/', function() use ($app) {
    $controller = new ObjetController($app);
    $controller->accueil();
});

/* ===================== ROUTES VILLES (BOLTON) ===================== */

$router->get('/villes', function() use ($app) {
    $controller = new VilleController($app);
    $controller->index();
});

$router->get('/villes/create', function() use ($app) {
    $controller = new VilleController($app);
    $controller->create();
});

$router->post('/villes/store', function() use ($app) {
    $controller = new VilleController($app);
    $controller->store();
});

$router->get('/villes/edit/@id', function($id) use ($app) {
    $controller = new VilleController($app);
    $controller->edit((int) $id);
});

$router->post('/villes/update/@id', function($id) use ($app) {
    $controller = new VilleController($app);
    $controller->update((int) $id);
});

$router->get('/villes/delete/@id', function($id) use ($app) {
    $controller = new VilleController($app);
    $controller->delete((int) $id);
});

/* ===================== ROUTES BESOINS (SEDRA) ===================== */

$router->get('/besoins', function() use ($app) {
    $controller = new BesoinController($app);
    $controller->index();
});

$router->get('/besoins/create', function() use ($app) {
    $controller = new BesoinController($app);
    $controller->create();
});

$router->post('/besoins/store', function() use ($app) {
    $controller = new BesoinController($app);
    $controller->store();
});

$router->get('/besoins/edit/@id', function($id) use ($app) {
    $controller = new BesoinController($app);
    $controller->edit((int) $id);
});

$router->post('/besoins/update/@id', function($id) use ($app) {
    $controller = new BesoinController($app);
    $controller->update((int) $id);
});

$router->get('/besoins/delete/@id', function($id) use ($app) {
    $controller = new BesoinController($app);
    $controller->delete((int) $id);
});

/* ===================== ROUTES DONS (MAHERY) ===================== */

$router->get('/dons', function() use ($app) {
    $controller = new DonController($app);
    $controller->index();
});

$router->get('/dons/create', function() use ($app) {
    $controller = new DonController($app);
    $controller->create();
});

$router->post('/dons/store', function() use ($app) {
    $controller = new DonController($app);
    $controller->store();
});

$router->get('/dons/edit/@id', function($id) use ($app) {
    $controller = new DonController($app);
    $controller->edit((int) $id);
});

$router->post('/dons/update/@id', function($id) use ($app) {
    $controller = new DonController($app);
    $controller->update((int) $id);
});

$router->get('/dons/delete/@id', function($id) use ($app) {
    $controller = new DonController($app);
    $controller->delete((int) $id);
});

/* ===================== ROUTES SIMULATION (MAHERY) ===================== */

$router->get('/simulation', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->index();
});

$router->post('/simulation/run', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->run();
});

$router->post('/simulation/reset', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->reset();
});

$router->get('/simulation/results', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->results();
});

/* ===================== ROUTES DASHBOARD (MAHERY) ===================== */

$router->get('/dashboard', function() use ($app) {
    $controller = new DashboardController($app);
    $controller->index();
});

$router->get('/dashboard/ville/@id', function($id) use ($app) {
    $controller = new DashboardController($app);
    $controller->villeDetail((int) $id);
});
/* ===================== ROUTES CONFIGURATION (MAHERY) ===================== */

$router->get('/configuration', function() use ($app) {
    $controller = new ConfigurationController($app);
    $controller->index();
});

$router->post('/configuration/frais', function() use ($app) {
    $controller = new ConfigurationController($app);
    $controller->updateFrais();
});

$router->post('/configuration/update', function() use ($app) {
    $controller = new ConfigurationController($app);
    $controller->update();
});

$router->post('/configuration/create', function() use ($app) {
    $controller = new ConfigurationController($app);
    $controller->create();
});

$router->get('/configuration/delete/@cle', function($cle) use ($app) {
    $controller = new ConfigurationController($app);
    $controller->delete($cle);
});

$router->get('/configuration/calcul-frais', function() use ($app) {
    $controller = new ConfigurationController($app);
    $controller->calculFrais();
});

/* ===================== ROUTES ACHATS (MAHERY) ===================== */

$router->get('/achats', function() use ($app) {
    $controller = new AchatController($app);
    $controller->index();
});

$router->get('/achats/create', function() use ($app) {
    $controller = new AchatController($app);
    $controller->create();
});

$router->post('/achats/store', function() use ($app) {
    $controller = new AchatController($app);
    $controller->store();
});

$router->get('/achats/show/@id', function($id) use ($app) {
    $controller = new AchatController($app);
    $controller->show((int) $id);
});

$router->get('/achats/edit/@id', function($id) use ($app) {
    $controller = new AchatController($app);
    $controller->edit((int) $id);
});

$router->post('/achats/update/@id', function($id) use ($app) {
    $controller = new AchatController($app);
    $controller->update((int) $id);
});

$router->get('/achats/validate/@id', function($id) use ($app) {
    $controller = new AchatController($app);
    $controller->validate((int) $id);
});

$router->get('/achats/cancel/@id', function($id) use ($app) {
    $controller = new AchatController($app);
    $controller->cancel((int) $id);
});

$router->get('/achats/delete/@id', function($id) use ($app) {
    $controller = new AchatController($app);
    $controller->delete((int) $id);
});

$router->get('/achats/stats', function() use ($app) {
    $controller = new AchatController($app);
    $controller->stats();
});

/* ===================== ROUTES RECAP + AJAX (MAHERY) ===================== */

$router->get('/recap', function() use ($app) {
    $controller = new RecapController($app);
    $controller->index();
});

// API AJAX pour Sedra - Récupérer les données de récap en JSON
$router->get('/recap/data', function() use ($app) {
    $controller = new RecapController($app);
    $controller->data();
});

$router->get('/recap/besoins-restants', function() use ($app) {
    $controller = new RecapController($app);
    $controller->besoinsRestants();
});

$router->get('/recap/ville/@id', function($id) use ($app) {
    $controller = new RecapController($app);
    $controller->parVille((int) $id);
});

/* ===================== ROUTES DONS ARGENT (MAHERY) ===================== */

$router->get('/dons-argent', function() use ($app) {
    $controller = new DonArgentController($app);
    $controller->index();
});

$router->get('/dons-argent/create', function() use ($app) {
    $controller = new DonArgentController($app);
    $controller->create();
});

$router->post('/dons-argent/store', function() use ($app) {
    $controller = new DonArgentController($app);
    $controller->store();
});

$router->get('/dons-argent/edit/@id', function($id) use ($app) {
    $controller = new DonArgentController($app);
    $controller->edit((int) $id);
});

$router->post('/dons-argent/update/@id', function($id) use ($app) {
    $controller = new DonArgentController($app);
    $controller->update((int) $id);
});

$router->get('/dons-argent/delete/@id', function($id) use ($app) {
    $controller = new DonArgentController($app);
    $controller->delete((int) $id);
});

/* ===================== ROUTES ATTRIBUTIONS (MAHERY) ===================== */

$router->get('/attributions', function() use ($app) {
    $controller = new AttributionController($app);
    $controller->index();
});

$router->get('/attributions/create', function() use ($app) {
    $controller = new AttributionController($app);
    $controller->create();
});

$router->post('/attributions/store', function() use ($app) {
    $controller = new AttributionController($app);
    $controller->store();
});

$router->get('/attributions/edit/@id', function($id) use ($app) {
    $controller = new AttributionController($app);
    $controller->edit((int) $id);
});

$router->post('/attributions/update/@id', function($id) use ($app) {
    $controller = new AttributionController($app);
    $controller->update((int) $id);
});

$router->get('/attributions/delete/@id', function($id) use ($app) {
    $controller = new AttributionController($app);
    $controller->delete((int) $id);
});