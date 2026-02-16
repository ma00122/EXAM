<?php

use app\controllers\AdminController;
use app\controllers\AuthController;
use app\controllers\EchangeController;
use app\controllers\ObjetController;
use app\controllers\VilleController;
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



/* ===================== ROUTES VILLES - MODULE BOLTON ===================== */

// Liste des villes
$router->get('/villes', function() use ($app) {
    $controller = new VilleController($app);
    $controller->index();
});

// Formulaire création ville
$router->get('/villes/create', function() use ($app) {
    $controller = new VilleController($app);
    $controller->create();
});

// Enregistrer une nouvelle ville
$router->post('/villes/store', function() use ($app) {
    $controller = new VilleController($app);
    $controller->store();
});

// Formulaire modification ville
$router->get('/villes/edit/@id', function($id) use ($app) {
    $controller = new VilleController($app);
    $controller->edit((int) $id);
});

// Mettre à jour une ville
$router->post('/villes/update/@id', function($id) use ($app) {
    $controller = new VilleController($app);
    $controller->update((int) $id);
});

// Supprimer une ville
$router->get('/villes/delete/@id', function($id) use ($app) {
    $controller = new VilleController($app);
    $controller->delete((int) $id);
});

// API JSON - Liste des villes
$router->get('/api/villes', function() use ($app) {
    $controller = new VilleController($app);
    $controller->apiIndex();
});

// API JSON - Détail d'une ville
$router->get('/api/villes/@id', function($id) use ($app) {
    $controller = new VilleController($app);
    $controller->apiShow((int) $id);
});

/* ===================== ROUTES DONS - MODULE MAHERY ===================== */

use app\controllers\DonController;
use app\controllers\SimulationController;
use app\controllers\DashboardController;

// Liste des dons
$router->get('/dons', function() use ($app) {
    $controller = new DonController($app);
    $controller->index();
});

// Formulaire création don
$router->get('/dons/create', function() use ($app) {
    $controller = new DonController($app);
    $controller->create();
});

// Enregistrer un nouveau don
$router->post('/dons/store', function() use ($app) {
    $controller = new DonController($app);
    $controller->store();
});

// Détail d'un don
$router->get('/dons/@id', function($id) use ($app) {
    $controller = new DonController($app);
    $controller->show((int) $id);
});

// Formulaire modification don
$router->get('/dons/edit/@id', function($id) use ($app) {
    $controller = new DonController($app);
    $controller->edit((int) $id);
});

// Mettre à jour un don
$router->post('/dons/update/@id', function($id) use ($app) {
    $controller = new DonController($app);
    $controller->update((int) $id);
});

// Supprimer un don
$router->get('/dons/delete/@id', function($id) use ($app) {
    $controller = new DonController($app);
    $controller->delete((int) $id);
});

// API JSON - Dons
$router->get('/api/dons', function() use ($app) {
    $controller = new DonController($app);
    $controller->apiIndex();
});

$router->get('/api/dons/@id', function($id) use ($app) {
    $controller = new DonController($app);
    $controller->apiShow((int) $id);
});

/* ===================== ROUTES SIMULATION - MODULE MAHERY ===================== */

// Page simulation
$router->get('/simulation', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->index();
});

// Exécuter la simulation
$router->post('/simulation/run', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->run();
});

// Réinitialiser la simulation
$router->post('/simulation/reset', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->reset();
});

// Résultats de la simulation
$router->get('/simulation/results', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->results();
});

// API Simulation
$router->post('/api/simulation/run', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->apiRun();
});

$router->get('/api/simulation/stats', function() use ($app) {
    $controller = new SimulationController($app);
    $controller->apiStats();
});

/* ===================== ROUTES DASHBOARD BNGRC - MODULE MAHERY ===================== */

// Dashboard principal
$router->get('/bngrc/dashboard', function() use ($app) {
    $controller = new DashboardController($app);
    $controller->index();
});

// Détail par ville
$router->get('/bngrc/dashboard/ville/@id', function($id) use ($app) {
    $controller = new DashboardController($app);
    $controller->villeDetail((int) $id);
});

// API Dashboard
$router->get('/api/bngrc/dashboard', function() use ($app) {
    $controller = new DashboardController($app);
    $controller->apiIndex();
});

$router->get('/api/bngrc/dashboard/ville/@id', function($id) use ($app) {
    $controller = new DashboardController($app);
    $controller->apiVilleDetail((int) $id);
});
