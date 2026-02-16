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

/* ===================== ROUTES BESOINS RESTANTS (BOLTON) ===================== */

$router->get('/besoins/restants', function() use ($app) {
    $controller = new BesoinController($app);
    $controller->restants();
});

/* ===================== ROUTES RECAP DATA API (BOLTON) ===================== */

$router->get('/recap/data', function() use ($app) {
    $controller = new BesoinController($app);
    $controller->recapData();
});

$router->get('/recap/data/ville/@id', function($id) use ($app) {
    $controller = new BesoinController($app);
    $controller->recapDataVille((int) $id);
});

$router->get('/besoins/calculer-achat/@id', function($id) use ($app) {
    $controller = new BesoinController($app);
    $controller->calculerAchat((int) $id);
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
